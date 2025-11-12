<?php

use App\Http\Controllers\Tenant\BookController;
use App\Http\Controllers\Tenant\PettyCashBookController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

//Route::put('/reservations/{id}', [BookController::class, 'update']);

Route::put('reservations/attachments', [BookController::class, 'attachments']);

Route::apiResource('/reservations', BookController::class);

Route::get('customers/{document_number}', [BookController::class, 'searchCustomer']);
Route::get('customers/ruc/{ruc_number}', [BookController::class, 'searchCustomerByRuc']);

Route::get('customer_record/{document_number}', [BookController::class, 'customer_record']);


