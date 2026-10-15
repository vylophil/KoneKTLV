<?php

namespace App\Http\Controllers;

use App\Models\JobSkill;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

/** @extends ResourceController<JobSkill> */
class JobSkillController extends ResourceController
{
    /** @var class-string<JobSkill> */
    protected string $model = JobSkill::class;

    /** @return array<string, list<mixed>> */
    protected function rules(Request $request, ?Model $record = null): array
    {
        return [];
    }

    protected function authorizeAction(Request $request, string $action, ?Model $record = null): void
    {
        $this->user($request);
        abort_unless(in_array($action, ['index', 'show'], true), 405, 'Manual job tagging has been retired.');
    }

    /** @return Builder<JobSkill> */
    protected function query(Request $request): Builder
    {
        return JobSkill::query()->whereHas('jobPosting', fn ($job) => $job->where('is_active', true));
    }
}
