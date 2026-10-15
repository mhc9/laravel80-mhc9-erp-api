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

Route::get('/manual-user', [App\Http\Controllers\ManualController::class, 'index']);

/** Requisition */
Route::get('/requisitions/{id}/print-pr', [App\Http\Controllers\RequisitionController::class, 'printPR']);
Route::get('/requisitions/{id}/document', [App\Http\Controllers\RequisitionController::class, 'getDocument']);
Route::get('/requisitions/{id}/report', [App\Http\Controllers\RequisitionController::class, 'getReport']);
Route::get('/requisitions/{id}/directive', [App\Http\Controllers\RequisitionController::class, 'getDirective']);
Route::get('/requisitions/{id}/consider', [App\Http\Controllers\RequisitionController::class, 'getConsider']);
Route::get('/requisitions/{id}/notice', [App\Http\Controllers\RequisitionController::class, 'getNotice']);

/** Inspection */
Route::get('/inspections/{id}/form', [App\Http\Controllers\InspectionController::class, 'getForm']);
Route::get('/inspections/{id}/report', [App\Http\Controllers\InspectionController::class, 'getReport']);

/** Loan */
Route::get('/loans/{id}/form', [App\Http\Controllers\LoanController::class, 'getForm']);
Route::get('/loans/{id}/contract', [App\Http\Controllers\LoanController::class, 'getContract']);

/** Loan Contract */
Route::get('/loan-contracts/export/{year}', [App\Http\Controllers\LoanContractController::class, 'export']);

/** LoanRefund */
Route::get('/loan-refunds/{id}/form', [App\Http\Controllers\LoanRefundController::class, 'getForm']);
Route::get('/loan-refunds/{id}/over20', [App\Http\Controllers\LoanRefundController::class, 'getOver20']);
Route::get('/loan-refunds/{id}/return', [App\Http\Controllers\LoanRefundController::class, 'getReturn']);

/** WPM OT */
Route::get('/invitations/report', [App\Http\Controllers\InvitationController::class, 'getReport']);

/** WPM Car */
Route::get('/cars', [App\Http\Controllers\CarController::class, 'index']);

Route::get('/redirect', function (Request $request) {
    $request->session()->put('state', $state = Str::random(40));

    $query = http_build_query([
        'client_id'     => env('OAUTH_CLIENT_ID'),
        'redirect_uri'  => env('OAUTH_REDIRECT_URL'),
        'response_type' => 'code',
        'scope'         => '',
        'state'         => $state,
    ]);

    return redirect(env('OAUTH_HOST_URL').'/oauth/authorize?'.$query);
});

Route::get('/callback', function (Request $request) {
    $state = $request->session()->pull('state');

    throw_unless(
        strlen($state) > 0 && $state === $request->state,
        InvalidArgumentException::class,
        'Invalid state value.'
    );

    $response = Http::asForm()->post(env('OAUTH_HOST_URL').'/oauth/token', [
        'grant_type'    => 'authorization_code',
        'client_id'     => env('OAUTH_CLIENT_ID'),
        'client_secret' => env('OAUTH_CLIENT_SECRET'),
        'redirect_uri'  => env('OAUTH_REDIRECT_URL'),
        'code'          => $request->code,
    ]);

    return $response->json();
});