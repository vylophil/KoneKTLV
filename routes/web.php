<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\JobsPageController;
use App\Http\Controllers\MatchesPageController;
use App\Http\Controllers\ProfilePageController;
use App\Http\Controllers\ResumeController;
use App\Models\JobPosting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', HomeController::class)->name('home');
Route::get('/jobs', [JobsPageController::class, 'index'])->name('jobs.index');
Route::get('/jobs/{job}', [JobsPageController::class, 'show'])->whereNumber('job')->name('jobs.show');
Route::get('/jobs/{job}/out', [JobsPageController::class, 'out'])->whereNumber('job')->name('jobs.out');

Route::middleware(['auth', 'active'])->group(function (): void {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');
    Route::post('/jobs/{job}/apply', [JobsPageController::class, 'apply'])->middleware('throttle:20,1')->name('jobs.apply');
    Route::get('/jobs/{job}/apply', fn (JobPosting $job) => redirect('/jobs/'.$job->id.'#apply'))->name('jobs.apply.start');
    Route::post('/jobs/{job}/save', [JobsPageController::class, 'save'])->name('jobs.save');
    Route::delete('/jobs/{job}/save', [JobsPageController::class, 'unsave'])->name('jobs.unsave');
    Route::get('/saved-jobs', [JobsPageController::class, 'saved'])->name('saved-jobs.index');
    Route::get('/applications', [JobsPageController::class, 'applications'])->name('applications.index');
    Route::put('/applications/{application}', [JobsPageController::class, 'updateApplication'])->name('applications.update');
    Route::get('/applications/{application}/resume', [JobsPageController::class, 'applicationResume'])->name('applications.resume');
    Route::get('/matches', [MatchesPageController::class, 'index'])->name('matches.index');
    Route::post('/matches/refresh', [MatchesPageController::class, 'refresh'])->middleware('throttle:5,1')->name('matches.refresh');
    Route::get('/profile', [ProfilePageController::class, 'index'])->name('profile.edit');
    Route::put('/profile', [ProfilePageController::class, 'update'])->name('profile.update');
    Route::post('/profile/career/{kind}', [ProfilePageController::class, 'storeCareer'])->whereIn('kind', ['experience', 'education']);
    Route::put('/profile/career/{kind}/{record}', [ProfilePageController::class, 'updateCareer'])->whereIn('kind', ['experience', 'education']);
    Route::delete('/profile/career/{kind}/{record}', [ProfilePageController::class, 'destroyCareer'])->whereIn('kind', ['experience', 'education']);
    Route::post('/profile/skills', [ProfilePageController::class, 'addSkill']);
    Route::delete('/profile/skills/{record}', [ProfilePageController::class, 'removeSkill']);
    Route::get('/settings', fn (Request $request) => Inertia::render('settings/index', ['email' => $request->user()->email]))->name('settings');
    Route::delete('/account', [AccountController::class, 'destroy'])->name('account.destroy');
    Route::post('/profile/resume', [ResumeController::class, 'store'])->name('profile.resume.store');
    Route::get('/profile/resume', [ResumeController::class, 'download'])->name('profile.resume.download');
});

require __DIR__.'/backend.php';
require __DIR__.'/access-control.php';
