<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class ActivityFiveDemoSeeder extends Seeder
{
    public function run(): void
    {
        if (! app()->environment(['local', 'testing'])) {
            return;
        }

        foreach (['admin@example.com' => 'Admin', 'manager@example.com' => 'Manager'] as $email => $role) {
            $user = User::query()->firstOrCreate(
                ['email' => $email],
                ['name' => 'Demo '.$role, 'password' => 'password'],
            );

            // Never promote an existing account or overwrite an administrator's changes.
            if ($user->wasRecentlyCreated) {
                $user->assignRole($role);
            }
        }
    }
}
