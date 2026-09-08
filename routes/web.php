<?php

use App\Http\Controllers\PostController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});


Route::get('/users', [UserController::class,'index'])->name('users');
Route::get('/posts', [PostController::class,'index'])->name('posts');