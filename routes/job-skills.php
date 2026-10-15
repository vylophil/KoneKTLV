<?php

use App\Http\Controllers\JobSkillController;
use Illuminate\Support\Facades\Route;

Route::resource('job-skills', JobSkillController::class)
    ->only(['index', 'show'])
    ->parameters(['job-skills' => 'record']);
