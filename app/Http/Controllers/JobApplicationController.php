<?php

namespace App\Http\Controllers;

use App\Models\JobApplication;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/** @extends ResourceController<JobApplication> */
class JobApplicationController extends ResourceController
{
    /** @var class-string<JobApplication> */
    protected string $model = JobApplication::class;

    protected ?string $actorColumn = 'user_id';

    /** @return array<string, list<mixed>> */
    protected function rules(Request $request, ?Model $record = null): array
    {
        if ($record !== null) {
            if ($record->getAttribute('user_id') !== $this->user($request)->getKey()) {
                return ['status' => ['required', Rule::in(['reviewing', 'shortlisted', 'interview', 'offered', 'rejected'])], 'job_id' => ['prohibited'], 'cover_letter' => ['prohibited'], 'resume_url' => ['prohibited']];
            }

            return ['job_id' => ['prohibited'], 'cover_letter' => ['nullable', 'string', 'max:10000'], 'resume_url' => ['nullable', 'url:http,https', 'max:500'],
                'status' => ['required', Rule::in($record->getAttribute('status') === 'offered' ? ['withdrawn', 'accepted'] : ['withdrawn'])]];
        }

        return [
            'job_id' => ['required', 'integer', Rule::exists('job_postings', 'id')->where('is_active', true)->where(fn ($query) => $query->whereNull('deadline')->orWhere('deadline', '>=', date('Y-m-d'))), Rule::unique('job_applications', 'job_id')->where('user_id', $this->user($request)->getKey())],
            'cover_letter' => ['nullable', 'string', 'max:10000'],
            'resume_url' => ['nullable', 'url:http,https', 'max:500'],
            'status' => ['prohibited'],
        ];
    }

    /** @return Builder<JobApplication> */
    protected function query(Request $request): Builder
    {
        $user = $this->user($request);

        return JobApplication::query()->where(fn ($query) => $query->where('user_id', $user->getKey())
            ->orWhereHas('jobPosting', fn ($job) => $job->where('employer_id', $user->getKey())));
    }

    protected function authorizeAction(Request $request, string $action, ?Model $record = null): void
    {
        $user = $this->user($request);
        if (in_array($action, ['create', 'store'], true)) {
            abort_unless($user->getAttribute('role') === 'job_seeker', 403);
        }
        if ($action === 'destroy' && $record !== null) {
            abort_unless($record->getAttribute('user_id') === $user->getKey(), 403);
        }
        if ($action === 'update' && $record !== null && $record->getAttribute('user_id') !== $user->getKey()) {
            abort_if(in_array($record->getAttribute('status'), ['withdrawn', 'accepted'], true), 409, 'This application is closed.');
        }
    }
}
