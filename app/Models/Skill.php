<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

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
}
