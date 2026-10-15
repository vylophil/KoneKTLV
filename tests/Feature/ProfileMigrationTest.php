<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ProfileMigrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_user_cannot_have_multiple_profiles(): void
    {
        $user = User::factory()->create();
        DB::table('profiles')->insert(['user_id' => $user->id]);

        $this->expectException(UniqueConstraintViolationException::class);

        DB::table('profiles')->insert(['user_id' => $user->id]);
    }

    public function test_a_profile_cannot_reference_a_missing_user(): void
    {
        $this->expectException(QueryException::class);

        DB::table('profiles')->insert(['user_id' => 999]);
    }

    public function test_deleting_a_user_removes_only_their_profile(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        DB::table('profiles')->insert([
            ['user_id' => $user->id],
            ['user_id' => $otherUser->id],
        ]);

        DB::table('users')->where('id', $user->id)->delete();

        $this->assertDatabaseMissing('profiles', ['user_id' => $user->id]);
        $this->assertDatabaseHas('profiles', ['user_id' => $otherUser->id]);
        $this->assertDatabaseCount('profiles', 1);
    }
}
