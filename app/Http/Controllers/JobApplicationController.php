<?php

namespace App\Http\Controllers;

use App\Models\JobApplication;
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
        return [
            'job_id' => $record ? ['prohibited'] : ['required', 'integer', Rule::exists('job_postings', 'id')->where('is_active', true)->where(fn ($query) => $query->whereNull('deadline')->orWhere('deadline', '>=', now()->toDateString())), Rule::unique('job_applications', 'job_id')->where('user_id', $this->user($request)->getKey())],
            'status' => ['sometimes', Rule::in(['opened', 'applied', 'interview', 'offered', 'accepted', 'rejected', 'withdrawn'])],
            'notes' => ['nullable', 'string', 'max:10000'],
            'cover_letter' => ['nullable', 'string', 'max:10000'],
            'resume_url' => ['nullable', 'url:http,https', 'max:500'],
            'self_reported' => ['prohibited'], 'opened_at' => ['prohibited'], 'resume_path' => ['prohibited'],
        ];
    }
}
