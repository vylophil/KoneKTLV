<?php

use App\Http\Controllers\ConnectionController;
use Illuminate\Support\Facades\Route;

Route::resource('connections', ConnectionController::class)
    ->parameters(['connections' => 'record']);
