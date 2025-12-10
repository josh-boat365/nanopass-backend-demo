<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\PrivilegeController;
use App\Http\Controllers\PasswordPolicyController;
use App\Http\Controllers\SystemPasswordController;
use App\Http\Controllers\PasswordCategoryController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

//Authentication Routes
Route::post('/register', [AuthController::class, 'store']);
Route::post('/login', [AuthController::class, 'login']);

//User Management Routes
Route::get('/admin/users', [UserController::class, 'index']);
Route::post('/admin/create-user', [UserController::class, 'store']);
Route::get('/admin/edit-user/{user}', [UserController::class, 'show']);
Route::post('/admin/update-user/{user}', [UserController::class, 'update']);
Route::delete('/admin/delete-user/{user}', [UserController::class, 'destroy']);

//Privilege Management Routes
Route::post('/admin/privilege', [PrivilegeController::class, 'index']);
Route::post('/admin/create-privilege', [PrivilegeController::class, 'store']);
Route::get('/admin/edit-privilege/{$privilege}', [PrivilegeController::class, 'show']);
Route::get('/admin/update-privilege/{$privilege}', [PrivilegeController::class, 'update']);
Route::get('/admin/delete-privilege/{$privilege}', [PrivilegeController::class, 'destroy']);

//Password Category Management Routes
Route::get('/admin/password-categories', [PasswordCategoryController::class, 'index']);
Route::post('/admin/create-password-category', [PasswordCategoryController::class, 'store']);
Route::get('/admin/edit-password-category/{category}', [PasswordCategoryController::class, 'show']);
Route::post('/admin/update-password-category/{category}', [PasswordCategoryController::class, 'update']);
Route::delete('/admin/delete-password-category/{category}', [PasswordCategoryController::class, 'destroy']);

//Password Policy Management Routes
Route::get('/admin/password-policies', [PasswordPolicyController::class, 'index']);
Route::post('/admin/create-password-policy', [PasswordPolicyController::class, 'store']);
Route::get('/admin/edit-password-policy/{policy}', [PasswordPolicyController::class, 'show']);
Route::post('/admin/update-password-policy/{policy}', [PasswordPolicyController::class, 'update']);
Route::delete('/admin/delete-password-policy/{policy}', [PasswordPolicyController::class, 'destroy']);

//System Password Management Routes
Route::get('/admin/system-passwords', [SystemPasswordController::class, 'index']);
Route::post('/admin/create-system-password', [SystemPasswordController::class, 'store']);
Route::get('/admin/edit-system-password/{systemPassword}', [SystemPasswordController::class, 'show']);
Route::post('/admin/update-system-password/{systemPassword}', [SystemPasswordController::class, 'update']);
Route::delete('/admin/delete-system-password/{systemPassword}', [SystemPasswordController::class, 'destroy']);




