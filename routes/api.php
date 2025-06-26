<?php

use App\Http\Controllers\ImageController;
use App\Http\Controllers\LoginController;
use Illuminate\Support\Facades\Route;

Route::post("/register", [LoginController::class, "register"])
    ->name("register");
Route::post("/login", [LoginController::class, "login"])
    ->name("login");

Route::middleware("auth:sanctum")->group(function () {
    Route::post("/logout", [LoginController::class, "logout"])
        ->name("logout");
    Route::resource("/images", ImageController::class);
});
