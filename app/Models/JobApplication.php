<?php

namespace App\Models;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** @property CarbonImmutable|null $applied_at */
class JobApplication extends Model
{
    /** @var array<string, mixed> */
    protected $attributes = ['status' => 'applied', 'self_reported' => true];

    /** @var list<string> */
    protected $hidden = ['resume_path'];

    protected $table = 'job_applications';

    /** @var list<string> */
    protected $fillable = ['job_id', 'cover_letter', 'resume_url', 'status', 'self_reported', 'opened_at', 'notes'];

    protected static function booted(): void
    {
        static::updating(function (JobApplication $application): void {
            if ($application->isDirty(['status', 'notes'])) {
                $application->self_reported = true;
            }
        });
    }

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
            'applied_at' => 'immutable_datetime',
            'opened_at' => 'immutable_datetime',
            'self_reported' => 'boolean',
            'updated_at' => 'immutable_datetime',
        ];
    }
}
