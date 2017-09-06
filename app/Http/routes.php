<?php

Route::group(['prefix' => 'api/v1'], function (){

    Route::resource('meeting', 'MeetingController', [
        'except' => ['edit', 'create']
    ]);

    Route::resource('meeting/registration', 'RegistrationController', [
        'only' => ['store', 'destroy']
    ]);

    Route::post('meeting/user',[
        'uses' => 'AuthController@store'
    ]);

    Route::post('user/signin',[
        'uses' => 'AuthController@signin'
    ]);

});

Route::controllers([
	'auth' => '\App\Http\Controllers\AuthController',
]);
