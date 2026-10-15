<?php

namespace App\Http\Controllers;

use App\Models\JobPosting;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class HomeController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $profile = $request->user()?->profile;
        $user = $request->user();
        $search = $request->string('search')->trim()->limit(100)->toString();
        $jobs = JobPosting::query()
            ->with('company:id,name,location')
            ->where('is_active', true)
            ->where(fn (Builder $query) => $query->whereNull('deadline')->orWhereDate('deadline', '>=', today()))
            ->when($search !== '', function (Builder $query) use ($search): void {
                $query->where(function (Builder $query) use ($search): void {
                    $query->where('title', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%")
                        ->orWhere('location', 'like', "%{$search}%")
                        ->orWhereHas('company', fn (Builder $company) => $company->where('name', 'like', "%{$search}%"));
                });
            })
            ->latest()
            ->limit(12)
            ->get()
            ->map(fn (JobPosting $job): array => [
                'id' => $job->id,
                'title' => $job->title,
                'company' => $job->company->name,
                'location' => $job->location ?? $job->company->location ?? 'Location not specified',
                'workType' => Str::of($job->job_type)->replace('_', ' ')->title()->toString(),
                'workArrangement' => Str::of($job->work_arrangement ?? 'on_site')->replace('_', ' ')->title()->toString(),
                'description' => Str::limit($job->description, 180),
                'requirements' => $job->requirements,
                'responsibilities' => $job->responsibilities,
            ]);
        $matchedJobs = $user !== null && $profile?->resume_path !== null
            ? $user->jobMatches()
                ->whereHas('jobPosting', fn (Builder $query) => $query
                    ->where('is_active', true)
                    ->where(fn (Builder $query) => $query->whereNull('deadline')->orWhereDate('deadline', '>=', today())))
                ->with('jobPosting.company:id,name,location')
                ->orderByDesc('match_score')
                ->limit(8)
                ->get()
                ->map(fn ($match): array => [
                    'id' => $match->jobPosting->id,
                    'title' => $match->jobPosting->title,
                    'company' => $match->jobPosting->company->name,
                    'location' => $match->jobPosting->location ?? $match->jobPosting->company->location ?? 'Location not specified',
                    'workType' => Str::of($match->jobPosting->job_type)->replace('_', ' ')->title()->toString(),
                    'workArrangement' => Str::of($match->jobPosting->work_arrangement ?? 'on_site')->replace('_', ' ')->title()->toString(),
                    'description' => Str::limit($match->jobPosting->description, 180),
                    'requirements' => $match->jobPosting->requirements,
                    'responsibilities' => $match->jobPosting->responsibilities,
                    'matchScore' => (float) $match->match_score,
                ])
            : [];

        return Inertia::render('jobs/index', [
            'search' => $search,
            'jobs' => $jobs,
            'matchedJobs' => $matchedJobs,
            'hasResume' => $profile?->resume_path !== null,
            'canUploadResume' => $request->user() !== null,
            'resume' => $profile?->resume_path === null ? null : [
                'name' => $profile->resume_original_name ?? 'resume.pdf',
                'downloadUrl' => route('profile.resume.download'),
            ],
        ]);
    }
}
