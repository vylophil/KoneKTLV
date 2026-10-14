<?php

namespace App\Http\Controllers;

use App\Models\SavedJob;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/** @extends ResourceController<SavedJob> */
class SavedJobController extends ResourceController
{
    /** @var class-string<SavedJob> */
    protected string $model = SavedJob::class;

    protected ?string $actorColumn = 'user_id';

    /** @return array<string, list<mixed>> */
    protected function rules(Request $request, ?Model $record = null): array
    {
        return [
            'job_id' => ['required', 'integer', 'min:0', Rule::exists('job_postings', 'id'), Rule::unique('saved_jobs', 'job_id')->where('user_id', $this->user($request)->getKey())->ignore($record)],
        ];
    }
}
