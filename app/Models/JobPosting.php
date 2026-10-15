<?php

namespace App\Models;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** @property CarbonImmutable|null $deadline */
class JobPosting extends Model
{
    protected $table = 'job_postings';

    /** @var list<string> */
    protected $fillable = ['company_id', 'title', 'description', 'requirements', 'responsibilities', 'location', 'job_type', 'work_arrangement', 'salary_min', 'salary_max', 'salary_currency', 'experience_level', 'min_experience_years', 'education_requirement', 'is_active', 'deadline'];

    /** @return BelongsTo<Company, $this> */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    /** @return BelongsTo<User, $this> */
    public function employer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'employer_id');
    }

    /** @return HasMany<JobApplication, $this> */
    public function applications(): HasMany
    {
        return $this->hasMany(JobApplication::class, 'job_id');
    }

    /** @return HasMany<SavedJob, $this> */
    public function savedJobs(): HasMany
    {
        return $this->hasMany(SavedJob::class, 'job_id');
    }

    /** @return HasMany<JobMatch, $this> */
    public function matches(): HasMany
    {
        return $this->hasMany(JobMatch::class, 'job_id');
    }

    /** @return BelongsToMany<Skill, $this> */
    public function skills(): BelongsToMany
    {
        return $this->belongsToMany(Skill::class, 'job_skills', 'job_id', 'skill_id')
            ->withPivot(['id', 'importance'])->withTimestamps();
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'salary_min' => 'decimal:2',
            'salary_max' => 'decimal:2',
            'min_experience_years' => 'integer',
            'is_active' => 'boolean',
            'deadline' => 'immutable_date',
            'created_at' => 'immutable_datetime',
            'updated_at' => 'immutable_datetime',
        ];
    }
}
