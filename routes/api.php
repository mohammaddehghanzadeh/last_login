<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('doctor/register',[\App\Http\Controllers\ProviderAuthController::class,'register']);
Route::post('doctor/login',[\App\Http\Controllers\ProviderAuthController::class,'login']);

Route::post('patient/register',[\App\Http\Controllers\ClientAuthController::class,'register']);
Route::post('patient/login',[\App\Http\Controllers\ClientAuthController::class,'login']);


Route::middleware('auth:sanctum')->get('/last-login', [\App\Http\Controllers\LoginInfoController::class, 'lastLogin']);
