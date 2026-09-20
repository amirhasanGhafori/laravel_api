<?php

use App\Http\Controllers\PostController;
use App\Http\Controllers\UserController;
use App\Jobs\ReconcileAccount;
use App\Models\Post;
use Illuminate\Pipeline\Pipeline;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});


Route::get('/users', [UserController::class,'index'])->name('users');
Route::get('/posts', [PostController::class,'index'])->name('posts');


Route::get('/pipeline',function(){
    $pipeline = app(Pipeline::class);
    $pipeline->send('hello world')
    ->through([
        function ($payload, $next) {
            $string = ucwords($payload);
            return $next($string);
        },
        function ($payload, $next) {
            $stringArray = explode(' ', $payload);
            return $next($stringArray);
        },
        Post::class
    ])
    ->then(function ($value) {
        dump($value);
    });



    return "Done";

});