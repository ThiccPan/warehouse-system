<?php

use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\MutationController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::post('/register', [UserController::class, 'register']);
Route::post('/login', [UserController::class, 'login']);
Route::middleware('auth:sanctum')->post('/logout', [UserController::class, 'logout']);

Route::middleware('auth:sanctum')
    ->prefix('/location')
    ->group(function () {
        Route::get('', [LocationController::class, 'index']);
        Route::get('/{id}', [LocationController::class, 'show']);
        Route::post('', [LocationController::class, 'insert']);
        Route::put('/{id}', [LocationController::class, 'update']);
        Route::delete('/{id}', [LocationController::class, 'delete']);
    });

Route::middleware('auth:sanctum')
    ->prefix('categories')
    ->group(function () {
        Route::get('', [CategoryController::class, 'index']);
        Route::get('/{id}', [CategoryController::class, 'show']);
        Route::post('', [CategoryController::class, 'insert']);
        Route::put('/{id}', [CategoryController::class, 'update']);
        Route::delete('/{id}', [CategoryController::class, 'delete']);
    });

Route::middleware('auth:sanctum')
    ->prefix('items')
    ->group(function () {
        Route::get('', [ItemController::class, 'index']);
        Route::get('/{id}', [ItemController::class, 'show']);
        Route::post('', [ItemController::class, 'insert']);
        Route::put('/{id}', [ItemController::class, 'update']);
        Route::delete('/{id}', [ItemController::class, 'delete']);
    });

Route::middleware('auth:sanctum')
    ->prefix('mutations')
    ->group(function () {
        Route::get('', [MutationController::class, 'index']);
        Route::get('/{id}', [MutationController::class, 'show']);
        Route::post('', [MutationController::class, 'insert']);
        Route::put('/{id}', [MutationController::class, 'update']);
        Route::delete('/{id}', [MutationController::class, 'delete']);
    });
