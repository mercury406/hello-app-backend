<?php

namespace App\Http\Controllers\Locations;

use Carbon\Carbon;
use App\Models\Location;
use App\Models\ContactOwner;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Services\ContactService;

class LocationController extends Controller
{

    /**
     * The contact service instance.
     */
    protected $contact_service;

    /**
     * Create a new controller instance.
     * 
     * @param \App\Http\Services\ContactService
     * @return void
     */

    public function __construct(ContactService $contact_service) {
        $this->contact_service = $contact_service;
    }

    /**
     * Display the specified resource.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function getNearbyUsers(Request $request)
    {
        
        $owner = ContactOwner::where('uid', $request->header('Authorization') )
                                ->with(['greetings', 'contacts'])
                                ->first() ?? abort(404);



        $contacts = $request->contacts ?? abort(403, "No data provided");
        

        // dd($contacts);

        
        // $mutualContacts = $owner->contacts->filter(function ($contact){
        //     return ContactOwner::where(['msisdn' => $contact->msisdn])->with('location')->orderByDesc('created_at')->first();
        // });

        $mutualContacts = collect(json_decode($contacts));
        
        $final = $mutualContacts->map(function($mutual) use($owner){
            // dd($mutual->msisdn);
            $candidate_contact = $owner->contacts->where('msisdn', $mutual->msisdn)->first();
            
            // dd($candidate_contact);
            
            if(!$candidate_contact) return [
                "name" => null,
                "msisdn" => $mutual->msisdn,
                "distance" => -1,
                "canSendHello" => false
            ];

            
            $candidate = ContactOwner::where(['msisdn' => $mutual->msisdn])->with(['location', 'greetings'])->orderByDesc('created_at')->first();
            $distance = $this->contact_service->distanceCalculation($owner, $candidate);
                   
            return [
                "name" => $candidate_contact->cName,
                "msisdn" => $mutual->msisdn,
                "distance" => $distance,
                "canSendHello" => $this->contact_service->canSendHello($owner, $candidate, $distance)
            ];
        });

        $final = $final->filter(function($f) {
            return $f['distance'] >= 0 && $f['name'] != null;
        });

        return response()->json(['status' => 'ok', "contacts" => $final->values()], 200);

        
        

        // $personalContactInstance = PersonalContact::where('contact_id', $contact_id)->firstOrFail();

        // dd($personalContactInstance->location);

        // $finalContacts = [];
        // $data = json_decode($personalContactInstance->data);

        // $greetings = $personalContactInstance->greetings;
        // // $to = $greetings->first()->to;

        // foreach($data->contacts as $con) {

        //     $contact = ContactOwner::where('msisdn', $con->msisdn)->first();

        //     if(!$contact) continue;

        //     $distance = $this->contact_service->distanceCalculation($personalContactInstance, $contact);

        //     $canSendHello = true;

        //     if($distance < 0) continue; 

        //     if( $distance > self::MAX_DISTANCE ) $canSendHello = false;

        //     $greeting = $greetings->where('to', $con->msisdn)->first();

        //     $now = Carbon::now();
        //     $updated = Carbon::parse($greeting->updated_at);

        //     if($updated->diffInSeconds($now) > self::MIN_TIMEDIFFERENCE_IN_SECONDS) $canSendHello = true;

        //     $finalContacts[] = [
        //         "name" => $con->name, 
        //         "msisdn" => $con->msisdn, 
        //         "distance" => $distance,
        //         "canSendHello" => $canSendHello
        //     ];

        // }

        // return response()->json(['status' => 'ok', 'contacts' => $finalContacts], 200);
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

        Location::updateOrCreate(["contact_id" => $uid], ["lat" => $lat, "long" => $long]);

        return response()->json(["status"=> "ok", "message" => "created"], 201);
     
        // if(Location::create([ "lat" => $lat, "long" => $long, "contact_id" => $uid]))
            // return response()->json(["status"=> "ok", "message" => "created", "code" => 201]); 
        // return response()->json(["status"=> "error", "message" => "Wrong!", "code" => 400]); 
    }
}
