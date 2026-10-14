<?php

namespace App\Http\Controllers;

use App\Models\Experience;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

/** @extends ResourceController<Experience> */
class ExperienceController extends ResourceController
{
    /** @var class-string<Experience> */
    protected string $model = Experience::class;

    protected ?string $actorColumn = 'user_id';

    /** @return array<string, list<mixed>> */
    protected function rules(Request $request, ?Model $record = null): array
    {
        return [
            'company_name' => ['required', 'string', 'max:255'],
            'job_title' => ['required', 'string', 'max:255'],
            'location' => ['nullable', 'string', 'max:255'],
            'start_date' => ['required', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:'.$request->input('start_date', $record?->getAttribute('start_date'))],
            'is_current' => ['nullable', 'boolean'],
            'description' => ['nullable', 'string', 'max:10000'],
        ];
    }
}
