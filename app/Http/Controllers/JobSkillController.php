<?php

namespace App\Http\Controllers;

use App\Models\JobSkill;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/** @extends ResourceController<JobSkill> */
class JobSkillController extends ResourceController
{
    /** @var class-string<JobSkill> */
    protected string $model = JobSkill::class;

    /** @return array<string, list<mixed>> */
    protected function rules(Request $request, ?Model $record = null): array
    {
        return [
            'job_id' => ['required', 'integer', 'min:0', Rule::exists('job_postings', 'id')->where('employer_id', $this->user($request)->getKey())],
            'skill_id' => ['required', 'integer', 'min:0', Rule::exists('skills', 'id'), Rule::unique('job_skills', 'skill_id')->where('job_id', $request->input('job_id', $record?->getAttribute('job_id')))->ignore($record)],
            'importance' => ['nullable', Rule::in(['required', 'preferred', 'nice_to_have'])],
        ];
    }

    protected function authorizeAction(Request $request, string $action, ?Model $record = null): void
    {
        abort_unless($this->user($request)->getAttribute('role') === 'employer', 403);
    }

    /** @return Builder<JobSkill> */
    protected function query(Request $request): Builder
    {
        return JobSkill::query()->whereHas('jobPosting', fn ($job) => $job->where('employer_id', $this->user($request)->getKey()));
    }
}
