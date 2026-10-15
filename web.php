<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ResumeController;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');

Route::middleware('auth')->group(function (): void {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');
    Route::delete('/account', [AccountController::class, 'destroy'])->name('account.destroy');
    Route::post('/profile/resume', [ResumeController::class, 'store'])->name('profile.resume.store');
    Route::get('/profile/resume', [ResumeController::class, 'download'])->name('profile.resume.download');

    Route::get('/networking-chat', function () {
        $users = User::where('id', '!=', Auth::id())->get();

        return view('chat.index', compact('users'));
    })->name('networking.chat');
});

require __DIR__.'/backend.php';
