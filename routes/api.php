<?php

use App\Http\Controllers\AuthorController;
use App\Http\Controllers\ImageSetController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::group([
    'middleware' => 'api',
    'prefix' => 'auth'
], function ($router) {
    Route::post('/register', [AuthController::class, 'register'])->name('register');
    Route::post('/login', [AuthController::class, 'login'])->name('login');
    Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:api')->name('logout');
    Route::post('/refresh', [AuthController::class, 'refresh'])->middleware('auth:api')->name('refresh');
    Route::post('/me', [AuthController::class, 'me'])->middleware('auth:api')->name('me');
});

Route::group([
    'middleware' => 'auth:api',
], function ($router) {
    Route::post("/products", [ProductController::class, 'store']);
    Route::delete("/products/{id}", [ProductController::class, 'destroy']);
    Route::resource("/users", UserController::class);
    Route::delete("/image-set/{id}", [ImageSetController::class, "destroy"]);
    Route::post("/image-set", [ImageSetController::class, "upload"]);
    Route::post('/authors', [AuthorController::class, 'store']);
    Route::get('/authors', [AuthorController::class, 'index']);
    Route::get('/authors/{id}', [AuthorController::class, 'show']);
    Route::delete('/authors/{id}', [AuthorController::class, 'destroy']);
    Route::put('/authors/{id}', [AuthorController::class, 'update']);
});

Route::get("/image-set", [ImageSetController::class, "index"]);
Route::get('/products', [ProductController::class, 'index']);
Route::get('/products/{id}', [ProductController::class, 'show']);
