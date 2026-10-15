<?php

namespace Tests\Feature;

use App\Models\Profile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_profile_can_be_created_and_retrieved_through_its_user(): void
    {
        $user = User::factory()->create();
        $profile = $user->profile()->create([
            'headline' => 'Backend developer',
            'years_of_experience' => '2',
        ]);

        $retrievedUser = User::with('profile')->findOrFail($user->id);
        $retrievedProfile = Profile::findOrFail($profile->id);

        $this->assertTrue($retrievedUser->profile->is($profile));
        $this->assertTrue($retrievedProfile->user->is($user));
        $this->assertSame(2, $retrievedProfile->years_of_experience);
        $this->assertNull($retrievedProfile->phone);
        $this->assertNotNull($retrievedProfile->created_at);
        $this->assertNotNull($retrievedProfile->updated_at);
    }

    public function test_profile_mass_assignment_cannot_change_its_id_or_owner(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $profile = $user->profile()->create([
            'headline' => 'Original headline',
            'id' => 999,
            'user_id' => $otherUser->id,
        ]);
        $profileId = $profile->id;

        $profile->update([
            'headline' => 'Updated headline',
            'id' => 999,
            'user_id' => $otherUser->id,
        ]);
        $profile->refresh();

        $this->assertNotSame(999, $profileId);
        $this->assertSame($profileId, $profile->id);
        $this->assertSame($user->id, $profile->user_id);
        $this->assertSame('Updated headline', $profile->headline);
        $this->assertNull($otherUser->profile);
    }
}
