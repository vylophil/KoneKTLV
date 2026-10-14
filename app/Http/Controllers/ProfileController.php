<?php

namespace App\Http\Controllers;

use App\Models\Profile;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

/** @extends ResourceController<Profile> */
class ProfileController extends ResourceController
{
    /** @var class-string<Profile> */
    protected string $model = Profile::class;

    protected ?string $actorColumn = 'user_id';

    /** @return array<string, list<mixed>> */
    protected function rules(Request $request, ?Model $record = null): array
    {
        return [
            'headline' => ['nullable', 'string', 'max:255'],
            'bio' => ['nullable', 'string', 'max:10000'],
            'location' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20'],
            'website' => ['nullable', 'string', 'max:500', 'url:http,https'],
            'avatar_url' => ['nullable', 'string', 'max:500', 'url:http,https'],
            'resume_url' => ['nullable', 'string', 'max:500', 'url:http,https'],
            'linkedin_url' => ['nullable', 'string', 'max:500', 'url:http,https'],
            'github_url' => ['nullable', 'string', 'max:500', 'url:http,https'],
            'industry' => ['nullable', 'string', 'max:255'],
            'years_of_experience' => ['sometimes', 'integer', 'min:0', 'max:80'],
        ];
    }

    protected function authorizeAction(Request $request, string $action, ?Model $record = null): void
    {
        $this->user($request);
        if ($action === 'store') {
            abort_if($this->query($request)->exists(), 409, 'A profile already exists.');
        }
    }
}
