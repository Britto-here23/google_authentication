<?php

use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Route;
use Laravel\Socialite\Facades\Socialite;
use App\Http\Controllers\LoginController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});



Route::get('auth/google/redirect', [LoginController::class, 'redirectToGoogle'])->name('google.redirect');
Route::get('auth/google/callback', [LoginController::class, 'handleGoogleCallback']);
Route::get('user/details/{id}', [LoginController::class, 'userDetails'])->name('user.details');


Route::post('/logout', [LoginController::class, 'logout'])->name('logout');


