<?php

use App\Http\Controllers\EndorsementController;
use Illuminate\Support\Facades\Route;

Route::resource('endorsements', EndorsementController::class)
    ->parameters(['endorsements' => 'record']);
