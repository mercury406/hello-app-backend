<?php

namespace App\Http\Controllers;

use App\Models\ContactOwner;
use App\Models\Location;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class LocationControllerV2 extends Controller
{
    public function saveLocation(Request $request)
    {

        $uid = $request->uid;

        ContactOwner::findOrFail($uid);
        $long = $request->long;
        $lat = $request->lat;

        Location::updateOrCreate(["contact_id" => $uid], ["lat" => $lat, "long" => $long]);

     
        // if(Location::create([ "lat" => $lat, "long" => $long, "contact_id" => $uid]))
            return response()->json(["status"=> "ok", "message" => "created", "code" => 201]); 
        return response()->json(["status"=> "error", "message" => "Wrong!", "code" => 400]); 
    
    }
}
