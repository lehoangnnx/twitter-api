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
Route::get('/tweet/show/{id}', 'AuthTwitterController@showTweet')->name('showTweet');
Route::get('/tweet/retweet', 'AuthTwitterController@retweetTweet')->name('retweetTweet');
Route::get('/tweet/favorites', 'AuthTwitterController@favoritesTweet')->name('favoritesTweet');
Route::post('/tweet/reply', 'AuthTwitterController@replyTweet')->name('replyTweet');

Auth::routes();

URL::forceScheme('https');
