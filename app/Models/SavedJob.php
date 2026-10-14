<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SavedJob extends Model
{
    protected $table = 'saved_jobs';

    /** @var list<string> */
    protected $fillable = ['job_id'];

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
            'saved_at' => 'immutable_datetime',
        ];
    }
}
