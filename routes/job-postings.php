<?php

use App\Http\Controllers\JobPostingController;
use Illuminate\Support\Facades\Route;

Route::resource('job-postings', JobPostingController::class)
    ->only(['index', 'show'])
    ->parameters(['job-postings' => 'record']);
