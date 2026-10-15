<?php

namespace App\Models;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** @property CarbonImmutable|null $computed_at */
class JobMatch extends Model
{
    protected $table = 'job_matches';

    /** @var list<string> */
    protected $fillable = ['job_id', 'match_score', 'skill_score', 'experience_score', 'education_score', 'matched_terms'];

    /** @return BelongsTo<JobPosting, $this> */
    public function jobPosting(): BelongsTo
    {
        return $this->belongsTo(JobPosting::class, 'job_id');
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'matched_terms' => 'array',
            'match_score' => 'decimal:2',
            'skill_score' => 'decimal:2',
            'experience_score' => 'decimal:2',
            'education_score' => 'decimal:2',
            'computed_at' => 'immutable_datetime',
        ];
    }
}
