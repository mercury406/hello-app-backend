<?php

namespace App\Http\Controllers\Locations;

use App\Models\Location;
use App\Models\ContactOwner;
use Illuminate\Http\Request;
use App\Http\Actions\CanSendHello;
use App\Http\Controllers\Controller;
use App\Http\Actions\CalculateDistance;

class LocationController extends Controller
{

    /**
     * Display the specified resource.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function getNearbyUsers(Request $request)
    {
        $owner = ContactOwner::where('uid', $request->header('Authorization'))
            ->with(['greetings', 'contacts'])
            ->first() ?? abort(404);

        $contacts = $request->contacts ?? abort(403, "No data provided");

        $mutualContacts = collect(json_decode($contacts));

        $final = $mutualContacts->map(function ($mutual) use ($owner) {
            $candidate_contact = $owner->contacts->where('msisdn', $mutual->msisdn)->first();

            if (!$candidate_contact) return [
                "name" => "",
                "msisdn" => $mutual->msisdn,
                "distance" => -1,
                "canSendHello" => false
            ];

            $candidate = ContactOwner::where(['msisdn' => $mutual->msisdn])->with(['location', 'greetings'])->orderByDesc('created_at')->first();
            $distance = new CalculateDistance($owner, $candidate);

            return [
                "name" => $candidate_contact->cName,
                "msisdn" => $mutual->msisdn,
                "distance" => $distance,
                "canSendHello" => new CanSendHello($owner, $candidate, $distance)
            ];
        });

        $final = $final->filter(function ($f) {
            return $f['distance'] >= 0 && $f['name'] != null && $f["distance"] < 25;
        });

        return response()->json(['status' => 'ok', "contacts" => $final->values()], 200);
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $uid = $request->uid;

        ContactOwner::findOrFail($uid);

        $long = $request->long;
        $lat = $request->lat;

        $location = Location::firstOrNew(["contact_id" => $uid]);
        $location->lat = $lat;
        $location->long = $long;
        $location->updated_at = now();
        $location->save();

        return response()->json(["status" => "ok", "message" => "created"], 201);
    }
}
