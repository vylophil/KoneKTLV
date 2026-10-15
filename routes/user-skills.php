<?php

use App\Http\Controllers\UserSkillController;
use Illuminate\Support\Facades\Route;

Route::resource('user-skills', UserSkillController::class)
    ->parameters(['user-skills' => 'record']);
