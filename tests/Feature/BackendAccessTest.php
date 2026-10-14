<?php

namespace Tests\Feature;

use App\Models\Connection;
use App\Models\Endorsement;
use App\Models\Skill;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use RuntimeException;
use Tests\TestCase;

class BackendAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_only_the_receiver_can_accept_a_connection_and_blocking_is_enforced(): void
    {
        $requester = User::factory()->create();
        $receiver = User::factory()->create();
        $this->actingAs($requester)->postJson(route('backend.connections.store'), ['receiver_id' => $requester->id])
            ->assertUnprocessable()->assertJsonValidationErrors('receiver_id');
        $this->postJson(route('backend.connections.store'), ['receiver_id' => $receiver->id, 'status' => 'accepted'])
            ->assertUnprocessable()->assertJsonValidationErrors('status');
        $created = $this->postJson(route('backend.connections.store'), ['receiver_id' => $receiver->id])->assertCreated();
        $id = $created->json('id');
        $this->patchJson(route('backend.connections.update', $id), ['status' => 'accepted'])->assertUnprocessable();
        $this->actingAs($receiver)->patchJson(route('backend.connections.update', $id), ['status' => 'accepted'])->assertOk();
        $this->patchJson(route('backend.connections.update', $id), ['status' => 'blocked'])->assertOk();
        $this->actingAs($requester)->patchJson(route('backend.connections.update', $id), ['status' => 'accepted'])->assertConflict();
        $this->postJson(route('backend.messages.store'), ['receiver_id' => $receiver->id, 'content' => 'Blocked'])->assertUnprocessable();
    }

    public function test_messages_are_private_and_only_the_recipient_can_mark_them_read(): void
    {
        $sender = User::factory()->create();
        $receiver = User::factory()->create();
        $outsider = User::factory()->create();
        $this->actingAs($sender)->postJson(route('backend.messages.store'), ['receiver_id' => $receiver->id, 'content' => 'Before connecting'])->assertUnprocessable();
        Connection::forceCreate(['requester_id' => $sender->id, 'receiver_id' => $receiver->id, 'status' => 'accepted']);
        $created = $this->postJson(route('backend.messages.store'), ['receiver_id' => $receiver->id, 'content' => 'Private message'])->assertCreated();
        $id = $created->json('id');
        $this->patchJson(route('backend.messages.update', $id), ['is_read' => true])->assertUnprocessable();
        $this->actingAs($outsider)->getJson(route('backend.messages.show', $id))->assertNotFound();
        $this->getJson(route('backend.messages.index'))->assertJsonPath('total', 0);
        $this->actingAs($receiver)->getJson(route('backend.messages.show', $id))->assertOk();
        $this->patchJson(route('backend.messages.update', $id), ['content' => 'Tampered'])->assertUnprocessable();
        $this->patchJson(route('backend.messages.update', $id), ['is_read' => true])->assertOk()->assertJsonPath('is_read', true)->assertJsonPath('content', 'Private message');
        $this->deleteJson(route('backend.messages.destroy', $id))->assertForbidden();
    }

    public function test_applicants_cannot_set_employer_decisions_and_employers_cannot_change_their_documents(): void
    {
        $employer = User::factory()->create();
        $employer->forceFill(['role' => 'employer'])->save();
        $applicant = User::factory()->create();
        $outsider = User::factory()->create();
        $company = $employer->companies()->create(['name' => 'Company']);
        $job = $employer->jobPostings()->create(['company_id' => $company->id, 'title' => 'Developer', 'description' => 'Work', 'job_type' => 'full_time']);
        $this->actingAs($applicant)->postJson(route('backend.job-applications.store'), ['job_id' => $job->id, 'status' => 'accepted'])->assertUnprocessable();
        $created = $this->postJson(route('backend.job-applications.store'), ['job_id' => $job->id, 'cover_letter' => 'Applicant document'])->assertCreated();
        $id = $created->json('id');
        $this->patchJson(route('backend.job-applications.update', $id), ['status' => 'offered'])->assertUnprocessable();
        $this->actingAs($outsider)->getJson(route('backend.job-applications.show', $id))->assertNotFound();
        $this->actingAs($employer)->getJson(route('backend.job-applications.show', $id))->assertOk();
        $this->patchJson(route('backend.job-applications.update', $id), ['cover_letter' => 'Tampered'])->assertUnprocessable();
        $this->patchJson(route('backend.job-applications.update', $id), ['status' => 'offered'])->assertOk();
        $this->deleteJson(route('backend.job-applications.destroy', $id))->assertForbidden();
        $this->actingAs($applicant)->patchJson(route('backend.job-applications.update', $id), ['status' => 'accepted'])->assertOk();
        $this->actingAs($employer)->patchJson(route('backend.job-applications.update', $id), ['status' => 'reviewing'])->assertConflict();
    }

    public function test_applications_reject_inactive_or_expired_jobs_and_duplicate_applications(): void
    {
        $employer = User::factory()->create();
        $company = $employer->companies()->create(['name' => 'Company']);
        $job = $employer->jobPostings()->create(['company_id' => $company->id, 'title' => 'Developer', 'description' => 'Work', 'job_type' => 'full_time', 'is_active' => false]);
        $applicant = User::factory()->create();
        $this->actingAs($applicant)->postJson(route('backend.job-applications.store'), ['job_id' => $job->id])->assertUnprocessable();
        $job->update(['is_active' => true, 'deadline' => '2000-01-01']);
        $this->postJson(route('backend.job-applications.store'), ['job_id' => $job->id])->assertUnprocessable();
        $job->update(['deadline' => null]);
        $this->postJson(route('backend.job-applications.store'), ['job_id' => $job->id])->assertCreated();
        $this->postJson(route('backend.job-applications.store'), ['job_id' => $job->id])->assertUnprocessable();
    }

    public function test_endorsements_require_a_connection_and_a_skill_the_recipient_has(): void
    {
        $endorser = User::factory()->create();
        $peer = User::factory()->create();
        $outsider = User::factory()->create();
        $skill = Skill::create(['name' => 'PHP']);
        $this->actingAs($endorser)->postJson(route('backend.endorsements.store'), ['endorsed_user_id' => $peer->id, 'skill_id' => $skill->id])->assertUnprocessable();
        Connection::forceCreate(['requester_id' => $endorser->id, 'receiver_id' => $peer->id, 'status' => 'accepted']);
        $this->postJson(route('backend.endorsements.store'), ['endorsed_user_id' => $peer->id, 'skill_id' => $skill->id])->assertUnprocessable()->assertJsonValidationErrors('skill_id');
        $peer->skills()->attach($skill);
        $created = $this->postJson(route('backend.endorsements.store'), ['endorsed_user_id' => $peer->id, 'skill_id' => $skill->id])->assertCreated();
        $id = $created->json('id');
        $this->postJson(route('backend.endorsements.store'), ['endorsed_user_id' => $peer->id, 'skill_id' => $skill->id])->assertUnprocessable();
        $this->assertDatabaseHas('user_skills', ['user_id' => $peer->id, 'skill_id' => $skill->id, 'endorsement_count' => 1]);
        $this->actingAs($outsider)->getJson(route('backend.endorsements.show', $id))->assertNotFound();
        $this->actingAs($peer)->getJson(route('backend.endorsements.show', $id))->assertOk();
        $this->deleteJson(route('backend.endorsements.destroy', $id))->assertForbidden();
        $this->actingAs($endorser)->deleteJson(route('backend.endorsements.destroy', $id))->assertNoContent();
        $this->assertDatabaseHas('user_skills', ['user_id' => $peer->id, 'skill_id' => $skill->id, 'endorsement_count' => 0]);
    }

    public function test_changing_an_endorsement_rebuilds_both_skill_counts(): void
    {
        $endorser = User::factory()->create();
        $peer = User::factory()->create();
        $first = Skill::create(['name' => 'PHP']);
        $second = Skill::create(['name' => 'SQL']);
        $peer->skills()->attach([$first->id, $second->id]);
        Connection::forceCreate(['requester_id' => $endorser->id, 'receiver_id' => $peer->id, 'status' => 'accepted']);
        $created = $this->actingAs($endorser)->postJson(route('backend.endorsements.store'), ['endorsed_user_id' => $peer->id, 'skill_id' => $first->id])->assertCreated();

        $this->patchJson(route('backend.endorsements.update', $created->json('id')), ['skill_id' => $second->id])->assertOk();

        $this->assertDatabaseHas('user_skills', ['user_id' => $peer->id, 'skill_id' => $first->id, 'endorsement_count' => 0]);
        $this->assertDatabaseHas('user_skills', ['user_id' => $peer->id, 'skill_id' => $second->id, 'endorsement_count' => 1]);
        $endorser->delete();
        $this->assertDatabaseHas('user_skills', ['user_id' => $peer->id, 'skill_id' => $second->id, 'endorsement_count' => 0]);
    }

    public function test_recreating_a_user_skill_restores_its_existing_endorsement_count(): void
    {
        $endorser = User::factory()->create();
        $peer = User::factory()->create();
        $skill = Skill::create(['name' => 'PHP']);
        $peer->skills()->attach($skill);
        Connection::forceCreate(['requester_id' => $endorser->id, 'receiver_id' => $peer->id, 'status' => 'accepted']);
        $this->actingAs($endorser)->postJson(route('backend.endorsements.store'), ['endorsed_user_id' => $peer->id, 'skill_id' => $skill->id])->assertCreated();
        $peer->skills()->detach($skill);

        $this->actingAs($peer)->postJson(route('backend.user-skills.store'), ['skill_id' => $skill->id])
            ->assertCreated()->assertJsonPath('endorsement_count', 1);
    }

    public function test_failed_model_events_roll_back_the_record_and_derived_count(): void
    {
        $endorser = User::factory()->create();
        $peer = User::factory()->create();
        $skill = Skill::create(['name' => 'PHP']);
        $peer->skills()->attach($skill);
        Connection::forceCreate(['requester_id' => $endorser->id, 'receiver_id' => $peer->id, 'status' => 'accepted']);
        new Endorsement;
        Endorsement::saved(function (): void {
            throw new RuntimeException('Simulated persistence failure.');
        });

        try {
            $this->withoutExceptionHandling()->actingAs($endorser)->postJson(route('backend.endorsements.store'), [
                'endorsed_user_id' => $peer->id, 'skill_id' => $skill->id,
            ]);
            $this->fail('The simulated failure did not occur.');
        } catch (RuntimeException $exception) {
            $this->assertSame('Simulated persistence failure.', $exception->getMessage());
        }

        $this->assertDatabaseCount('endorsements', 0);
        $this->assertDatabaseHas('user_skills', ['user_id' => $peer->id, 'skill_id' => $skill->id, 'endorsement_count' => 0]);
    }
}
