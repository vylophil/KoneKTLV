<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Profile extends Model
{
    /** @var list<string> */
    protected $fillable = [
        'headline',
        'bio',
        'location',
        'phone',
        'website',
        'avatar_url',
        'resume_url',
        'linkedin_url',
        'github_url',
        'industry',
        'years_of_experience',
    ];

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return ['years_of_experience' => 'integer'];
    }
}
