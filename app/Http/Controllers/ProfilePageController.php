<?php

namespace App\Http\Controllers;

use App\Models\Skill;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ProfilePageController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $this->user($request);
        $profile = $user->profile;
        $fields = ['headline', 'bio', 'location', 'phone', 'website', 'linkedin_url', 'github_url', 'industry', 'years_of_experience'];
        $section = $request->query('section', 'details');

        return Inertia::render('profile/index', [
            'section' => in_array($section, ['details', 'experience', 'education', 'skills', 'resume'], true) ? $section : 'details',
            'profile' => $profile?->only($fields),
            'resume' => $profile?->resume_path ? ['name' => $profile->resume_original_name, 'downloadUrl' => route('profile.resume.download')] : null,
            'experience' => $user->workExperience()->latest('start_date')->get()->map(fn ($item) => [
                ...$item->only(['id', 'company_name', 'job_title', 'location', 'description', 'is_current']),
                'start_date' => $item->start_date->toDateString(), 'end_date' => $item->end_date?->toDateString(),
            ]),
            'education' => $user->education()->latest('start_date')->get()->map(fn ($item) => [
                ...$item->only(['id', 'institution', 'degree', 'field_of_study', 'grade', 'description', 'is_current']),
                'start_date' => $item->start_date->toDateString(), 'end_date' => $item->end_date?->toDateString(),
            ]),
            'skills' => $user->userSkillEntries()->with('skill')->get()->map(fn ($item) => [
                'id' => $item->id, 'name' => $item->skill->name, 'proficiency_level' => $item->proficiency_level,
            ]),
            'skillOptions' => Skill::query()->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $profile = $this->user($request)->profile()->first();
        if ($profile) {
            app(ProfileController::class)->update($request, (string) $profile->id);
        } else {
            app(ProfileController::class)->store($request);
        }
        $request->user()->unsetRelation('profile');

        return back()->with('success', 'Profile details saved.');
    }

    public function storeCareer(Request $request, string $kind): RedirectResponse
    {
        $this->user($request);
        $this->career($kind)->store($request);

        return back()->with('success', ucfirst($kind).' added.');
    }

    public function updateCareer(Request $request, string $kind, string $record): RedirectResponse
    {
        $this->user($request);
        $this->career($kind)->update($request, $record);

        return back()->with('success', ucfirst($kind).' updated.');
    }

    public function destroyCareer(Request $request, string $kind, string $record): RedirectResponse
    {
        $this->user($request);
        $this->career($kind)->destroy($request, $record);

        return back()->with('success', ucfirst($kind).' removed.');
    }

    public function addSkill(Request $request): RedirectResponse
    {
        $this->user($request);
        app(UserSkillController::class)->store($request);

        return back()->with('success', 'Skill added.');
    }

    public function removeSkill(Request $request, string $record): RedirectResponse
    {
        $this->user($request);
        app(UserSkillController::class)->destroy($request, $record);

        return back()->with('success', 'Skill removed.');
    }

    private function career(string $kind): ExperienceController|EducationController
    {
        return match ($kind) {
            'experience' => app(ExperienceController::class),
            'education' => app(EducationController::class),
            default => abort(404),
        };
    }

    private function user(Request $request): User
    {
        $user = $request->user();
        abort_unless($user instanceof User && $user->is_active, 403);

        return $user;
    }
}
