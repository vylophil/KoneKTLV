<?php

use App\Http\Controllers\JobPostingController;
use Illuminate\Support\Facades\Route;

Route::resource('job-postings', JobPostingController::class)
    ->parameters(['job-postings' => 'record']);
