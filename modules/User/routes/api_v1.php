<?php

use App\Http\Controllers\Api\V1\AuthorTicketsController;
use App\Http\Controllers\Api\V1\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    Route::apiResource('users.tickets', AuthorTicketsController::class)->except(['update']);
    Route::put('users/{user}/tickets/{ticket}', [AuthorTicketsController::class, 'replace']);
    Route::patch('users/{user}/tickets/{ticket}', [AuthorTicketsController::class, 'update'])->name('author.ticket.update');


    Route::apiResource('users', UserController::class)->except('update');
    Route::patch('users/{user}', [UserController::class, 'update']);
    Route::put('users/{users}', [UserController::class, 'replace']);
    //group route manager
});