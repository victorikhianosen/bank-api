<?php

use App\Http\Controllers\User\AccountCategoryController;
use App\Http\Controllers\User\AccountOfficerController;
use App\Http\Controllers\User\AccountProductController;
use App\Http\Controllers\User\AccountTypeController;
use App\Http\Controllers\User\AuthController;
use App\Http\Controllers\User\BranchController;
use App\Http\Controllers\User\CurrencyController;
use App\Http\Controllers\User\CustomerController;
use App\Http\Controllers\User\GeneralLedgerController;
use App\Http\Controllers\User\PermissionController;
use App\Http\Controllers\User\RoleController;
use App\Http\Controllers\User\SettingsController;
use App\Http\Controllers\User\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Route::get('/check', function (Request $request) {
//     return $request->user();
// })->middleware('user');



// Route::prefix('auth')->group(function () {
Route::post('login', [AuthController::class, 'login']);
Route::post('verify/2fa-code', [UserController::class, 'verify2faOtp']);
// });

Route::middleware('user:user')->group(function () {
    // USERS
    Route::get('list', [UserController::class, 'index']);
    Route::get('/profile', [UserController::class, 'profile']);
    Route::get('show/{id}', [UserController::class, 'show']);
    Route::post('create', [UserController::class, 'store']);
    Route::post('update/{id}', [UserController::class, 'update']);
    Route::post('show/{id}', [UserController::class, 'single']);
    Route::post('reset-password/{id}', [UserController::class, 'resetPassword']);
    Route::put('update-status/{id}', [UserController::class, 'updateStatus']);
    Route::delete('delete/{id}', [UserController::class, 'delete']);

    // ROLE
    Route::prefix('roles')->group(function () {
        Route::get('/list', [RoleController::class, 'index']);
        // Route::get('/list', [RoleController::class, 'listRoles']);
        Route::get('/lists-with-permission', [RoleController::class, 'listRolesWithPermissions']);
        Route::post('/store', [RoleController::class, 'store']);

        Route::get('/show/{id}', [RoleController::class, 'show']);

        Route::put('/update/{id}', [RoleController::class, 'update']);
        Route::post('/{id}/assign-permissions', [RoleController::class, 'assignPermissions']);
        Route::post('/assign-role', [RoleController::class, 'assignRole']);

        Route::delete('/{id}', [RoleController::class, 'destroy']);
    });

    // BRANCHES
    Route::prefix('branches')->group(function () {
        Route::get('/', [BranchController::class, 'index']);
        Route::get('/{branch}', [BranchController::class, 'show']);
        Route::post('', [BranchController::class, 'store']);
        Route::put('/{branch}', [BranchController::class, 'update']);
        Route::post('/status/{branch}', [BranchController::class, 'updateStatus']);
        // Route::delete('/delete/{branch}', [BranchController::class, 'delete']);

    });

    // PERMISSION
    Route::prefix('permission')->group(function () {
        Route::get('/list', [PermissionController::class, 'index']);
        Route::get('/list-paginate', [PermissionController::class, 'indexWithPagination']);

        Route::post('/store', [PermissionController::class, 'store']);
        Route::put('/update/{id}', [PermissionController::class, 'update']);
        Route::delete('/{id}', [PermissionController::class, 'destroy']);
    });

    // SETTINGS
    Route::prefix('settings')->group(function () {
        Route::get('all', [SettingsController::class, 'index']);
        Route::post('store', [SettingsController::class, 'store']);
        Route::post('update', [SettingsController::class, 'update']);
    });

    // CURRENCY
    Route::prefix('currencies')->group(function () {
        Route::get('list', [CurrencyController::class, 'index']);
    });

    Route::prefix('customer')->group(function () {
        Route::post('/', [CustomerController::class, 'store']);
    });

    // General Ledger
    Route::prefix('general-ledgers')->group(function () {
        Route::get('/', [GeneralLedgerController::class, 'index']);
        Route::get('{id}', [GeneralLedgerController::class, 'show']);
        Route::post('/', [GeneralLedgerController::class, 'store']);
        Route::put('{id}', [GeneralLedgerController::class, 'update']);
        Route::post('{id}/status', [GeneralLedgerController::class, 'updateStatus']);
    });


    // ACCOUNT CATEGORY
    Route::prefix('account-categories')->group(function () {
        Route::get('/', [AccountCategoryController::class, 'index']);
        Route::post('/', [AccountCategoryController::class, 'store']);
        Route::get('{id}', [AccountCategoryController::class, 'show']);
        Route::put('{id}', [AccountCategoryController::class, 'update']); // name only
        Route::post('{id}/status', [AccountCategoryController::class, 'updateStatus']);
    });


    // ACCOUNT PRODUCT
    Route::prefix('account-product')->group(function () {
        Route::get('/', [AccountProductController::class, 'index']);
        Route::get('/{id}', [AccountProductController::class, 'show']);
        Route::post('/', [AccountProductController::class, 'store']);
        Route::post('/status', [AccountProductController::class, 'updateStatus']);
    });


    // Customers
    Route::prefix('customer')->group(function () {
        Route::post('/', [CustomerController::class, 'store']);
        Route::post('/{id}/activate', [CustomerController::class, 'activate']);
        Route::post('/{id}/reject', [CustomerController::class, 'reject']);
    });





    // SETTINGS
    Route::prefix('account-officers')->group(function () {
        Route::get('', [AccountOfficerController::class, 'index']);
        Route::get('/{id}', [AccountOfficerController::class, 'show']);
        Route::post('/', [AccountOfficerController::class, 'store']);
        Route::put('/{id}', [AccountOfficerController::class, 'update']);
        Route::delete('/{id}', [AccountOfficerController::class, 'delete']);
    });


    Route::post('/logout', [AuthController::class, 'logout'])->middleware('user:user');
});
