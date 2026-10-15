<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AccountActionsTest extends TestCase
{
    use RefreshDatabase;

    private const ACCOUNT_PATH = '/account';

    public function test_a_user_can_sign_out_from_the_dashboard_session(): void
    {
        /** @var User $user */
        $user = User::factory()->create();

        $this->actingAs($user)->post('/logout')->assertRedirect('/');
        $this->assertGuest();
    }

    public function test_account_deletion_requires_the_exact_username_and_removes_private_resume(): void
    {
        Storage::fake('local');
        /** @var User $user */
        $user = User::factory()->create(['email' => 'jordan@example.test']);
        $profile = $user->profile()->create([
            'headline' => 'Developer',
            'resume_path' => 'resumes/1/resume.pdf',
            'resume_original_name' => 'resume.pdf',
        ]);
        Storage::disk('local')->put($profile->resume_path, 'private resume');

        $this->actingAs($user)->from('/dashboard')->delete(self::ACCOUNT_PATH, [
            'username' => 'Jordan Reyes',
        ])->assertSessionHasErrors('username');
        $this->assertDatabaseHas('users', ['id' => $user->id]);
        $this->assertTrue(Storage::disk('local')->exists($profile->resume_path));

        $this->delete(self::ACCOUNT_PATH, ['username' => 'jordan@example.test'])->assertRedirect('/');

        $this->assertGuest();
        $this->assertDatabaseMissing('users', ['id' => $user->id]);
        $this->assertDatabaseMissing('profiles', ['id' => $profile->id]);
        $this->assertFalse(Storage::disk('local')->exists($profile->resume_path));
    }

    public function test_guests_cannot_delete_accounts(): void
    {
        $this->delete(self::ACCOUNT_PATH, ['username' => 'guest@example.test'])->assertRedirect('/login');
    }
}
