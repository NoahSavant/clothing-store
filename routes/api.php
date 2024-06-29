<?php

use App\Constants\UserConstants\UserRole;
use App\Http\Controllers\AddressController;
use App\Http\Controllers\AuthenController;
use App\Http\Controllers\BlockController;
use App\Http\Controllers\CollectionController;
use App\Http\Controllers\DiscountController;
use App\Http\Controllers\FileController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\TagController;
use App\Http\Controllers\VariableController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\VariantController;
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
Route::get('/csrf-token', function () {
    return response()->json(['csrf_token' => csrf_token()]);
});
Route::post('/login', [AuthenController::class, 'login'])->name('login');
Route::post('/sign-up', [AuthenController::class, 'signUp'])->name('auth.signup');
Route::get('/unauthenticated', [AuthenController::class, 'throwAuthenError'])->name('auth.authenError');
Route::get('/unauthorized', [AuthenController::class, 'throwAuthorError'])->name('auth.authorError');
Route::post('/send-verify', [AuthenController::class, 'sendVerify'])->name('sendVerify');
Route::post('/active-account', [AuthenController::class, 'activeAccount'])->name('activeAccount');
Route::post('/reset-password', [AuthenController::class, 'resetPassword'])->name('resetPassword');
Route::post('/refresh', [AuthenController::class, 'refresh'])->name('refresh');
Route::controller(FileController::class)->prefix('files')->group(function () {
    Route::get('/', 'index')->name('getAllPages');
    Route::post('/', 'upload')->name('uploadFiles');
    Route::put('/', 'update')->name('updateBlocks');
    Route::delete('/', 'delete')->name('deleteBlocks');
});

Route::controller(CategoryController::class)->prefix('categories')->group(function () {
    Route::get('/', 'index')->name('getAllCategories');
});

Route::controller(CollectionController::class)->prefix('collections')->group(function () {
    Route::get('/', 'index')->name('getAllCategories');
});

Route::controller(ProductController::class)->prefix('products')->group(function () {
    Route::get('/', 'index')->name('getAllProducts');
    Route::get('/{id}', 'get')->name('getSingleProduct');
});

Route::controller(VariantController::class)->prefix('variants')->group(function () {
    Route::get('/{id}', 'index')->name('getAllVariants');
});

Route::controller(PageController::class)->prefix('pages')->group(function () {
    Route::get('/{slug}', 'detail')->name('getPageDetail');
});

Route::controller(TagController::class)->prefix('tags')->group(function () {
    Route::get('/', 'index')->name('getTags');
});

Route::controller(DiscountController::class)->prefix('discounts')->group(function () {
    Route::get('/', 'index')->name('getDiscounts');
});
// authen 
Route::controller(TagController::class)->prefix('tags')->group(function () {
    Route::post('/', 'create')->name('createTags');
    Route::delete('/', 'delete')->name('deleteTags');
    Route::put('/{id}', 'update')->name('updateTags');
});

Route::controller(CategoryController::class)->prefix('categories')->group(function () {
    Route::post('/', 'create')->name('createCategories');
    Route::put('/{id}', 'update')->name('updateCategories');
    Route::delete('/', 'delete')->name('deleteCategories');
});

Route::controller(CollectionController::class)->prefix('collections')->group(function () {
    Route::post('/', 'create')->name('createCollection');
    Route::put('/{id}', 'update')->name('updateCollection');
    Route::delete('/', 'delete')->name('deleteCollection');
    Route::get('/{id}', 'get')->name('getCollection');
    Route::post('/{id}/update-product', 'updateProducts')->name('updateProduct');

});

Route::controller(DiscountController::class)->prefix('discounts')->group(function () {
    Route::post('/', 'create')->name('createDiscounts');
    Route::put('/{id}', 'update')->name('updateDiscounts');
    Route::delete('/', 'delete')->name('deleteDiscounts');
});

Route::controller(ProductController::class)->prefix('products')->group(function () {
    Route::post('/', 'create')->name('createProducts');
    Route::put('/{id}', 'update')->name('updateProducts');
    Route::delete('/', 'delete')->name('deleteProducts');
});

Route::controller(VariantController::class)->prefix('variants')->group(function () {
    Route::put('/{id}', 'update')->name('updateVariant');
    Route::post('/{id}', 'create')->name('createVariant');
    Route::delete('', 'delete')->name('deleteVariants');
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
});
