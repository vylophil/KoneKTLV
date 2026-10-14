<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $name
 * @property string $email
 * @property Carbon|null $email_verified_at
 * @property string $password
 * @property string|null $remember_token
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /** @var list<string> */
    protected $fillable = ['name', 'email', 'password', 'first_name', 'last_name'];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    /** @return HasOne<Profile, $this> */
    public function profile(): HasOne
    {
        return $this->hasOne(Profile::class);
    }

    /** @return BelongsToMany<Skill, $this> */
    public function skills(): BelongsToMany
    {
        return $this->belongsToMany(Skill::class, 'user_skills')
            ->withPivot(['id', 'proficiency_level', 'endorsement_count'])
            ->withTimestamps();
    }

    /** @return HasMany<Experience, $this> */
    public function workExperience(): HasMany
    {
        return $this->hasMany(Experience::class, 'user_id');
    }

    /** @return HasMany<Education, $this> */
    public function education(): HasMany
    {
        return $this->hasMany(Education::class, 'user_id');
    }

    /** @return HasMany<Company, $this> */
    public function companies(): HasMany
    {
        return $this->hasMany(Company::class, 'user_id');
    }

    /** @return HasMany<JobPosting, $this> */
    public function jobPostings(): HasMany
    {
        return $this->hasMany(JobPosting::class, 'employer_id');
    }

    /** @return HasMany<JobApplication, $this> */
    public function jobApplications(): HasMany
    {
        return $this->hasMany(JobApplication::class, 'user_id');
    }

    /** @return HasMany<UserSkill, $this> */
    public function userSkillEntries(): HasMany
    {
        return $this->hasMany(UserSkill::class, 'user_id');
    }

    /** @return HasMany<SavedJob, $this> */
    public function savedJobs(): HasMany
    {
        return $this->hasMany(SavedJob::class, 'user_id');
    }

    /** @return HasMany<JobMatch, $this> */
    public function jobMatches(): HasMany
    {
        return $this->hasMany(JobMatch::class, 'user_id');
    }

    /** @return HasMany<Connection, $this> */
    public function sentConnections(): HasMany
    {
        return $this->hasMany(Connection::class, 'requester_id');
    }

    /** @return HasMany<Connection, $this> */
    public function receivedConnections(): HasMany
    {
        return $this->hasMany(Connection::class, 'receiver_id');
    }

    /** @return HasMany<Message, $this> */
    public function sentMessages(): HasMany
    {
        return $this->hasMany(Message::class, 'sender_id');
    }

    /** @return HasMany<Message, $this> */
    public function receivedMessages(): HasMany
    {
        return $this->hasMany(Message::class, 'receiver_id');
    }

    /** @return HasMany<Endorsement, $this> */
    public function endorsementsGiven(): HasMany
    {
        return $this->hasMany(Endorsement::class, 'endorser_id');
    }

    /** @return HasMany<Endorsement, $this> */
    public function endorsementsReceived(): HasMany
    {
        return $this->hasMany(Endorsement::class, 'endorsed_user_id');
    }

    /** @return HasMany<ProfileView, $this> */
    public function profileViewsMade(): HasMany
    {
        return $this->hasMany(ProfileView::class, 'viewer_id');
    }

    /** @return HasMany<ProfileView, $this> */
    public function profileViewsReceived(): HasMany
    {
        return $this->hasMany(ProfileView::class, 'viewed_id');
    }
}
