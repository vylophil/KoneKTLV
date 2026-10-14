<?php

namespace App\Http\Controllers;

use App\Models\JobMatch;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/** @extends ResourceController<JobMatch> */
class JobMatchController extends ResourceController
{
    /** @var class-string<JobMatch> */
    protected string $model = JobMatch::class;

    protected ?string $actorColumn = 'user_id';

    /** @return array<string, list<mixed>> */
    protected function rules(Request $request, ?Model $record = null): array
    {
        return [
            'job_id' => ['required', 'integer', 'min:0', Rule::exists('job_postings', 'id'), Rule::unique('job_matches', 'job_id')->where('user_id', $this->user($request)->getKey())->ignore($record)],
            'match_score' => ['numeric', 'min:0', 'max:100'],
            'skill_score' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'experience_score' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'education_score' => ['nullable', 'numeric', 'min:0', 'max:100'],
        ];
    }

    protected function authorizeAction(Request $request, string $action, ?Model $record = null): void
    {
        $user = $this->user($request);
        if (in_array($action, ['create', 'store', 'edit', 'update', 'destroy'], true)) {
            abort_unless($user->can('manage job matches'), 403);
        }
    }
}
