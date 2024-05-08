<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ApplicationController;
use App\Http\Controllers\Utils\WebPushController;

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

Route::get('/{any}', [ApplicationController::class, 'index'])->where('any', '.*');


//Store a push subscriber.
Route::post('/push',[WebPushController::class, 'store']);

//make a push notification.
Route::get('/push','WebPushController@sendWebPushNewChat')->name('sendWebPushNewChat');

//make a push notification.
//Route::post('/send-web-push-new-chat',[WebPushController::class, 'sendWebPushNewChat'])->name('sendWebPushNewChat'););
