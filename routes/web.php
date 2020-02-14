<?php

// Route::get('/', 'HomeController@index')->name('home');


Route::get('dashboard', ['uses' => 'DashBoardController@index']);
