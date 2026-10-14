<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Endorsement extends Model
{
    protected $table = 'endorsements';

    /** @var list<string> */
    protected $fillable = ['endorsed_user_id', 'skill_id'];

    protected static function booted(): void
    {
        static::saved(fn (Endorsement $endorsement) => $endorsement->refreshSkillCounts());
        static::deleted(fn (Endorsement $endorsement) => $endorsement->refreshSkillCounts());
    }

    private function refreshSkillCounts(): void
    {
        foreach ([
            [$this->getAttribute('endorsed_user_id'), $this->getAttribute('skill_id')],
            [$this->getOriginal('endorsed_user_id'), $this->getOriginal('skill_id')],
        ] as [$userId, $skillId]) {
            if ((is_int($userId) || is_string($userId)) && (is_int($skillId) || is_string($skillId))) {
                UserSkill::refreshEndorsementCount($userId, $skillId);
            }
        }
    }

    /** @return BelongsTo<User, $this> */
    public function endorser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'endorser_id');
    }

    /** @return BelongsTo<User, $this> */
    public function endorsedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'endorsed_user_id');
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
            'created_at' => 'immutable_datetime',
        ];
    }
}
