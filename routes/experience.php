<?php

use App\Http\Controllers\ExperienceController;
use Illuminate\Support\Facades\Route;

Route::resource('experience', ExperienceController::class)
    ->parameters(['experience' => 'record']);
