<?php

use App\Http\Controllers\EducationController;
use Illuminate\Support\Facades\Route;

Route::resource('education', EducationController::class)
    ->parameters(['education' => 'record']);
