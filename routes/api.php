<?php

use App\Http\Controllers\Api\AuthController;
use App\Filament\Resources\ProgramResource\Api\ProgramApiService;
use Filament\Facades\Filament;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


ProgramApiService::registerRoutes(Filament::getPanel('admin'));

// Route::post('/login', [AuthController::class, 'login']);
