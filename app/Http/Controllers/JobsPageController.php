<?php

namespace App\Http\Controllers;

use App\Models\JobApplication;
use App\Models\JobPosting;
use App\Models\User;
use App\Services\JSearch;
use App\Services\JSearchUnavailable;
use App\Support\JobView;
use Illuminate\Contracts\Cache\LockTimeoutException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class JobsPageController extends Controller
{
    public function index(Request $request, JSearch $source): Response
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'arrangement' => ['nullable', Rule::in(['remote', 'hybrid', 'on_site'])],
            'type' => ['nullable', Rule::in(['full_time', 'part_time', 'contract', 'internship', 'freelance'])],
            'sort' => ['nullable', Rule::in(['newest', 'title'])],
            'page' => ['nullable', 'integer', 'min:1'],
            'location' => ['nullable', 'string', 'max:100'],
            'cursor' => ['nullable', 'string', 'max:16000'],
            'cached' => ['nullable', 'boolean'],
        ]);
        $search = trim($filters['search'] ?? '');
        $sourceError = null;
        if (config('services.jsearch.enabled') && ! $request->boolean('cached')) {
            try {
                $result = $source->search($filters);
                $items = $result['jobs'];
                if (($filters['sort'] ?? '') === 'title') {
                    $items = $items->sortBy('title')->values();
                }

                return Inertia::render('jobs/index', [
                    'filters' => [...$filters, 'search' => $search],
                    'jobs' => ['data' => $items->map(fn (JobPosting $job) => JobView::data($job)), 'total' => $items->count(), 'from' => null, 'to' => null,
                        'current_page' => 1, 'last_page' => 1, 'prev_page_url' => null, 'next_page_url' => null],
                    'liveSearch' => true, 'sourceError' => null, 'providerEnabled' => true,
                    'nextSearchUrl' => $result['cursor'] ? route('jobs.index', [...$filters, 'cursor' => $result['cursor']]) : null,
                    'fetchedAt' => $result['fetchedAt'],
                ]);
            } catch (JSearchUnavailable $exception) {
                $sourceError = $exception->getMessage();
            } catch (LockTimeoutException) {
                $sourceError = 'This search is already loading. Please try again shortly.';
            }
        }
        $jobs = JobPosting::query()->with('company')->where('is_active', true)
            ->where(fn (Builder $query) => $query->whereNull('deadline')->orWhereDate('deadline', '>=', today()))
            ->when($search !== '', fn (Builder $query) => $query->where(function (Builder $query) use ($search): void {
                $query->where('title', 'like', "%{$search}%")->orWhere('description', 'like', "%{$search}%")
                    ->orWhere('location', 'like', "%{$search}%")->orWhere('external_company_name', 'like', "%{$search}%")
                    ->orWhereHas('company', fn (Builder $company) => $company->where('name', 'like', "%{$search}%"));
            }))
            ->when($filters['arrangement'] ?? null, fn (Builder $query, string $value) => $query->where('work_arrangement', $value))
            ->when($filters['type'] ?? null, fn (Builder $query, string $value) => $query->where('job_type', $value));
        $jobs->when($filters['location'] ?? null, fn (Builder $query, string $value) => $query->where('location', 'like', '%'.$value.'%'));
        $jobs->when(config('services.jsearch.enabled'), fn (Builder $query) => $query->where('source', 'jsearch'));
        if (($filters['sort'] ?? 'newest') === 'title') {
            $jobs->orderBy('title')->orderBy('id');
        } else {
            $jobs->latest()->orderByDesc('id');
        }

        $page = $jobs->paginate(12)->withQueryString();
        if (config('services.jsearch.enabled')) {
            $page->appends(['cached' => 1]);
        }

        return Inertia::render('jobs/index', [
            'filters' => [...$filters, 'search' => $search],
            'jobs' => $page->through(fn (JobPosting $job) => JobView::data($job)),
            'liveSearch' => false, 'sourceError' => $sourceError, 'nextSearchUrl' => null, 'fetchedAt' => null,
            'providerEnabled' => (bool) config('services.jsearch.enabled'),
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
        abort_unless($job->is_active || $user->savedJobs()->where('job_id', $job->id)->exists() || $user->jobApplications()->where('job_id', $job->id)->exists(), 404);
        $application = $user->jobApplications()->firstOrCreate(['job_id' => $job->id], ['status' => 'applied', 'self_reported' => true]);
        if ($application->status === 'opened') {
            $application->forceFill(['status' => 'applied', 'applied_at' => now(), 'self_reported' => true])->save();
        }

        return to_route('applications.index')->with('success', 'Added to your personal tracker. KoneKT does not send applications to employers.');
    }

    public function out(Request $request, JobPosting $job): RedirectResponse
    {
        $url = $job->apply_url;
        abort_unless($job->is_active && is_string($url) && filter_var($url, FILTER_VALIDATE_URL)
            && in_array(parse_url($url, PHP_URL_SCHEME), ['http', 'https'], true), 404, 'The original application link is unavailable.');
        if ($request->user()?->is_active) {
            $record = $request->user()->jobApplications()->firstOrCreate(['job_id' => $job->id], ['status' => 'opened', 'applied_at' => null, 'self_reported' => false]);
            if ($record->wasRecentlyCreated) {
                $record->applied_at = null;
            }
            $record->forceFill(['opened_at' => now()])->save();
        }

        return redirect()->away($url);
    }

    public function updateApplication(Request $request, JobApplication $application): RedirectResponse
    {
        abort_unless($application->user_id === $this->seeker($request)->id, 404);
        $data = $request->validate([
            'status' => ['required', Rule::in(['opened', 'applied', 'interview', 'offered', 'accepted', 'rejected', 'withdrawn'])],
            'notes' => ['nullable', 'string', 'max:10000'],
        ]);
        $application->fill($data);
        $application->self_reported = true;
        if ($data['status'] !== 'opened' && $application->applied_at === null) {
            $application->applied_at = now();
        }
        $application->save();

        return back()->with('success', 'Your application tracker has been updated.');
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
                'notes' => $item->notes, 'selfReported' => $item->self_reported,
                'resumeUrl' => $item->resume_path ? route('applications.resume', $item) : null,
                'job' => JobView::data($item->jobPosting),
            ]),
        ]);
    }

    public function applicationResume(Request $request, JobApplication $application): BinaryFileResponse
    {
        $user = $request->user();
        abort_unless($user instanceof User && $user->is_active && $application->user_id === $user->id, 404);
        abort_unless(is_string($application->resume_path) && Storage::disk('local')->exists($application->resume_path), 404);

        return response()->download(Storage::disk('local')->path($application->resume_path), 'application-resume.pdf', ['Content-Type' => 'application/pdf']);
    }

    private function seeker(Request $request): User
    {
        $user = $request->user();
        abort_unless($user instanceof User && $user->is_active, 403);

        return $user;
    }
}
