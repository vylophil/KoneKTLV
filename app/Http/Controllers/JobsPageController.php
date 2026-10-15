<?php

namespace App\Http\Controllers;

use App\Models\JobApplication;
use App\Models\JobPosting;
use App\Models\User;
use App\Support\JobView;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Throwable;

class JobsPageController extends Controller
{
    public function index(Request $request): Response
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'arrangement' => ['nullable', Rule::in(['remote', 'hybrid', 'on_site'])],
            'type' => ['nullable', Rule::in(['full_time', 'part_time', 'contract', 'internship', 'freelance'])],
            'sort' => ['nullable', Rule::in(['newest', 'title'])],
            'page' => ['nullable', 'integer', 'min:1'],
        ]);
        $search = trim($filters['search'] ?? '');
        $jobs = JobPosting::query()->with('company')->where('is_active', true)
            ->where(fn (Builder $query) => $query->whereNull('deadline')->orWhereDate('deadline', '>=', today()))
            ->when($search !== '', fn (Builder $query) => $query->where(function (Builder $query) use ($search): void {
                $query->where('title', 'like', "%{$search}%")->orWhere('description', 'like', "%{$search}%")
                    ->orWhere('location', 'like', "%{$search}%")
                    ->orWhereHas('company', fn (Builder $company) => $company->where('name', 'like', "%{$search}%"));
            }))
            ->when($filters['arrangement'] ?? null, fn (Builder $query, string $value) => $query->where('work_arrangement', $value))
            ->when($filters['type'] ?? null, fn (Builder $query, string $value) => $query->where('job_type', $value));
        if (($filters['sort'] ?? 'newest') === 'title') {
            $jobs->orderBy('title')->orderBy('id');
        } else {
            $jobs->latest()->orderByDesc('id');
        }

        return Inertia::render('jobs/index', [
            'filters' => [...$filters, 'search' => $search],
            'jobs' => $jobs->paginate(12)->withQueryString()->through(fn (JobPosting $job) => JobView::data($job)),
        ]);
    }

    public function show(Request $request, JobPosting $job): Response
    {
        $user = $request->user();
        $application = $user?->jobApplications()->where('job_id', $job->id)->first();
        // Closed listings stay visible to people who saved or applied to them.
        abort_unless($job->is_active || $application || $user?->savedJobs()->where('job_id', $job->id)->exists(), 404);

        return Inertia::render('jobs/show', [
            'job' => JobView::data($job->load('company'), true),
            'isSaved' => $user?->savedJobs()->where('job_id', $job->id)->exists() ?? false,
            'application' => $application === null ? null : ['id' => $application->id, 'status' => $application->status],
            'hasResume' => is_string($user?->profile?->resume_path) && Storage::disk('local')->exists($user->profile->resume_path),
            'backUrl' => '/jobs'.($request->getQueryString() ? '?'.$request->getQueryString() : ''),
        ]);
    }

    public function apply(Request $request, JobPosting $job): RedirectResponse
    {
        $user = $this->seeker($request);
        abort_unless($job->is_active && ($job->deadline === null || $job->deadline->greaterThanOrEqualTo(today())), 409, 'This role is no longer accepting applications.');
        $data = $request->validate(['cover_letter' => ['nullable', 'string', 'max:10000']]);
        if ($user->jobApplications()->where('job_id', $job->id)->exists()) {
            throw ValidationException::withMessages(['application' => 'You have already applied to this role. View it in Applications.']);
        }
        $resume = $user->profile?->resume_path;
        if (! is_string($resume) || ! Storage::disk('local')->exists($resume)) {
            throw ValidationException::withMessages(['application' => 'Upload a resume in your profile before applying.']);
        }
        $copy = 'applications/'.$user->id.'/'.Str::uuid().'.pdf';
        abort_unless(Storage::disk('local')->copy($resume, $copy), 500, 'We could not attach your resume. Please try again.');
        try {
            $application = new JobApplication($data);
            $application->job_id = $job->id;
            $application->resume_path = $copy;
            $user->jobApplications()->save($application);
        } catch (Throwable $exception) {
            Storage::disk('local')->delete($copy);
            if ($exception instanceof UniqueConstraintViolationException) {
                throw ValidationException::withMessages(['application' => 'You have already applied to this role.']);
            }
            throw $exception;
        }

        return to_route('applications.index')->with('success', 'Application sent. Your resume has been attached.');
    }

    public function save(Request $request, JobPosting $job): RedirectResponse
    {
        $user = $this->seeker($request);
        abort_unless($job->is_active && ($job->deadline === null || $job->deadline->greaterThanOrEqualTo(today())), 409, 'This role is no longer available.');
        $user->savedJobs()->firstOrCreate(['job_id' => $job->id]);

        return back()->with('success', 'Job saved.');
    }

    public function unsave(Request $request, JobPosting $job): RedirectResponse
    {
        $this->seeker($request)->savedJobs()->where('job_id', $job->id)->delete();

        return back()->with('success', 'Job removed from saved jobs.');
    }

    public function saved(Request $request): Response
    {
        $records = $this->seeker($request)->savedJobs()->with('jobPosting.company')->latest('saved_at')->paginate(12);

        return Inertia::render('jobs/saved', [
            'jobs' => $records->withQueryString()->through(fn ($saved) => JobView::data($saved->jobPosting)),
        ]);
    }

    public function applications(Request $request): Response
    {
        $records = $this->seeker($request)->jobApplications()->with('jobPosting.company')->latest('applied_at')->paginate(12);

        return Inertia::render('jobs/applications', [
            'applications' => $records->withQueryString()->through(fn (JobApplication $item) => [
                'id' => $item->id, 'status' => $item->status,
                'appliedAt' => $item->applied_at?->toDateString(),
                'coverLetter' => $item->cover_letter,
                'resumeUrl' => $item->resume_path ? route('applications.resume', $item) : null,
                'job' => JobView::data($item->jobPosting),
            ]),
        ]);
    }

    public function applicationResume(Request $request, JobApplication $application): BinaryFileResponse
    {
        $user = $request->user();
        abort_unless($user instanceof User && $user->is_active && ($application->user_id === $user->id || $application->jobPosting->employer_id === $user->id), 404);
        abort_unless(is_string($application->resume_path) && Storage::disk('local')->exists($application->resume_path), 404);

        return response()->download(Storage::disk('local')->path($application->resume_path), 'application-resume.pdf', ['Content-Type' => 'application/pdf']);
    }

    private function seeker(Request $request): User
    {
        $user = $request->user();
        abort_unless($user instanceof User && $user->is_active && $user->role === 'job_seeker', 403);

        return $user;
    }
}
