<?php

namespace App\Support;

use App\Models\JobPosting;
use Illuminate\Support\Str;

class JobView
{
    /** @return array<string, mixed> */
    public static function data(JobPosting $job, bool $full = false): array
    {
        return [
            'id' => $job->id,
            'title' => $job->title,
            'company' => $job->company->name,
            'location' => $job->location ?? $job->company->location ?? 'Location not specified',
            'workType' => Str::of($job->job_type)->replace('_', ' ')->title()->toString(),
            'workArrangement' => Str::of($job->work_arrangement ?? 'on_site')->replace('_', ' ')->title()->toString(),
            'description' => $full ? $job->description : Str::limit($job->description, 180),
            'requirements' => $job->requirements,
            'responsibilities' => $job->responsibilities,
            'deadline' => $job->deadline?->toDateString(),
            'salaryMin' => $job->salary_min,
            'salaryMax' => $job->salary_max,
            'currency' => $job->salary_currency,
            'companyDescription' => $full ? $job->company->description : null,
            'isOpen' => $job->is_active && ($job->deadline === null || $job->deadline->greaterThanOrEqualTo(today())),
        ];
    }
}
