<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;

class UserSkill extends Model
{
    protected $table = 'user_skills';

    /** @var list<string> */
    protected $fillable = ['skill_id', 'proficiency_level'];

    protected static function booted(): void
    {
        static::created(function (UserSkill $entry): void {
            static::refreshEndorsementCount($entry->getAttribute('user_id'), $entry->getAttribute('skill_id'));
        });
    }

    public static function refreshEndorsementCount(int|string $userId, int|string $skillId): void
    {
        static::query()->where('user_id', $userId)->where('skill_id', $skillId)->update([
            'endorsement_count' => DB::raw('(select count(*) from endorsements where endorsements.endorsed_user_id = user_skills.user_id and endorsements.skill_id = user_skills.skill_id)'),
        ]);
    }

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
