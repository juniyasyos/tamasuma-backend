<?php

use App\Http\Controllers\Api\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\LearningAreaController;
use App\Http\Controllers\Api\ModulesController;
use App\Http\Controllers\Api\PartnersController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


// Route::post('/login', [AuthController::class, 'login']);

Route::prefix('v1')->group(function () {
    Route::get('learning-areas', [LearningAreaController::class, 'index']);
    Route::get('learning-areas/{learningArea}', [LearningAreaController::class, 'show']);
    Route::get('learning-areas/{learningArea}/programs', [LearningAreaController::class, 'programs']);

    // Frontend modules feed
    Route::get('modules', [ModulesController::class, 'index']);
    Route::get('modules/featured', [ModulesController::class, 'featured']);

    // Partners
    Route::get('partners', [PartnersController::class, 'index']);
});
