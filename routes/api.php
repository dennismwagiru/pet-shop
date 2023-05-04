<?php

use App\Http\Controllers\Api\AdminController;
use Illuminate\Http\Request;
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

Route::prefix('v1')->name('v1.')->middleware('accept-json')->group(function () {
    Route::prefix('admin')->name('admin.')->group(function () {
        Route::post('login', [AdminController::class, 'login'])->name('login');
        Route::get('logout', [AdminController::class, 'logout'])->name('logout');
    });
});

Route::middleware('auth.jwt')->get('/user', function (Request $request) {
    return $request->user();
});
