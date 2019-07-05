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
    Route::get('api/getpeminjaman','PengembalianbarangController@getPeminjaman');
    Route::get('api/getdetailmasuk','InventarismasukController@getdetailinventaris');
    Route::get('api/getdetailkeluar','InventariskeluarController@getdetailinventaris');
});

