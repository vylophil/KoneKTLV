<?php

namespace Tests\Feature;

use App\Models\Skill;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_to_sign_in_from_the_dashboard(): void
    {
        $this->get('/dashboard')->assertRedirect('/login');
    }

    public function test_signed_in_user_sees_their_profile_resume_and_career_information(): void
    {
        /** @var User $user */
        $user = User::factory()->create(['name' => 'Jordan Reyes']);
        /** @var User $otherUser */
        $otherUser = User::factory()->create(['name' => 'Private Person']);
        $user->profile()->create([
            'headline' => 'Laravel developer',
            'location' => 'Manila',
            'resume_path' => 'resumes/1/jordan.pdf',
            'resume_original_name' => 'Jordan-Reyes.pdf',
        ]);
        $otherUser->profile()->create(['headline' => 'Private profile']);
        $skill = Skill::create(['name' => 'Laravel']);
        $user->skills()->attach($skill);
        $user->workExperience()->create([
            'company_name' => 'Northstar Labs',
            'job_title' => 'Developer',
            'start_date' => '2023-01-01',
            'is_current' => true,
        ]);
        $user->education()->create([
            'institution' => 'City University',
            'degree' => 'bachelors',
            'field_of_study' => 'Computer Science',
            'start_date' => '2019-01-01',
            'end_date' => '2022-12-31',
        ]);

        $this->actingAs($user)->get('/dashboard')->assertOk()->assertInertia(fn (Assert $page) => $page
            ->component('dashboard/index')
            ->where('user.name', 'Jordan Reyes')
            ->where('profile.headline', 'Laravel developer')
            ->where('profile.location', 'Manila')
            ->where('resume.name', 'Jordan-Reyes.pdf')
            ->where('profileCompletion', 67)
            ->has('skills', 1)
            ->where('skills.0.name', 'Laravel')
            ->has('experience', 1)
            ->where('experience.0.jobTitle', 'Developer')
            ->has('education', 1)
            ->where('education.0.institution', 'City University')
            ->where('stats.applications', 0)
            ->where('stats.savedJobs', 0)
            ->where('stats.matches', 0)
        );
    }
}
