<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\Analyze\AnalyzeSkincareController;
use App\Http\Controllers\Analyze\AnalyzeHistoryController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::group(['prefix' => 'auth'], function () {
    Route::post('/register', RegisterController::class)->name('register');
    Route::post('/login', LoginController::class)->name('login');
    Route::post('/logout', LogoutController::class)->name('logout');
});

Route::group(['prefix' => 'analyze'], function () {
    Route::post('/skincare', [AnalyzeSkincareController::class, 'Analyze'])->name('analyze.skincare');
    Route::get('/history', [AnalyzeHistoryController::class, 'index'])->name('analyze.history');
});