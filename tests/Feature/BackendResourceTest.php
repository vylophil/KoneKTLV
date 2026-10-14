<?php

namespace Tests\Feature;

use App\Models\Connection;
use App\Models\Skill;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class BackendResourceTest extends TestCase
{
    use RefreshDatabase;

    public static function resources(): array
    {
        return array_map(fn ($resource) => [$resource], [
            'skills', 'profiles', 'user-skills', 'experience', 'education', 'companies',
            'job-postings', 'job-skills', 'job-applications', 'saved-jobs', 'job-matches',
            'connections', 'messages', 'endorsements', 'profile-views',
        ]);
    }

    #[DataProvider('resources')]
    public function test_all_seven_resource_actions_work(string $resource): void
    {
        $owner = User::factory()->create();
        if ($resource !== 'job-applications') {
            $owner->forceFill(['role' => 'employer'])->save();
        }
        Gate::define('manage skills', fn (User $user) => $user->is($owner));
        Gate::define('manage job matches', fn (User $user) => $user->is($owner));
        [$payload, $update] = $this->payload($resource, $owner);
        $this->actingAs($owner);
        $prefix = 'backend.'.$resource;

        $this->getJson(route($prefix.'.index'))->assertOk()->assertJsonPath('per_page', 25);
        $this->getJson(route($prefix.'.create'))->assertOk()->assertJsonStructure(['fields']);
        $created = $this->postJson(route($prefix.'.store'), $payload)->assertCreated();
        $id = $created->json('id');
        $this->getJson(route($prefix.'.show', $id))->assertOk()->assertJsonPath('id', $id);
        $this->getJson(route($prefix.'.edit', $id))->assertOk()->assertJsonStructure(['record', 'fields']);
        $this->patchJson(route($prefix.'.update', $id), $update)->assertOk();
        $this->deleteJson(route($prefix.'.destroy', $id))->assertNoContent();
        $this->getJson(route($prefix.'.show', $id))->assertNotFound();
    }

    #[DataProvider('resources')]
    public function test_resources_require_authentication(string $resource): void
    {
        $this->getJson(route('backend.'.$resource.'.index'))->assertUnauthorized();
    }

    private function payload(string $resource, User $owner): array
    {
        $peer = User::factory()->create();
        $employer = $resource === 'job-applications' ? $peer : $owner;
        $employer->forceFill(['role' => 'employer'])->save();
        $company = $employer->companies()->create(['name' => 'Fixture Company']);
        $job = $employer->jobPostings()->create([
            'company_id' => $company->id, 'title' => 'Fixture Job', 'description' => 'Build software', 'job_type' => 'full_time',
        ]);
        $skill = Skill::create(['name' => 'Fixture Skill']);
        if (in_array($resource, ['messages', 'endorsements'], true)) {
            Connection::forceCreate(['requester_id' => $owner->id, 'receiver_id' => $peer->id, 'status' => 'accepted']);
            $peer->skills()->attach($skill);
        }

        return match ($resource) {
            'skills' => [['name' => 'New Skill', 'category' => 'Programming'], ['name' => 'Updated Skill']],
            'profiles' => [['headline' => 'Developer'], ['headline' => 'Senior developer']],
            'user-skills' => [['skill_id' => $skill->id], ['proficiency_level' => 'expert']],
            'experience' => [['company_name' => 'Konekt', 'job_title' => 'Developer', 'start_date' => '2020-01-01'], ['job_title' => 'Lead']],
            'education' => [['institution' => 'University', 'degree' => 'bachelors', 'start_date' => '2020-01-01'], ['grade' => 'A']],
            'companies' => [['name' => 'New Company'], ['name' => 'Updated Company']],
            'job-postings' => [['company_id' => $company->id, 'title' => 'Developer', 'description' => 'Work', 'job_type' => 'full_time'], ['title' => 'Lead']],
            'job-skills' => [['job_id' => $job->id, 'skill_id' => $skill->id], ['importance' => 'preferred']],
            'job-applications' => [['job_id' => $job->id, 'cover_letter' => 'Interested'], ['cover_letter' => 'Updated']],
            'saved-jobs' => [['job_id' => $job->id], ['job_id' => $job->id]],
            'job-matches' => [['job_id' => $job->id, 'match_score' => 75], ['match_score' => 80]],
            'connections' => [['receiver_id' => $peer->id, 'message' => 'Connect'], ['status' => 'blocked']],
            'messages' => [['receiver_id' => $peer->id, 'content' => 'Hello'], ['content' => 'Updated']],
            'endorsements' => [['endorsed_user_id' => $peer->id, 'skill_id' => $skill->id], ['skill_id' => $skill->id]],
            'profile-views' => [['viewed_id' => $peer->id], ['viewed_id' => $peer->id]],
        };
    }

    public function test_private_records_cannot_be_read_updated_or_deleted_by_another_user(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $profile = $owner->profile()->create(['headline' => 'Private']);
        $this->actingAs($other);

        $this->getJson(route('backend.profiles.show', $profile->id))->assertNotFound();
        $this->patchJson(route('backend.profiles.update', $profile->id), ['headline' => 'Changed'])->assertNotFound();
        $this->deleteJson(route('backend.profiles.destroy', $profile->id))->assertNotFound();
        $this->getJson(route('backend.profiles.index'))->assertJsonPath('total', 0);
    }

    public function test_validation_blocks_owner_spoofing_and_invalid_profile_fields(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $this->postJson(route('backend.profiles.store'), ['user_id' => 999, 'website' => 'javascript:alert(1)', 'years_of_experience' => -1])
            ->assertUnprocessable()->assertJsonValidationErrors(['user_id', 'website', 'years_of_experience']);
        $this->postJson(route('backend.profiles.store'), ['years_of_experience' => null])
            ->assertUnprocessable()->assertJsonValidationErrors('years_of_experience');
        $this->assertDatabaseCount('profiles', 0);
    }

    public function test_employers_cannot_use_another_employers_company_or_edit_their_job(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $owner->forceFill(['role' => 'employer'])->save();
        $other->forceFill(['role' => 'employer'])->save();
        $company = $owner->companies()->create(['name' => 'Owner Company']);
        $job = $owner->jobPostings()->create(['company_id' => $company->id, 'title' => 'Job', 'description' => 'Work', 'job_type' => 'full_time']);
        $this->actingAs($other);

        $this->postJson(route('backend.job-postings.store'), ['company_id' => $company->id, 'title' => 'Intruder', 'description' => 'Work', 'job_type' => 'full_time'])
            ->assertUnprocessable()->assertJsonValidationErrors('company_id');
        $this->patchJson(route('backend.job-postings.update', $job->id), ['title' => 'Intruder'])->assertForbidden();
        $this->getJson(route('backend.job-postings.show', $job->id))->assertOk();
    }

    public function test_salary_ranges_are_validated_on_partial_updates(): void
    {
        $owner = User::factory()->create();
        $owner->forceFill(['role' => 'employer'])->save();
        $company = $owner->companies()->create(['name' => 'Company']);
        $job = $owner->jobPostings()->create(['company_id' => $company->id, 'title' => 'Job', 'description' => 'Work', 'job_type' => 'full_time', 'salary_min' => 100, 'salary_max' => 200]);
        $this->actingAs($owner);

        $this->patchJson(route('backend.job-postings.update', $job->id), ['salary_min' => 300])->assertUnprocessable()->assertJsonValidationErrors('salary_min');
        $this->patchJson(route('backend.job-postings.update', $job->id), ['salary_max' => 50])->assertUnprocessable()->assertJsonValidationErrors('salary_max');
    }

    public function test_catalog_writes_and_inactive_accounts_are_restricted(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user)->postJson(route('backend.skills.store'), ['name' => 'Unauthorized'])->assertForbidden();
        $this->getJson(route('backend.skills.create'))->assertForbidden();
        $user->forceFill(['is_active' => false])->save();
        $this->getJson(route('backend.profiles.index'))->assertForbidden();
    }

    public function test_backend_mutations_keep_csrf_protection(): void
    {
        $user = User::factory()->create();
        $this->app->detectEnvironment(fn () => 'local');
        $this->actingAs($user)->postJson(route('backend.profiles.store'), ['headline' => 'No token'])->assertStatus(419);
        $this->withSession(['_token' => 'test-csrf-token'])->postJson(route('backend.profiles.store'), ['headline' => 'With token'], ['X-CSRF-TOKEN' => 'test-csrf-token'])->assertCreated();
    }

    public function test_guest_browser_requests_receive_json_instead_of_a_missing_login_redirect(): void
    {
        $this->get(route('backend.profiles.index'))->assertUnauthorized()->assertHeader('Content-Type', 'application/json');
    }

    public function test_form_metadata_does_not_offer_server_controlled_fields(): void
    {
        $user = User::factory()->create();
        $response = $this->actingAs($user)->getJson(route('backend.job-applications.create'))->assertOk();

        $this->assertContains('job_id', $response->json('fields'));
        $this->assertNotContains('status', $response->json('fields'));
        $this->assertNotContains('user_id', $response->json('fields'));
    }

    public function test_job_cards_include_public_company_details_without_exposing_account_fields(): void
    {
        $employer = User::factory()->create();
        $company = $employer->companies()->create(['name' => 'Konekt', 'location' => 'Manila']);
        $job = $employer->jobPostings()->create(['company_id' => $company->id, 'title' => 'Developer', 'description' => 'Work', 'job_type' => 'full_time']);
        $applicant = User::factory()->create();

        $this->actingAs($applicant)->getJson(route('backend.job-postings.show', $job->id))
            ->assertOk()->assertJsonPath('company.name', 'Konekt')->assertJsonPath('company.location', 'Manila')
            ->assertJsonMissingPath('company.user_id')->assertJsonMissingPath('company.user');
    }
}
