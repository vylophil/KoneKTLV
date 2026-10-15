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
            'company' => $job->external_company_name ?? $job->company->name ?? 'Company not specified',
            'location' => $job->location ?? $job->company->location ?? 'Philippines — location not specified',
            'workType' => $job->job_type ? Str::of($job->job_type)->replace('_', ' ')->title()->toString() : 'Type not specified',
            'workArrangement' => $job->work_arrangement ? Str::of($job->work_arrangement)->replace('_', ' ')->title()->toString() : 'Arrangement not specified',
            'description' => $full ? $job->description : Str::limit($job->description, 180),
            'requirements' => $job->requirements,
            'responsibilities' => $job->responsibilities,
            'deadline' => $job->deadline?->toDateString(),
            'salaryMin' => $job->salary_min,
            'salaryMax' => $job->salary_max,
            'currency' => $job->salary_currency,
            'companyDescription' => $full ? $job->company?->description : null,
            'source' => $job->source,
            'publisher' => $job->publisher,
            'applyUrl' => $job->apply_url,
            'salaryPeriod' => $job->salary_period,
            'salaryText' => $job->salary_text,
            'fetchedAt' => $job->fetched_at?->toIso8601String(),
            'isOpen' => $job->is_active && ($job->deadline === null || $job->deadline->greaterThanOrEqualTo(today())),
        ];
    }
}
