<?php

use Illuminate\Http\Request;

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

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('options2', 'TicketController@options2');
Route::get('options', 'TicketController@options');
Route::get('ticket/open', 'TicketController@open');
Route::get('ticket/close', 'TicketController@close');
Route::get('ticket', 'TicketController@info');
Route::post('create/comment', 'TicketController@comment');
Route::post('register', 'RegisterController@store');
Route::post('create/ticket', 'TicketController@store');
Route::post('close/ticket', 'TicketController@closeticket');
Route::get('mail/tickets', 'TicketController@sendMail');