<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Artisan;
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

Route::get('/', function () {
   // return view('welcome');
	return "meta-hi.uz в разработке";
});

Route::get('/privacy', function() {
	return view('privacy');
});

Route::get('/privacy/ru', function() {
	return view('privacy_ru');
});


// Route::get('/migrate', function() {
// 	$a = Artisan::call('migrate');
// 	return $a;
// });


// Route::get('/rollback', function() {
// 	$a = Artisan::call('migrate:rollback');
// 	return $a;
// });

// Route::get('/fresh', function() {
// 	$a = Artisan::call('migrate:fresh');
// 	return $a;
// });

// Route::get('/makemigration', function() {
// 	Artisan::call('make:migration add_soft_deletes_to_contacts_table');
// });
