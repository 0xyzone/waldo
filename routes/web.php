<?php

use App\Http\Controllers\FontController;
use App\Http\Controllers\LetterController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/letters', [LetterController::class, 'index'])->name('letters.index');
Route::get('/letters/create', [LetterController::class, 'create'])->name('letters.create');
Route::get('/letters/generate', [LetterController::class, 'generate'])->name('letters.generate');

// Font management (must be before /{id} routes)
Route::get('/letters/fonts', [FontController::class, 'index'])->name('letters.fonts');
Route::post('/letters/fonts', [FontController::class, 'store'])->name('letters.fonts.store');
Route::get('/letters/fonts/api', [FontController::class, 'apiList'])->name('letters.fonts.api');
Route::delete('/letters/fonts/{font}', [FontController::class, 'destroy'])->name('letters.fonts.destroy');

Route::post('/letters', [LetterController::class, 'store'])->name('letters.store');
Route::get('/letters/{id}/edit', [LetterController::class, 'edit'])->name('letters.edit');
Route::put('/letters/{id}', [LetterController::class, 'update'])->name('letters.update');
Route::delete('/letters/{id}', [LetterController::class, 'destroy'])->name('letters.destroy');
