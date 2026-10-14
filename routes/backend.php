<?php

use App\Http\Middleware\AuthenticateBackend;
use Illuminate\Support\Facades\Route;

Route::middleware(AuthenticateBackend::class)->prefix('backend')->name('backend.')->group(function (): void {
    require __DIR__.'/skills.php';
    require __DIR__.'/profiles.php';
    require __DIR__.'/user-skills.php';
    require __DIR__.'/experience.php';
    require __DIR__.'/education.php';
    require __DIR__.'/companies.php';
    require __DIR__.'/job-postings.php';
    require __DIR__.'/job-skills.php';
    require __DIR__.'/job-applications.php';
    require __DIR__.'/saved-jobs.php';
    require __DIR__.'/job-matches.php';
    require __DIR__.'/connections.php';
    require __DIR__.'/messages.php';
    require __DIR__.'/endorsements.php';
    require __DIR__.'/profile-views.php';
});
