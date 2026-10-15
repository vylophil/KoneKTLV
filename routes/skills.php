<?php

use App\Http\Controllers\SkillController;
use Illuminate\Support\Facades\Route;

Route::resource('skills', SkillController::class)
    ->parameters(['skills' => 'record']);
