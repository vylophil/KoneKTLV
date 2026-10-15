<?php

namespace App\Services;

use App\Models\JobPosting;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ResumeJobMatcher
{
    /** @var list<string> */
    private const STOP_WORDS = [
        'about', 'after', 'also', 'and', 'are', 'for', 'from', 'have', 'into',
        'its', 'our', 'that', 'the', 'their', 'this', 'through', 'with', 'you',
        'your', 'will', 'work', 'years',
    ];

    public function refresh(User $user, string $resumeText): int
    {
        $resumeTerms = $this->terms($resumeText);
        $jobs = JobPosting::query()
            ->with('skills')
            ->where('is_active', true)
            ->where(fn ($query) => $query->whereNull('deadline')->orWhereDate('deadline', '>=', today()))
            ->get();
        $matches = [];

        foreach ($jobs as $job) {
            $weights = [];
            $this->addTerms($weights, $job->title, 4);
            $this->addTerms($weights, $job->skills->pluck('name')->implode(' '), 3);
            $this->addTerms($weights, $job->requirements ?? '', 2);
            $this->addTerms($weights, $job->responsibilities ?? '', 2);
            $this->addTerms($weights, $job->description, 1);

            $totalWeight = array_sum($weights);
            if ($totalWeight === 0) {
                continue;
            }

            $matchedWeight = 0;
            foreach ($weights as $term => $weight) {
                if (isset($resumeTerms[$term])) {
                    $matchedWeight += $weight;
                }
            }

            if ($matchedWeight === 0) {
                continue;
            }

            $score = round(($matchedWeight / $totalWeight) * 100, 2);
            $matches[] = [
                'job_id' => $job->id,
                'match_score' => $score,
                'skill_score' => $score,
                'experience_score' => 0,
                'education_score' => 0,
            ];
        }

        DB::transaction(function () use ($user, $matches): void {
            $user->jobMatches()->delete();

            foreach ($matches as $match) {
                $user->jobMatches()->create($match);
            }
        });

        return count($matches);
    }

    /** @param array<string, int> $weights */
    private function addTerms(array &$weights, string $text, int $weight): void
    {
        foreach ($this->terms($text) as $term => $_) {
            $weights[$term] = ($weights[$term] ?? 0) + $weight;
        }
    }

    /** @return array<string, true> */
    private function terms(string $text): array
    {
        $normalized = Str::lower(Str::ascii($text));
        preg_match_all('/[a-z0-9][a-z0-9+#.-]+/', $normalized, $matches);
        $terms = array_diff($matches[0], self::STOP_WORDS);

        return array_fill_keys($terms, true);
    }
}
