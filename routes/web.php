<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Process;

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

Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

Route::get('/requisitions/{id}/print-pr', 'App\Http\Controllers\RequisitionController@printPR');

Route::get('/cars', [App\Http\Controllers\CarController::class, 'index']);

Route::get('/linkstorage', function () {
    echo Process::run("php artisan storage:link");
});
