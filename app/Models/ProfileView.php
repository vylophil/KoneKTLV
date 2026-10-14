<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProfileView extends Model
{
    protected $table = 'profile_views';

    /** @var list<string> */
    protected $fillable = ['viewed_id'];

    /** @return BelongsTo<User, $this> */
    public function viewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'viewer_id');
    }

    /** @return BelongsTo<User, $this> */
    public function viewedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'viewed_id');
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'viewed_at' => 'immutable_datetime',
        ];
    }
}
