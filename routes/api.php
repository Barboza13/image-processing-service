<?php

use App\Http\Controllers\LoginController;
use Illuminate\Support\Facades\Route;

Route::get('/register', [LoginController::class, 'register'])
    ->name('register');

Route::middleware('auth:sanctum')->group(function () {
    //
});
