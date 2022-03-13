<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('insert-cats', function(){

    \App\Models\CatJson::create(
        ['info' => json_encode(['name' => 'Fluffy', 'long-hair' => true])]
    );

    \App\Models\CatJson::create(
        ['info' => json_encode(['name' => 'Furball', 'long-hair' => false])]
    );

    \App\Models\CatJson::create(
        ['info' => json_encode(['name' => 'Igor', 'long-hair' => true])]
    );

    echo('done');
});