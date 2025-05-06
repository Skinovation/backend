<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\Analyze\AnalyzeSkincareController;
use App\Http\Controllers\Analyze\AnalyzeHistoryController;
use App\Http\Controllers\SosialMediaController;
use App\Http\Controllers\KomenController;

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
    Route::get('/history/{id}', [AnalyzeHistoryController::class, 'show'])->name('analyze.history.show');
});

Route::group(['prefix' => 'sosial-media'], function () {
    Route::get('/', [SosialMediaController::class, 'index'])->name('sosial-media.index');
    Route::post('/', [SosialMediaController::class, 'store'])->name('sosial-media.store');
    Route::get('/{id}', [SosialMediaController::class, 'edit'])->name('sosial-media.edit');
    Route::post('/{id}', [SosialMediaController::class, 'update'])->name('sosial-media.update');
    Route::delete('/{id}', [SosialMediaController::class, 'destroy'])->name('sosial-media.destroy');

    Route::group(['prefix' => '{id}/komentar'], function () {
        Route::get('/', [KomenController::class, 'index'])->name('sosial-media.komentar.index');
        Route::post('/', [KomenController::class, 'store'])->name('sosial-media.komentar.store');
        Route::get('/{komentarId}', [KomenController::class, 'edit'])->name('sosial-media.komentar.edit');
        Route::post('/{komentarId}', [KomenController::class, 'update'])->name('sosial-media.komentar.update');
        Route::delete('/{komentarId}', [KomenController::class, 'destroy'])->name('sosial-media.komentar.destroy');
    });
});