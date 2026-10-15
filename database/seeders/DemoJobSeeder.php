<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DemoJobSeeder extends Seeder
{
    public function run(): void
    {
        if (! app()->environment(['local', 'testing'])) {
            return;
        }

        $employer = User::query()->firstOrCreate(
            ['email' => 'demo-employer@konekt.local'],
            ['name' => 'KoneKT Demo Employer', 'password' => Str::random(40)],
        );
        $company = $employer->companies()->firstOrCreate(
            ['name' => 'KoneKT Demo Studio'],
            [
                'description' => 'Sample employer for the local KoneKT preview.',
                'industry' => 'Technology',
                'location' => 'Manila, Philippines',
            ],
        );

        $jobs = [
            [
                'title' => 'Junior Frontend Developer',
                'description' => 'Help build accessible, responsive experiences with React and TypeScript. This sample listing is for local preview only.',
                'location' => 'Manila, Philippines',
                'job_type' => 'full_time',
                'work_arrangement' => 'hybrid',
                'experience_level' => 'entry',
            ],
            [
                'title' => 'Laravel Backend Developer',
                'description' => 'Build reliable APIs and product workflows with PHP, Laravel, and relational databases. This sample listing is for local preview only.',
                'location' => 'Remote, Philippines',
                'job_type' => 'full_time',
                'work_arrangement' => 'remote',
                'experience_level' => 'mid',
            ],
            [
                'title' => 'Product Designer',
                'description' => 'Turn research and product goals into clear, thoughtful interfaces for web and mobile. This sample listing is for local preview only.',
                'location' => 'Makati, Philippines',
                'job_type' => 'contract',
                'work_arrangement' => 'on_site',
                'experience_level' => 'mid',
            ],
        ];

        foreach ($jobs as $job) {
            $employer->jobPostings()->updateOrCreate(
                ['company_id' => $company->id, 'title' => $job['title']],
                [...$job, 'is_active' => true, 'deadline' => null],
            );
        }
    }
}
