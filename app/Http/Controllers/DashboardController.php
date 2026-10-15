<?php

namespace App\Http\Controllers;

use App\Models\Education;
use App\Models\Experience;
use App\Models\Skill;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $user = $request->user();
        abort_unless($user instanceof User, 403);

        $profile = $user->profile;
        $skills = $user->skills()->orderBy('skills.name')->get(['skills.id', 'skills.name']);
        $experience = $user->workExperience()
            ->orderByDesc('is_current')
            ->orderByDesc('start_date')
            ->get(['id', 'company_name', 'job_title', 'location', 'start_date', 'end_date', 'is_current', 'description']);
        $education = $user->education()
            ->orderByDesc('is_current')
            ->orderByDesc('start_date')
            ->get(['id', 'institution', 'degree', 'field_of_study', 'start_date', 'end_date', 'is_current', 'grade', 'description']);
        $completionChecks = [
            filled($profile?->headline),
            filled($profile?->bio),
            filled($profile?->location),
            filled($profile?->phone),
            filled($profile?->industry),
            $profile?->resume_path !== null,
            $skills->isNotEmpty(),
            $experience->isNotEmpty(),
            $education->isNotEmpty(),
        ];

        return Inertia::render('dashboard/index', [
            'user' => [
                'name' => $user->name,
                'email' => $user->email,
                'role' => str($user->role)->replace('_', ' ')->title()->toString(),
            ],
            'profile' => $profile === null ? null : [
                'headline' => $profile->headline,
                'bio' => $profile->bio,
                'location' => $profile->location,
                'phone' => $profile->phone,
                'website' => $profile->website,
                'linkedinUrl' => $profile->linkedin_url,
                'githubUrl' => $profile->github_url,
                'industry' => $profile->industry,
                'yearsOfExperience' => $profile->years_of_experience,
            ],
            'profileCompletion' => (int) round((count(array_filter($completionChecks)) / count($completionChecks)) * 100),
            'resume' => $profile?->resume_path === null ? null : [
                'name' => $profile->resume_original_name ?? 'resume.pdf',
                'downloadUrl' => route('profile.resume.download'),
            ],
            'skills' => $skills
                ->map(fn (Skill $skill): array => [
                    'name' => $skill->name,
                    'proficiency' => $skill->pivot->proficiency_level,
                ]),
            'experience' => $experience
                ->map(fn (Experience $item): array => [
                    'id' => $item->id,
                    'company' => $item->company_name,
                    'jobTitle' => $item->job_title,
                    'location' => $item->location,
                    'startDate' => $item->start_date?->format('M Y'),
                    'endDate' => $item->is_current ? 'Present' : $item->end_date?->format('M Y'),
                    'description' => $item->description,
                ]),
            'education' => $education
                ->map(fn (Education $item): array => [
                    'id' => $item->id,
                    'institution' => $item->institution,
                    'degree' => str($item->degree)->replace('_', ' ')->title()->toString(),
                    'fieldOfStudy' => $item->field_of_study,
                    'startDate' => $item->start_date?->format('Y'),
                    'endDate' => $item->is_current ? 'Present' : $item->end_date?->format('Y'),
                    'grade' => $item->grade,
                ]),
            'stats' => [
                'applications' => $user->jobApplications()->count(),
                'savedJobs' => $user->savedJobs()->count(),
                'matches' => $user->jobMatches()->count(),
            ],
        ]);
    }
}
