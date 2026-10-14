<?php

use App\Http\Controllers\JobMatchController;
use Illuminate\Support\Facades\Route;

Route::resource('job-matches', JobMatchController::class)
    ->parameters(['job-matches' => 'record']);
