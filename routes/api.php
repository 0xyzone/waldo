<?php

use App\Http\Controllers\Api\EmployeeApiController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::prefix('v1')->group(function () {
    Route::get('/employees', [EmployeeApiController::class, 'index'])->name('api.v1.employees.index');
    Route::get('/employees/{employeeCode}', [EmployeeApiController::class, 'show'])->name('api.v1.employees.show');
});

// Default unversioned fallback / alias
Route::get('/employees', [EmployeeApiController::class, 'index'])->name('api.employees.index');
Route::get('/employees/{employeeCode}', [EmployeeApiController::class, 'show'])->name('api.employees.show');
