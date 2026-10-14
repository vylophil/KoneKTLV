<?php

namespace App\Http\Controllers;

use App\Models\JobPosting;
use Closure;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/** @extends ResourceController<JobPosting> */
class JobPostingController extends ResourceController
{
    /** @var class-string<JobPosting> */
    protected string $model = JobPosting::class;

    protected ?string $actorColumn = 'employer_id';

    /** @return array<string, list<mixed>> */
    protected function rules(Request $request, ?Model $record = null): array
    {
        return [
            'company_id' => ['required', 'integer', 'min:0', Rule::exists('companies', 'id')->where('user_id', $this->user($request)->getKey())],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'max:10000'],
            'requirements' => ['nullable', 'string', 'max:10000'],
            'responsibilities' => ['nullable', 'string', 'max:10000'],
            'location' => ['nullable', 'string', 'max:255'],
            'job_type' => ['required', Rule::in(['full_time', 'part_time', 'contract', 'internship', 'freelance'])],
            'work_arrangement' => ['nullable', Rule::in(['on_site', 'remote', 'hybrid'])],
            'salary_min' => ['bail', 'nullable', 'numeric', 'min:0', 'max:9999999999.99', function (string $attribute, mixed $value, Closure $fail) use ($request, $record): void {
                $maximum = $request->input('salary_max', $record?->getAttribute('salary_max'));
                if (is_numeric($maximum) && (float) $value > (float) $maximum) {
                    $fail('Minimum salary cannot exceed the maximum salary.');
                }
            }],
            'salary_max' => ['bail', 'nullable', 'numeric', 'min:0', 'max:9999999999.99', function (string $attribute, mixed $value, Closure $fail) use ($request, $record): void {
                $minimum = $request->input('salary_min', $record?->getAttribute('salary_min') ?? 0);
                if (is_numeric($minimum) && (float) $value < (float) $minimum) {
                    $fail('Maximum salary must be at least the minimum salary.');
                }
            }],
            'salary_currency' => ['nullable', 'string', 'max:3'],
            'experience_level' => ['nullable', Rule::in(['entry', 'mid', 'senior', 'executive'])],
            'min_experience_years' => ['nullable', 'integer', 'min:0', 'max:80'],
            'education_requirement' => ['nullable', Rule::in(['none', 'high_school', 'associate', 'bachelors', 'masters', 'doctorate'])],
            'is_active' => ['nullable', 'boolean'],
            'deadline' => ['nullable', 'date'],
        ];
    }

    protected function authorizeAction(Request $request, string $action, ?Model $record = null): void
    {
        $user = $this->user($request);
        if (! in_array($action, ['index', 'show'], true)) {
            abort_unless($user->getAttribute('role') === 'employer', 403);
            if ($record !== null) {
                abort_unless($record->getAttribute('employer_id') === $user->getKey(), 403);
            }
        }
    }

    /** @return Builder<JobPosting> */
    protected function query(Request $request): Builder
    {
        return JobPosting::query()->where(fn ($query) => $query->where('is_active', true)
            ->orWhere('employer_id', $this->user($request)->getKey()));
    }
}
