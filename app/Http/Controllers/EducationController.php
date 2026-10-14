<?php

namespace App\Http\Controllers;

use App\Models\Education;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/** @extends ResourceController<Education> */
class EducationController extends ResourceController
{
    /** @var class-string<Education> */
    protected string $model = Education::class;

    protected ?string $actorColumn = 'user_id';

    /** @return array<string, list<mixed>> */
    protected function rules(Request $request, ?Model $record = null): array
    {
        return [
            'institution' => ['required', 'string', 'max:255'],
            'degree' => ['required', Rule::in(['high_school', 'associate', 'bachelors', 'masters', 'doctorate', 'certification', 'other'])],
            'field_of_study' => ['nullable', 'string', 'max:255'],
            'start_date' => ['required', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:'.$request->input('start_date', $record?->getAttribute('start_date'))],
            'is_current' => ['nullable', 'boolean'],
            'grade' => ['nullable', 'string', 'max:50'],
            'description' => ['nullable', 'string', 'max:10000'],
        ];
    }
}
