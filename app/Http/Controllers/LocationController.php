<?php

namespace App\Http\Controllers;

use App\Models\ContactOwner;
use App\Models\Location;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class LocationController extends Controller
{
    public function saveLocation(Request $request)
    {

        $uid = $request->uid;

        ContactOwner::findOrFail($uid);
        $long = $request->long;
        $lat = $request->lat;
     
        if(Location::create([ "lat" => $lat, "long" => $long, "contact_id" => $uid]))
            return response("Created", 201); 
        abort(400);
    
    }
}
