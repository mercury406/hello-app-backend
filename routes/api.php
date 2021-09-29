<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\ContactControllerV2;
use App\Http\Controllers\LocationControllerV2;
use App\Http\Controllers\RegisterControllerV2;
use App\Http\Controllers\RegistrationController;

// Route::middleware('auth:api')->get('/user', function (Request $request) {
//     return $request->user();
// });

Route::post("/contacts", [ContactController::class, "storeContacts"]);
Route::post("/nearby", [ContactController::class, 'getNearbyUsers']);
Route::post("/notify", [ContactController::class, "notify"]);

Route::post("/location", [LocationController::class, "saveLocation"]);


Route::post("/register", [RegistrationController::class, "register"]);
Route::post("/register/{registration}", [RegistrationController::class, "saveUid"]);
Route::post("/get-code", [RegistrationController::class, "getCode"]);
Route::post("/savefcm", [RegistrationController::class, "changeFCM"]);
Route::post("/checkfcm", [RegistrationController::class, "checkFCM"]);


Route::group(["prefix" => "v1"], function() {
    Route::post("/register", [RegisterControllerV2::class, "register"]);
    Route::post("/register/{registration}", [RegisterControllerV2::class, "saveUid"]);
    Route::post("/get-code", [RegisterControllerV2::class, "getCode"]);
    Route::post("/savefcm", [RegisterControllerV2::class, "changeFCM"]);
    Route::post("/checkfcm", [RegisterControllerV2::class, "checkFCM"]);


    Route::post("/contacts", [ContactControllerV2::class, "storeContacts"]);
    Route::post("/nearby", [ContactControllerV2::class, 'getNearbyUsers']);
    Route::post("/notify", [ContactControllerV2::class, "notify"]);

    Route::post("/location", [LocationControllerV2::class, "saveLocation"]);
});