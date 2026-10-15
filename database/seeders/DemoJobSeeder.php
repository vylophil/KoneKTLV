<?php

namespace Database\Seeders;

use App\Models\JobPosting;
use Illuminate\Database\Seeder;

class DemoJobSeeder extends Seeder
{
    public function run(): void
    {
        if (! app()->environment(['local', 'testing'])) {
            return;
        }
        foreach ([
            ['Junior Frontend Developer', 'Help build accessible experiences with React and TypeScript.', 'Manila, Philippines', 'hybrid'],
            ['Laravel Backend Developer', 'Build reliable APIs with PHP, Laravel, and relational databases.', 'Remote, Philippines', 'remote'],
            ['Product Designer', 'Turn research and product goals into clear web and mobile interfaces.', 'Makati, Philippines', 'on_site'],
        ] as [$title, $description, $location, $arrangement]) {
            $key = hash('sha256', 'demo:'.$title);
            $job = JobPosting::where('source_key', $key)->first() ?? new JobPosting;
            $job->forceFill([
                'source' => 'demo', 'source_key' => $key, 'external_company_name' => 'KoneKT Demo Studio',
                'title' => $title, 'description' => $description.' Sample data for local preview only.',
                'location' => $location, 'job_type' => 'full_time', 'work_arrangement' => $arrangement,
                'is_active' => true,
            ])->save();
        }
    }
}
