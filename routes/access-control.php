<?php

use App\Http\Controllers\Admin\UserAccessController;
use App\Http\Controllers\Staff\SkillCatalogController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'active', 'role:Admin', 'permission:manage user roles'])
    ->prefix('admin')->name('admin.')->group(function (): void {
        Route::get('/users', [UserAccessController::class, 'index'])->name('users.index');
        Route::put('/users/{user}/role', [UserAccessController::class, 'update'])->name('users.role');
    });

Route::middleware(['auth', 'active', 'role:Admin|Manager', 'permission:manage skills'])
    ->prefix('staff')->name('staff.')->group(function (): void {
        Route::get('/skills', [SkillCatalogController::class, 'index'])->name('skills.index');
        Route::post('/skills', [SkillCatalogController::class, 'store'])->name('skills.store');
        Route::put('/skills/{record}', [SkillCatalogController::class, 'update'])->name('skills.update');
        Route::delete('/skills/{record}', [SkillCatalogController::class, 'destroy'])->name('skills.destroy');
    });
