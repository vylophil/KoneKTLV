<?php

use App\Http\Controllers\SavedJobController;
use Illuminate\Support\Facades\Route;

Route::resource('saved-jobs', SavedJobController::class)
    ->parameters(['saved-jobs' => 'record']);
