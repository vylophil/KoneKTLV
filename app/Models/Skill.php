<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\Pivot;

/** @property-read Pivot $pivot */
class Skill extends Model
{
    /** @var list<string> */
    protected $fillable = ['name', 'category'];

    /** @return BelongsToMany<User, $this> */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_skills')
            ->withPivot(['id', 'proficiency_level', 'endorsement_count'])
            ->withTimestamps();
    }

    /** @return BelongsToMany<JobPosting, $this> */
    public function jobPostings(): BelongsToMany
    {
        return $this->belongsToMany(JobPosting::class, 'job_skills', 'skill_id', 'job_id')
            ->withPivot(['id', 'importance'])->withTimestamps();
    }

    /** @return HasMany<Endorsement, $this> */
    public function endorsements(): HasMany
    {
        return $this->hasMany(Endorsement::class, 'skill_id');
    }

    /** @return HasMany<UserSkill, $this> */
    public function userSkillEntries(): HasMany
    {
        return $this->hasMany(UserSkill::class, 'skill_id');
    }
}
