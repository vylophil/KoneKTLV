<?php

use App\Http\Controllers\CompanyController;
use Illuminate\Support\Facades\Route;

Route::resource('companies', CompanyController::class)
    ->only(['index', 'show'])
    ->parameters(['companies' => 'record']);
