<?php

use App\Http\Controllers\FontController;
use App\Http\Controllers\LetterController;
use App\Http\Controllers\ReportController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});
Route::prefix('/letters')->middleware('role:super_admin|HR')->group(function () {
    Route::get('/', [LetterController::class, 'index'])->name('letters.index');
    Route::get('/create', [LetterController::class, 'create'])->name('letters.create');
    Route::get('/generate', [LetterController::class, 'generate'])->name('letters.generate');

    // Generated Letters History & Saving
    Route::get('/history', [LetterController::class, 'history'])->name('letters.history');
    Route::post('/save-generated', [LetterController::class, 'saveGenerated'])->name('letters.save-generated');
    Route::get('/history/{id}', [LetterController::class, 'showGenerated'])->name('letters.history.show');
    Route::get('/history/{id}/edit', [LetterController::class, 'editGenerated'])->name('letters.history.edit');
    Route::put('/history/{id}', [LetterController::class, 'updateGenerated'])->name('letters.history.update');
    Route::delete('/history/{id}', [LetterController::class, 'destroyGenerated'])->name('letters.history.destroy');

    // Font management (must be before /{id} routes)
    Route::get('/fonts', [FontController::class, 'index'])->name('letters.fonts');
    Route::post('/fonts', [FontController::class, 'store'])->name('letters.fonts.store');
    Route::get('/fonts/api', [FontController::class, 'apiList'])->name('letters.fonts.api');
    Route::delete('/fonts/{font}', [FontController::class, 'destroy'])->name('letters.fonts.destroy');

    Route::post('/', [LetterController::class, 'store'])->name('letters.store');
    Route::get('/{id}/edit', [LetterController::class, 'edit'])->name('letters.edit');
    Route::put('/{id}', [LetterController::class, 'update'])->name('letters.update');
    Route::delete('/{id}', [LetterController::class, 'destroy'])->name('letters.destroy');
});

// Reports
Route::get('/reports/birthdays', [ReportController::class, 'birthdays'])->name('reports.birthdays');
Route::get('/reports/departments', [ReportController::class, 'departments'])->name('reports.departments');
Route::get('/reports/departments/view', [ReportController::class, 'viewDepartments'])->name('reports.departments.view');
