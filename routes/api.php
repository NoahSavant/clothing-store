<?php

use App\Constants\UserConstants\UserRole;
use App\Http\Controllers\AddressController;
use App\Http\Controllers\AuthenController;
use App\Http\Controllers\BlockController;
use App\Http\Controllers\FileController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\VariableController;
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

Route::post('/login', [AuthenController::class, 'login'])->name('login');
Route::post('/sign-up', [AuthenController::class, 'signUp'])->name('auth.signup');
Route::get('/unauthenticated', [AuthenController::class, 'throwAuthenError'])->name('auth.authenError');
Route::get('/unauthorized', [AuthenController::class, 'throwAuthorError'])->name('auth.authorError');
Route::post('/send-verify', [AuthenController::class, 'sendVerify'])->name('sendVerify');
Route::post('/active-account', [AuthenController::class, 'activeAccount'])->name('activeAccount');
Route::post('/reset-password', [AuthenController::class, 'resetPassword'])->name('resetPassword');
Route::post('/refresh', [AuthenController::class, 'refresh'])->name('refresh');

Route::controller(PageController::class)->prefix('pages')->group(function () {
    Route::get('/{slug}', 'detail')->name('getPageDetail');
});

Route::middleware('auth:api')->group(function () {
    Route::middleware('author:' . UserRole::ADMIN)->group(function () {
        Route::controller(BlockController::class)->prefix('blocks')->group(function () {
            Route::get('/','index')->name('getAllBlock');

        });
    });

    Route::controller(AddressController::class)->prefix('addresses')->group(function () {
        Route::get('/', 'index')->name('getAllAddresses');
        Route::post('/', 'create')->name('createAddresses');
        Route::put('/', 'update')->name('updateAddresses');
        Route::delete('/', 'delete')->name('deleteAddresses');
    });

    Route::controller(BlockController::class)->prefix('blocks')->group(function () {
        Route::get('/', 'index')->name('getAllBlocks');
        Route::get('/{blockId}', 'detail')->name('getBlockData');
        Route::post('/create-parent', 'createParent')->name('createParentBlocks');
        Route::post('/', 'create')->name('createBlocks');
        Route::put('/', 'update')->name('updateBlocks');
        Route::delete('/', 'delete')->name('deleteBlocks');
    });

    Route::controller(VariableController::class)->prefix('variables')->group(function () {
        Route::get('/', 'index')->name('getAllBlocks');
        Route::post('/', 'create')->name('createVariables');
        Route::put('/', 'update')->name('updateBlocks');
        Route::delete('/', 'delete')->name('deleteBlocks');
    });

    Route::controller(PageController::class)->prefix('pages')->group(function () {
        Route::get('/', 'index')->name('getAllPages');
        Route::post('/', 'create')->name('createPages');
        Route::put('/', 'update')->name('updateBlocks');
        Route::delete('/', 'delete')->name('deleteBlocks');
    });

    Route::controller(FileController::class)->prefix('files')->group(function () {
        Route::get('/', 'index')->name('getAllPages');
        Route::post('/', 'upload')->name('uploadFiles');
        Route::put('/', 'update')->name('updateBlocks');
        Route::delete('/', 'delete')->name('deleteBlocks');
    });
});
