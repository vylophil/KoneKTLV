<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::resource('profiles', ProfileController::class)
    ->parameters(['profiles' => 'record']);
