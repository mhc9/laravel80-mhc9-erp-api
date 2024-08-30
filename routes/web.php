<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Request;

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
Route::get('/requisitions/{id}/document', 'App\Http\Controllers\RequisitionController@getDocument');
Route::get('/requisitions/{id}/report', 'App\Http\Controllers\RequisitionController@getReport');
Route::get('/requisitions/{id}/directive', 'App\Http\Controllers\RequisitionController@getDirective');
Route::get('/requisitions/{id}/consider', 'App\Http\Controllers\RequisitionController@getConsider');
Route::get('/requisitions/{id}/notice', 'App\Http\Controllers\RequisitionController@getNotice');

Route::get('/loans/{id}/form', [App\Http\Controllers\LoanController::class, 'getForm']);
Route::get('/loans/{id}/contract', [App\Http\Controllers\LoanController::class, 'getContract']);

/** PWM OT */
Route::get('/invitations/report', [App\Http\Controllers\InvitationController::class, 'getReport']);

/** PWM Car */
Route::get('/cars', [App\Http\Controllers\CarController::class, 'index']);

Route::get('/redirect', function (Request $request) {
    $request->session()->put('state', $state = Str::random(40));

    $query = http_build_query([
        'client_id' => '3',
        'redirect_uri' => 'http://localhost:32772/laravel80-mhc9-erp/public/callback',
        'response_type' => 'code',
        'scope' => '',
        'prompt' => 'none', // "none", "consent", or "login"
    ]);

    return redirect('http://localhost:5000/oauth/authorize?'.$query);
});

Route::get('/callback', function (Request $request) {
    $state = $request->session()->pull('state');

    throw_unless(
        strlen($state) > 0 && $state === $request->state,
        InvalidArgumentException::class,
        'Invalid state value.'
    );

    $response = Http::asForm()->post('http://localhost:5000/oauth/token', [
        'grant_type' => 'authorization_code',
        'client_id' => '3',
        'redirect_uri' => 'http://localhost:32772/laravel80-mhc9-erp/public/callback',
        'code' => $request->code,
    ]);

    return $response->json();
});