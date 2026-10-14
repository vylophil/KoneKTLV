<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserSkill extends Model
{
    protected $table = 'user_skills';

    /** @var list<string> */
    protected $fillable = ['skill_id', 'proficiency_level'];

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /** @return BelongsTo<Skill, $this> */
    public function skill(): BelongsTo
    {
        return $this->belongsTo(Skill::class, 'skill_id');
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'endorsement_count' => 'integer',
            'created_at' => 'immutable_datetime',
        ];
    }
}
