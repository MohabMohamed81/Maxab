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

//First Solution
Route::post('/sessionSchedule','ScheduleController@sessionSchedule');


//Second Solution
Route::post('/sessionSchedule2','ScheduleController2@sessionSchedule');
