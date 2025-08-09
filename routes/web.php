<?php

use App\Filament\Pages\Login as FilamentLogin;
use Illuminate\Support\Facades\Route;

Route::get('/filament/login', FilamentLogin::class)
    ->name('filament.admin.auth.login');

Route::get('/login', function () {
    return redirect()->route('filament.admin.auth.login');
})->name('login');

// Route::get('/', function () {
//     return view('welcome');
// });
