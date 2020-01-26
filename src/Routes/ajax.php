<?php 

/*
|--------------------------------------------------------------------------
| Ajax Routes
|--------------------------------------------------------------------------
|
| Here is where you can register ajax routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "ajax" middleware group. Now create something great!
|
*/

Route::get('checkDatabase', 'InstallPreparationController@checkDatabase');