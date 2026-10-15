<?php

namespace Tests\Feature;

use App\Models\JobPosting;
use App\Models\Skill;
use App\Models\User;
use Database\Seeders\SkillSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class JobPortalModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_company_deletion_cascades_through_job_records(): void
    {
        $employer = User::factory()->create();
        $applicant = User::factory()->create();
        $company = $employer->companies()->create(['name' => 'Konekt']);
        $job = new JobPosting(['title' => 'Developer', 'description' => 'Build applications', 'job_type' => 'full_time']);
        $job->employer()->associate($employer);
        $company->jobPostings()->save($job);
        $skill = Skill::create(['name' => 'PHP']);
        $job->skills()->attach($skill, ['importance' => 'required']);
        $application = $job->applications()->make(['cover_letter' => 'Interested']);
        $application->user()->associate($applicant);
        $application->save();
        $applicant->savedJobs()->create(['job_id' => $job->id]);
        $applicant->jobMatches()->create(['job_id' => $job->id, 'match_score' => 75]);

        $this->assertTrue($job->company->is($company));
        $this->assertTrue($application->jobPosting->is($job));
        $this->assertTrue($skill->jobPostings()->firstOrFail()->is($job));

        $company->delete();

        foreach (['job_postings', 'job_skills', 'job_applications', 'saved_jobs', 'job_matches'] as $table) {
            $this->assertDatabaseCount($table, 0);
        }
        $this->assertDatabaseHas('users', ['id' => $applicant->id]);
        $this->assertDatabaseHas('skills', ['id' => $skill->id]);
    }

    public function test_work_history_and_education_belong_to_their_user(): void
    {
        $user = User::factory()->create();
        $experience = $user->workExperience()->create([
            'company_name' => 'Konekt', 'job_title' => 'Developer', 'start_date' => '2024-01-01', 'is_current' => true,
        ]);
        $education = $user->education()->create([
            'institution' => 'University', 'degree' => 'bachelors', 'start_date' => '2020-06-01',
        ]);

        $this->assertTrue($experience->user->is($user));
        $this->assertTrue($education->user->is($user));
        $this->assertTrue($experience->is_current);
        $this->assertSame('2024-01-01', $experience->start_date->format('Y-m-d'));
    }

    public function test_networking_records_cascade_when_a_participant_is_deleted(): void
    {
        $sender = User::factory()->create();
        $receiver = User::factory()->create();
        $skill = Skill::create(['name' => 'SQL']);
        $connection = $sender->sentConnections()->create(['receiver_id' => $receiver->id, 'status' => 'accepted']);
        $message = $sender->sentMessages()->create(['receiver_id' => $receiver->id, 'content' => 'Hello']);
        $endorsement = $sender->endorsementsGiven()->create(['endorsed_user_id' => $receiver->id, 'skill_id' => $skill->id]);
        $view = $sender->profileViewsMade()->create(['viewed_id' => $receiver->id]);

        $this->assertTrue($connection->receiver->is($receiver));
        $this->assertTrue($message->sender->is($sender));
        $this->assertTrue($endorsement->skill->is($skill));
        $this->assertTrue($view->viewedUser->is($receiver));

        DB::table('users')->where('id', $receiver->id)->delete();

        foreach (['connections', 'messages', 'endorsements', 'profile_views'] as $table) {
            $this->assertDatabaseCount($table, 0);
        }
        $this->assertDatabaseHas('users', ['id' => $sender->id]);
    }

    public function test_user_mass_assignment_cannot_change_account_role_or_active_state(): void
    {
        $user = User::factory()->create();
        $user->update(['first_name' => 'Cody', 'role' => 'employer', 'is_active' => false]);
        $user->refresh();

        $this->assertSame('Cody', $user->first_name);
        $this->assertSame('job_seeker', $user->role);
        $this->assertTrue($user->is_active);
    }

    public function test_skill_seeding_is_safe_to_repeat(): void
    {
        $this->seed(SkillSeeder::class);
        $this->seed(SkillSeeder::class);

        $this->assertDatabaseCount('skills', 100);
        $this->assertDatabaseHas('skills', ['name' => 'Laravel', 'category' => 'Web Development']);
    }
}
