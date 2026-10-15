<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class PublicJobListingTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_can_browse_active_jobs_and_search_by_role_or_company(): void
    {
        $employer = User::factory()->create();
        $company = $employer->companies()->create(['name' => 'Northstar Labs', 'location' => 'Manila']);
        $visibleJob = $employer->jobPostings()->create([
            'company_id' => $company->id,
            'title' => 'Frontend Engineer',
            'description' => 'Build accessible product experiences.',
            'location' => 'Quezon City',
            'job_type' => 'full_time',
            'work_arrangement' => 'hybrid',
        ]);
        $employer->jobPostings()->create([
            'company_id' => $company->id,
            'title' => 'Closed role',
            'description' => 'No longer available.',
            'job_type' => 'full_time',
            'is_active' => false,
        ]);
        $employer->jobPostings()->create([
            'company_id' => $company->id,
            'title' => 'Expired role',
            'description' => 'Past its deadline.',
            'job_type' => 'full_time',
            'deadline' => now()->subDay()->toDateString(),
        ]);

        $this->get('/')->assertOk()->assertInertia(fn (Assert $page) => $page
            ->component('jobs/index')
            ->where('canUploadResume', false)
            ->has('jobs', 1)
            ->where('jobs.0.id', $visibleJob->id)
            ->where('jobs.0.title', 'Frontend Engineer')
            ->where('jobs.0.company', 'Northstar Labs')
            ->where('jobs.0.location', 'Quezon City')
            ->where('jobs.0.workType', 'Full Time')
            ->where('jobs.0.workArrangement', 'Hybrid')
        );

        $this->get('/?search=Northstar')->assertOk()->assertInertia(fn (Assert $page) => $page
            ->where('search', 'Northstar')
            ->has('jobs', 1)
            ->where('jobs.0.id', $visibleJob->id)
        );

        $this->get('/?search=missing')->assertOk()->assertInertia(fn (Assert $page) => $page
            ->where('search', 'missing')
            ->has('jobs', 0)
        );
    }
}
