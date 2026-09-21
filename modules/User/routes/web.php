<?php

use App\Http\Controllers\UserController;
use Illuminate\Pipeline\Pipeline;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});



Route::get('/users/show', [UserController::class,'index'])->name('users');


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
    ])
    ->then(function ($value) {
        dump($value);
    });


    return "Done";

});