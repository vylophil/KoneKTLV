<?php

use App\Http\Controllers\ProfileViewController;
use Illuminate\Support\Facades\Route;

Route::resource('profile-views', ProfileViewController::class)
    ->parameters(['profile-views' => 'record']);
