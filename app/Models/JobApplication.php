<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JobApplication extends Model
{
    protected $table = 'job_applications';

    /** @var list<string> */
    protected $fillable = ['job_id', 'cover_letter', 'resume_url', 'status'];

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
            'updated_at' => 'immutable_datetime',
        ];
    }
}
