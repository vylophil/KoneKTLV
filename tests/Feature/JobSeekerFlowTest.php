<?php

namespace Tests\Feature;

use App\Models\JobApplication;
use App\Models\JobPosting;
use App\Models\Skill;
use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class JobSeekerFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_home_and_member_home_have_separate_destinations(): void
    {
        $this->get('/')->assertOk()->assertInertia(fn (Assert $page) => $page->component('home'));
        $this->get('/?search=Laravel')->assertRedirect('/jobs?search=Laravel');
        $this->actingAs(User::factory()->create())->get('/')->assertRedirect('/dashboard');
        foreach (['/profile', '/matches', '/applications', '/saved-jobs', '/settings'] as $path) {
            $this->get($path)->assertOk();
        }
    }

    public function test_filters_and_sorting_apply_before_pagination(): void
    {
        $job = $this->job(['title' => 'A remote role', 'work_arrangement' => 'remote', 'job_type' => 'contract']);
        for ($index = 0; $index < 15; $index++) {
            $this->job(['title' => 'Office role '.$index, 'work_arrangement' => 'on_site']);
        }
        $this->get('/jobs?arrangement=remote&type=contract&sort=title')->assertInertia(fn (Assert $page) => $page
            ->where('jobs.total', 1)->where('jobs.data.0.id', $job->id)->where('filters.arrangement', 'remote'));
        $this->get('/jobs')->assertInertia(fn (Assert $page) => $page->where('jobs.total', 16)->has('jobs.data', 12));
        $this->get('/jobs?page=2')->assertInertia(fn (Assert $page) => $page->has('jobs.data', 4)->where('jobs.current_page', 2));
        $this->get('/jobs?arrangement=on_site&sort=title')->assertInertia(fn (Assert $page) => $page
            ->where('jobs.total', 15)->where('jobs.next_page_url', fn ($url) => str_contains($url, 'arrangement=on_site') && str_contains($url, 'sort=title')));
    }

    public function test_guest_apply_returns_to_the_selected_role_after_login(): void
    {
        $job = $this->job();
        $this->get('/jobs/'.$job->id.'/apply')->assertRedirect('/login');
        $user = User::factory()->create();
        $this->post('/login', ['email' => $user->email, 'password' => 'password'])->assertRedirect('/jobs/'.$job->id.'/apply');
        $this->get('/jobs/'.$job->id.'/apply')->assertRedirect('/jobs/'.$job->id.'#apply');
    }

    public function test_saving_is_idempotent_and_unsaving_is_scoped_to_the_owner(): void
    {
        $job = $this->job();
        $user = User::factory()->create();
        $other = User::factory()->create();
        $other->savedJobs()->create(['job_id' => $job->id]);
        $this->actingAs($user)->post('/jobs/'.$job->id.'/save')->assertRedirect();
        $this->post('/jobs/'.$job->id.'/save')->assertRedirect();
        $this->assertDatabaseCount('saved_jobs', 2);
        $this->get('/saved-jobs')->assertInertia(fn (Assert $page) => $page->has('jobs.data', 1));
        $this->delete('/jobs/'.$job->id.'/save')->assertRedirect();
        $this->assertDatabaseHas('saved_jobs', ['user_id' => $other->id, 'job_id' => $job->id]);
        $this->assertDatabaseMissing('saved_jobs', ['user_id' => $user->id]);
    }

    public function test_application_keeps_a_private_resume_snapshot_and_prevents_duplicates(): void
    {
        Storage::fake('local');
        $job = $this->job();
        $user = User::factory()->create();
        $user->profile()->create(['resume_path' => 'resumes/original.pdf']);
        Storage::disk('local')->put('resumes/original.pdf', 'original resume');
        $this->actingAs($user)->post('/jobs/'.$job->id.'/apply', ['cover_letter' => 'My relevant experience.'])->assertRedirect('/applications');
        $application = JobApplication::query()->sole();
        $this->assertSame('original resume', Storage::disk('local')->get($application->resume_path));
        Storage::disk('local')->put('resumes/original.pdf', 'replacement resume');
        $this->assertSame('original resume', Storage::disk('local')->get($application->resume_path));
        $this->get('/applications/'.$application->id.'/resume')->assertOk();
        $this->get('/applications')->assertInertia(fn (Assert $page) => $page->has('applications.data', 1)->where('applications.data.0.status', 'pending'));
        $this->post('/jobs/'.$job->id.'/apply')->assertSessionHasErrors('application');
        $this->assertDatabaseCount('job_applications', 1);
        $this->assertCount(1, Storage::disk('local')->allFiles('applications'));
        $this->actingAs(User::factory()->create())->get('/applications/'.$application->id.'/resume')->assertNotFound();
        $this->actingAs($job->employer)->get('/applications/'.$application->id.'/resume')->assertOk();
    }

    public function test_applications_require_a_resume_open_job_and_seeker_role(): void
    {
        $job = $this->job();
        $user = User::factory()->create();
        $this->actingAs($user)->post('/jobs/'.$job->id.'/apply')->assertSessionHasErrors('application');
        $job->update(['deadline' => now()->subDay()]);
        $this->post('/jobs/'.$job->id.'/apply')->assertStatus(409);
        $this->post('/jobs/'.$job->id.'/save')->assertStatus(409);
        $employer = User::factory()->create();
        $employer->setAttribute('role', 'employer')->save();
        $this->actingAs($employer)->post('/jobs/'.$job->id.'/apply')->assertForbidden();
        $this->assertDatabaseCount('job_applications', 0);
    }

    public function test_profile_editor_validates_and_uses_only_the_signed_in_owner(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();
        $other->profile()->create(['headline' => 'Private headline']);
        $this->actingAs($user)->put('/profile', ['headline' => 'My headline', 'years_of_experience' => 3])->assertRedirect();
        $this->assertDatabaseHas('profiles', ['user_id' => $user->id, 'headline' => 'My headline']);
        $this->put('/profile', ['headline' => 'Updated headline', 'user_id' => $other->id])->assertSessionHasErrors('user_id');
        $this->put('/profile', ['website' => 'javascript:alert(1)'])->assertSessionHasErrors('website');
        $this->get('/profile')->assertInertia(fn (Assert $page) => $page->where('profile.headline', 'My headline')->missing('profile.resume_path'));
        $this->assertDatabaseHas('profiles', ['user_id' => $other->id, 'headline' => 'Private headline']);
    }

    public function test_career_entries_can_be_added_edited_and_removed_without_crossing_ownership(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();
        $private = $other->workExperience()->create(['company_name' => 'Private', 'job_title' => 'Private', 'start_date' => '2020-01-01']);
        $this->actingAs($user)->post('/profile/career/experience', ['company_name' => 'Studio', 'job_title' => 'Developer', 'start_date' => '2023-01-01', 'is_current' => true])->assertRedirect();
        $entry = $user->workExperience()->sole();
        $this->put('/profile/career/experience/'.$entry->id, ['job_title' => 'Senior developer'])->assertRedirect();
        $this->assertDatabaseHas('experience', ['id' => $entry->id, 'job_title' => 'Senior developer']);
        $this->put('/profile/career/experience/'.$private->id, ['job_title' => 'Changed'])->assertNotFound();
        $this->delete('/profile/career/experience/'.$private->id)->assertNotFound();
        $this->delete('/profile/career/experience/'.$entry->id)->assertRedirect();
        $this->post('/profile/career/education', ['institution' => 'University', 'degree' => 'bachelors', 'start_date' => '2019-01-01'])->assertRedirect();
        $this->assertDatabaseHas('education', ['user_id' => $user->id, 'institution' => 'University']);
        $this->post('/profile/career/experience', ['company_name' => 'Invalid', 'job_title' => 'Invalid', 'start_date' => '2024-01-01', 'end_date' => '2020-01-01'])->assertSessionHasErrors();
    }

    public function test_skills_can_be_added_and_removed_without_crossing_ownership(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();
        $skill = Skill::create(['name' => 'Laravel']);
        $private = $other->userSkillEntries()->create(['skill_id' => $skill->id]);
        $this->actingAs($user)->post('/profile/skills', ['skill_id' => $skill->id, 'proficiency_level' => 'advanced'])->assertRedirect();
        $this->post('/profile/skills', ['skill_id' => $skill->id])->assertSessionHasErrors('skill_id');
        $this->delete('/profile/skills/'.$private->id)->assertNotFound();
        $this->delete('/profile/skills/'.$user->userSkillEntries()->sole()->id)->assertRedirect();
        $this->assertDatabaseHas('user_skills', ['id' => $private->id]);
    }

    public function test_password_recovery_renders_sends_a_link_and_resets_a_password(): void
    {
        Notification::fake();
        $user = User::factory()->create();
        $this->get('/forgot-password')->assertOk()->assertInertia(fn (Assert $page) => $page->component('auth/forgot-password'));
        $this->post('/forgot-password', ['email' => $user->email])->assertSessionHasNoErrors();
        Notification::assertSentTo($user, ResetPassword::class);
        $token = Password::createToken($user);
        $this->get('/reset-password/'.$token.'?email='.urlencode($user->email))->assertOk()->assertInertia(fn (Assert $page) => $page->component('auth/reset-password')->where('email', $user->email));
        $this->post('/reset-password', ['email' => $user->email, 'token' => $token, 'password' => 'new-secure-password', 'password_confirmation' => 'new-secure-password'])->assertSessionHasNoErrors();
        $this->assertTrue(Hash::check('new-secure-password', $user->fresh()->password));
    }

    /** @param array<string, mixed> $attributes */
    private function job(array $attributes = []): JobPosting
    {
        $employer = User::factory()->create();
        $company = $employer->companies()->create(['name' => 'Example Studio']);

        return $employer->jobPostings()->create([...[
            'company_id' => $company->id, 'title' => 'Laravel developer', 'description' => str_repeat('Full job description. ', 20),
            'job_type' => 'full_time', 'work_arrangement' => 'remote',
        ], ...$attributes]);
    }
}
