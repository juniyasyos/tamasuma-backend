<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PortfolioController;

// Route::get('/', function () {
//     return view('welcome');
// });

// Public portfolio page: /u/{user}/portfolio (user = id)
Route::get('/u/{user}/portfolio', [PortfolioController::class, 'show'])->name('portfolio.show');
