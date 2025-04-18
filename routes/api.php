<?php

use Illuminate\Support\Facades\Route;

Route::post('doctor/register',[\App\Http\Controllers\AuthProviderController::class,'register']);
Route::post('doctor/login',[\App\Http\Controllers\AuthProviderController::class,'login']);

Route::post('patient/register',[\App\Http\Controllers\AuthClientController::class,'register']);
Route::post('patient/login',[\App\Http\Controllers\AuthClientController::class,'login']);

Route::middleware(['auth:sanctum', 'resolve.user'])->get('last-login', [\App\Http\Controllers\LoginInfoController::class, 'lastLogin']);
