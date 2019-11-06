<?php

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

//Route::get('/', function () {
//    return view('twitter');
//});
//Route::get('/', 'AuthTwitterController@requestToken');

Route::get('/', function () {
    return redirect('login');
});

Route::get('/auth/twitter', 'AuthTwitterController@auth');


Route::get('/home', 'AuthTwitterController@requestToken')->name('home');

Route::post('/tweet/create', 'AuthTwitterController@createTweet')->name('createTweet');
Route::get('/tweet/delete', 'AuthTwitterController@deleteTweet')->name('deleteTweet');

Auth::routes();

URL::forceScheme('https');
