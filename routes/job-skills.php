<?php

use App\Http\Controllers\JobSkillController;
use Illuminate\Support\Facades\Route;

Route::resource('job-skills', JobSkillController::class)
    ->parameters(['job-skills' => 'record']);
