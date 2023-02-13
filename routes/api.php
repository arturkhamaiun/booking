<?php

use App\Http\Controllers\Reservation\CancelReservation;
use App\Http\Controllers\Reservation\StoreReservation;
use App\Http\Controllers\Auth\IssueToken;
use App\Http\Controllers\Reservation\ListReservations;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::post('/token', IssueToken::class)->name('token.issue');
Route::middleware('auth:sanctum')->group(function (Router $router) {
    $router->get('/reservations', ListReservations::class)->name('reservations.list');
    $router->post('/reservations', StoreReservation::class)->name('reservations.store');
    $router->put('/reservations/{id}/cancel', CancelReservation::class)->name('reservations.cancel');
});
