<?php

namespace App\Http\Controllers;

use App\Models\JobPosting;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

/** @extends ResourceController<JobPosting> */
class JobPostingController extends ResourceController
{
    /** @var class-string<JobPosting> */
    protected string $model = JobPosting::class;

    /** @return array<string, list<mixed>> */
    protected function rules(Request $request, ?Model $record = null): array
    {
        return [];
    }

    protected function authorizeAction(Request $request, string $action, ?Model $record = null): void
    {
        $this->user($request);
        abort_unless(in_array($action, ['index', 'show'], true), 405, 'Manual job posting has been retired.');
    }

    /** @return Builder<JobPosting> */
    protected function query(Request $request): Builder
    {
        return JobPosting::query()->with('company:id,name,logo_url,location')->where('is_active', true);
    }
}
