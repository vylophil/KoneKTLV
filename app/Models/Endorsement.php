<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Endorsement extends Model
{
    protected $table = 'endorsements';

    /** @var list<string> */
    protected $fillable = ['endorsed_user_id', 'skill_id'];

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
