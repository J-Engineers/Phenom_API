<?php

use App\Http\Middleware\ActiveUser;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\User\ProfileController;


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



Route::post('/v1/user/registration', [AuthController::class, 'register'])->name('user.register');

Route::post('/v1/user/registration/verify', [AuthController::class, 'send_registration_verification_email'])->name('user.verify')->middleware(ActiveUser::class);
Route::post('/v1/user/login', [AuthController::class, 'login'])->name('user.login')->middleware(ActiveUser::class);
Route::post('/v1/user/password/forgot', [AuthController::class, 'forgotPassword'])->name('user.password.forgot')->middleware(ActiveUser::class);
Route::post('/v1/user/password/reset', [AuthController::class, 'resetPassword'])->name('user.password.reset')->middleware(ActiveUser::class);



Route::group(['middleware' => ['auth:sanctum']], function () {
    Route::post('/v1/user/logout', [AuthController::class, 'logout'])->name('user.logout');
    Route::group(['middleware' => ['ActiveUser']], function(){

        // Admin - Users Routes
        Route::get('/v1/admin/users', [AdminController::class, 'getUsers'])->name('users');
        Route::get('/v1/admin/user', [AdminController::class, 'getUser'])->name('user');
        Route::delete('/v1/admin/user/delete', [AdminController::class, 'removeUser'])->name('user.remove');
        Route::get('/v1/admin/user/deactivate', [AdminController::class, 'deactivateUser'])->name('user.deactivate');
        Route::get('/v1/admin/user/activate', [AdminController::class, 'activateUser'])->name('user.activate');
        Route::get('/v1/admin/make/admin', [AdminController::class, 'makeAdmin'])->name('user.makeAdmin');
        Route::get('/v1/admin/cancel/admin', [AdminController::class, 'cancelAdmin'])->name('user.cancelAdmin');

       

        // Users Routes
        Route::get('/v1/user', [ProfileController::class, 'details'])->name('user.details');
        Route::put('/v1/user/password', [ProfileController::class, 'changePassword'])->name('user.password');
        Route::put('/v1/user/update', [ProfileController::class, 'updateDetails'])->name('user.update');
        Route::post('/v1/user/photo', [ProfileController::class, 'updatePhoto'])->name('user.photo');
    
    });
});