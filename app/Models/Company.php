<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Company extends Model
{
    protected $table = 'companies';

    /** @var list<string> */
    protected $fillable = ['name', 'description', 'industry', 'website', 'logo_url', 'location', 'company_size', 'founded_year'];

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /** @return HasMany<JobPosting, $this> */
    public function jobPostings(): HasMany
    {
        return $this->hasMany(JobPosting::class, 'company_id');
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'founded_year' => 'integer',
            'created_at' => 'immutable_datetime',
            'updated_at' => 'immutable_datetime',
        ];
    }
}
