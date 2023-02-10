<?php

use App\Http\Controllers\CancelReservation;
use App\Http\Controllers\CreateReservation;
use App\Http\Controllers\IssueToken;
use App\Http\Controllers\ShowReservations;
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

Route::post('/token', IssueToken::class);
Route::middleware('auth:sanctum')->group(function (Router $router) {
    $router->get('/reservations', ShowReservations::class);
    $router->post('/reservations', CreateReservation::class);
    $router->put('/reservations/{id}/cancel', CancelReservation::class);
});
