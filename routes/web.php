<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ResumeController;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');

Route::middleware('auth')->group(function (): void {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');
    Route::delete('/account', [AccountController::class, 'destroy'])->name('account.destroy');
    Route::post('/profile/resume', [ResumeController::class, 'store'])->name('profile.resume.store');
    Route::get('/profile/resume', [ResumeController::class, 'download'])->name('profile.resume.download');
});

require __DIR__.'/backend.php';
