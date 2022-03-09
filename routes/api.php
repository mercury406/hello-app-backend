<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Locations\LocationController;
use App\Http\Controllers\Contacts\PersonalContactsController;
use App\Http\Controllers\Registration\RegistrationController;
use App\Http\Controllers\Notifications\NotificationController;


// Route::get("/contacts", [PersonalContactsController::class, 'show']);
Route::post("/contacts", [PersonalContactsController::class, 'store']);


Route::apiResource('/location', LocationController::class)->only(['store']);
Route::post('/nearby', [LocationController::class, 'getNearbyUsers']);

Route::apiResource('/notify', NotificationController::class)->only(['store']);

Route::post("/register", [RegistrationController::class, "register"]);
Route::post("/register/{registration}", [RegistrationController::class, "saveUid"]);
Route::post("/get-code", [RegistrationController::class, "getCode"]);
Route::post("/savefcm", [RegistrationController::class, "changeFCM"]);
Route::post("/checkfcm", [RegistrationController::class, "checkFCM"]);
Route::delete("/delete/{owner}", [RegistrationController::class, "destroy"]);