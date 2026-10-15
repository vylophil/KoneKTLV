<?php

namespace App\Http\Controllers;

use App\Models\Experience;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/** @extends ResourceController<Experience> */
class ExperienceController extends ResourceController
{
    /** @var class-string<Experience> */
    protected string $model = Experience::class;

    protected ?string $actorColumn = 'user_id';

    /** @return array<string, list<mixed>> */
    protected function rules(Request $request, ?Model $record = null): array
    {
        $start = $this->dateBoundary($request, 'start_date', $record);
        $end = $this->dateBoundary($request, 'end_date', $record);

        return [
            'company_name' => ['required', 'string', 'max:255'],
            'job_title' => ['required', 'string', 'max:255'],
            'location' => ['nullable', 'string', 'max:255'],
            'start_date' => ['bail', 'required', 'regex:/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/D', 'date_format:Y-m-d', Rule::when($end !== null, 'before_or_equal:'.$end)],
            'end_date' => ['bail', 'nullable', 'regex:/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/D', 'date_format:Y-m-d', Rule::when($start !== null, 'after_or_equal:'.$start)],
            'is_current' => ['nullable', 'boolean'],
            'description' => ['nullable', 'string', 'max:10000'],
        ];
    }
}
