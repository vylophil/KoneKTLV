<?php

use App\Http\Controllers\MessageController;
use Illuminate\Support\Facades\Route;

Route::resource('messages', MessageController::class)
    ->parameters(['messages' => 'record']);
