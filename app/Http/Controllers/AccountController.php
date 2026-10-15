<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class AccountController extends Controller
{
    public function destroy(Request $request): RedirectResponse
    {
        $user = $request->user();
        abort_unless($user instanceof User, 403);

        $request->validate([
            'username' => ['required', 'string', Rule::in([$user->email])],
        ]);

        $resumePath = $user->profile?->resume_path;
        Auth::logout();
        DB::transaction(fn () => $user->delete());

        if (is_string($resumePath)) {
            Storage::disk('local')->delete($resumePath);
        }
        Storage::disk('local')->deleteDirectory('applications/'.$user->id);

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return to_route('home');
    }
}
