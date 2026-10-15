<?php

use App\Http\Controllers\JobApplicationController;
use Illuminate\Support\Facades\Route;

Route::resource('job-applications', JobApplicationController::class)
    ->parameters(['job-applications' => 'record']);
