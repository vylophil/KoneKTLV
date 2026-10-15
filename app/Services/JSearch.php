<?php

namespace App\Services;

use App\Models\JobPosting;
use Carbon\CarbonImmutable;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class JSearch
{
    /** @param array<string, mixed> $filters
     * @return array{jobs: Collection<int, JobPosting>, cursor: ?string, fetchedAt: string}
     */
    public function search(array $filters): array
    {
        $parameters = [
            'query' => trim(($filters['search'] ?? '').' jobs in '.($filters['location'] ?? 'Philippines')),
            'country' => 'ph', 'language' => 'en', 'num_pages' => 1,
        ];
        if (! empty($filters['cursor'])) {
            $parameters['cursor'] = $filters['cursor'];
        }
        if (($filters['arrangement'] ?? null) === 'remote') {
            $parameters['work_from_home'] = 'true';
        }
        $employment = ['full_time' => 'FULLTIME', 'part_time' => 'PARTTIME', 'contract' => 'CONTRACTOR', 'internship' => 'INTERN'];
        if (isset($employment[$filters['type'] ?? ''])) {
            $parameters['employment_types'] = $employment[$filters['type']];
        }
        $key = 'jsearch:search:'.hash('sha256', json_encode($parameters, JSON_THROW_ON_ERROR));
        // Cache public listing data only; credentials and user resumes never enter the payload.
        $payload = Cache::lock($key.':lock', 30)->block(5, function () use ($key, $parameters): array {
            return Cache::remember($key, (int) config('services.jsearch.cache_seconds'), function () use ($parameters): array {
                $credential = config('services.jsearch.key');
                if (! is_string($credential) || $credential === '') {
                    throw new JSearchUnavailable('Live job search is not configured yet.');
                }
                if (Cache::has('jsearch:cooldown')) {
                    throw new JSearchUnavailable('The job provider is temporarily unavailable. Please try again shortly.');
                }
                $usage = ['provider' => 'jsearch', 'period' => now()->format('Y-m')];
                DB::table('job_api_usage')->insertOrIgnore([...$usage, 'requests' => 0]);
                // Atomic and durable: clearing Laravel caches does not reset the monthly budget.
                if (! DB::table('job_api_usage')->where($usage)->where('requests', '<', max(1, (int) config('services.jsearch.monthly_limit')))->increment('requests')) {
                    throw new JSearchUnavailable('Live search has reached its monthly request allowance. Saved listings are still available.');
                }
                try {
                    $response = Http::acceptJson()->withHeaders(['x-api-key' => $credential])->withOptions(['allow_redirects' => false])->connectTimeout(5)->timeout(20)
                        ->get('https://api.openwebninja.com/jsearch/search-v2', $parameters);
                } catch (ConnectionException) {
                    Cache::put('jsearch:cooldown', true, 60);
                    throw new JSearchUnavailable('Live job search is temporarily unavailable. Please try again shortly.');
                }
                if (! $response->successful() || $response->json('status') !== 'OK' || ! is_array($response->json('data.jobs'))) {
                    Cache::put('jsearch:cooldown', true, 60);
                    // Do not surface upstream error bodies or request headers containing the API key.
                    throw new JSearchUnavailable($response->status() === 429
                        ? 'The job provider is busy or its allowance has been reached. Please try again later.'
                        : 'Live job search is temporarily unavailable. Your saved jobs are unaffected.');
                }

                return ['jobs' => array_slice($response->json('data.jobs'), 0, 10), 'cursor' => $response->json('data.cursor'), 'fetchedAt' => now()->toIso8601String()];
            });
        });
        $jobs = [];
        foreach ($payload['jobs'] as $item) {
            if (is_array($item)) {
                $job = $this->store($item, $payload['fetchedAt']);
                if ($job !== null) {
                    $jobs[] = $job;
                }
            }
        }

        return ['jobs' => collect($jobs), 'cursor' => is_string($payload['cursor']) ? $payload['cursor'] : null, 'fetchedAt' => $payload['fetchedAt']];
    }

    /** @param array<string, mixed> $item */
    private function store(array $item, string $fetchedAt): ?JobPosting
    {
        if (Validator::make($item, [
            'job_id' => ['required', 'string', 'max:16000'], 'job_title' => ['required', 'string', 'max:2000'],
            'job_country' => ['required', 'string', 'size:2'], 'employer_name' => ['nullable', 'string', 'max:2000'],
            'job_description' => ['nullable', 'string', 'max:200000'], 'job_location' => ['nullable', 'string', 'max:250'],
            'job_city' => ['nullable', 'string', 'max:250'], 'job_salary_string' => ['nullable', 'string', 'max:2000'],
            'job_salary' => ['nullable', 'string', 'max:2000'], 'job_publisher' => ['nullable', 'string', 'max:2000'],
            'job_employment_types' => ['nullable', 'array'], 'job_highlights' => ['nullable', 'array'],
            'job_employment_types.*' => ['string'], 'job_posted_at_datetime_utc' => ['nullable', 'string', 'max:100'],
        ])->fails()) {
            return null;
        }
        if (! is_string($item['job_id'] ?? null) || ! is_string($item['job_title'] ?? null)
            || strtoupper($item['job_country'] ?? '') !== 'PH') {
            return null;
        }
        $link = $item['job_apply_link'] ?? null;
        if (! is_string($link) || ! filter_var($link, FILTER_VALIDATE_URL) || ! in_array(parse_url($link, PHP_URL_SCHEME), ['http', 'https'], true)) {
            return null;
        }
        $job = JobPosting::firstOrNew(['source_key' => hash('sha256', 'jsearch:ph:'.$item['job_id'])]);
        $attributes = [
            'source_key' => hash('sha256', 'jsearch:ph:'.$item['job_id']),
            'source' => 'jsearch', 'external_id' => $item['job_id'], 'external_company_name' => Str::limit(strip_tags($item['employer_name'] ?? 'Company not specified'), 250, ''),
            'title' => Str::limit(strip_tags($item['job_title']), 250, ''), 'description' => strip_tags($item['job_description'] ?? ''),
            'requirements' => $this->highlights($item, 'Qualifications'), 'responsibilities' => $this->highlights($item, 'Responsibilities'),
            'location' => $item['job_location'] ?? $item['job_city'] ?? null, 'country' => 'PH',
            'job_type' => ['FULLTIME' => 'full_time', 'PARTTIME' => 'part_time', 'CONTRACTOR' => 'contract', 'INTERN' => 'internship'][$item['job_employment_types'][0] ?? ''] ?? null,
            'work_arrangement' => ($item['job_is_remote'] ?? null) === true ? 'remote' : (in_array($item['work_arrangement'] ?? null, ['remote', 'hybrid', 'on_site'], true) ? $item['work_arrangement'] : null),
            'salary_min' => $this->salary($item['job_min_salary'] ?? null), 'salary_max' => $this->salary($item['job_max_salary'] ?? null),
            'salary_currency' => null, 'salary_period' => in_array($item['job_salary_period'] ?? null, ['HOUR', 'DAY', 'WEEK', 'MONTH', 'YEAR'], true) ? $item['job_salary_period'] : null,
            'salary_text' => $item['job_salary_string'] ?? $item['job_salary'] ?? null, 'apply_url' => $link,
            'publisher' => Str::limit($item['job_publisher'] ?? 'JSearch', 250, ''), 'source_posted_at' => $this->date($item['job_posted_at_datetime_utc'] ?? null),
            'fetched_at' => $fetchedAt, 'is_active' => true, 'experience_level' => null, 'min_experience_years' => null, 'education_requirement' => null,
        ];
        try {
            $job->forceFill($attributes)->save();
        } catch (UniqueConstraintViolationException) {
            // Two different searches can discover the same listing at the same time.
            $job = JobPosting::where('source_key', $attributes['source_key'])->firstOrFail();
            $job->forceFill($attributes)->save();
        }

        return $job;
    }

    private function salary(mixed $value): ?float
    {
        return is_numeric($value) && (float) $value >= 0 && (float) $value <= 9999999999.99 ? (float) $value : null;
    }

    private function date(mixed $value): ?CarbonImmutable
    {
        if (! is_string($value) || trim($value) === '') {
            return null;
        }
        try {
            return CarbonImmutable::parse($value);
        } catch (\Throwable) {
            return null;
        }
    }

    /** @param array<string, mixed> $item */
    private function highlights(array $item, string $kind): ?string
    {
        $values = $item['job_highlights'][$kind] ?? [];

        return is_array($values) && $values !== [] ? implode("\n", array_map(fn ($value) => strip_tags(is_string($value) ? $value : ''), $values)) : null;
    }
}
