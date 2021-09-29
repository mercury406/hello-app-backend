<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use App\Models\ContactOwner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ContactControllerV2 extends Controller
{
    public function getNearbyUsers(Request $request)
    {
        // $contacts = $request->contacts;
        // $uid = $request->uid;
        // if (!$contacts) return ErMessage("json error");

        $uid = $request->uid;
        $owner = ContactOwner::find($uid);

        // add_contacts($contacts, $uid);

        $mutual = getPossibleContacts($owner);
        
        return ["status" => "ok", "contacts" => $mutual];
    }

    public function storeContacts(Request $request)
    {
        $from = $request->uid;
        $contacts = json_decode($request->contacts, true);

        if (!$contacts) return ErMessage("json error");
        add_contacts($contacts, $from);
        
        $owner = ContactOwner::find($from);
        $mutual = getPossibleContacts($owner);
        return ["status" => "ok", "contacts" => $mutual];
    }

    public function notify(Request $request)
    {
        $uid = $request->from;
        $msisdn = $request->to;
        $notifyingUsers = ContactOwner::where("msisdn", $msisdn)->get();

        foreach ($notifyingUsers as $user){

            $token = $user->fcm; // who will get the notification

            $notifyer = ContactOwner::find($uid); // who sends notification

            $contact = Contact::where(["owner_uid" => $user->uid, "msisdn" => $notifyer->msisdn])->first();
            
            $this->notifications($token, $contact);
        }

        return response()->json(["status" => "ok"]);

    }

    function notifications($token, $contact)
    {
        $path_to_firebase_cm = 'https://fcm.googleapis.com/fcm/send';
        $fields = array(
            'to' => $token,
            // 'notification' => array('title' => $title, 'body' => $message, 'sound' => 'default', 'click_action' => 'FLUTTER_NOTIFICATION_CLICK', 'icon' => 'fcm_push_icon'),
            'data' => ["name" => "$contact->cName", "number" => "$contact->msisdn", "date" => "".date("H:i, d-m-Y"), "canSend" => "y"],
        );
        $headers = array(
            'Authorization:key=AAAAsYcmvC0:APA91bH25enZEkGw4NrXhVNyj74PjYMZUARoRKsYdZ_o-xPClvsxyxfAIEC4nGfntR9u50IqqUKQUbNLEymtQVGA9kYGj_u4gdW74VtIitHPQvOGwBEUcCedsf15y2ntes5KrkZAZwkh',
            'Content-Type:application/json'
        );
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $path_to_firebase_cm);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
        $result = curl_exec($ch);
        curl_close($ch);
        return $result;
    }
}



function add_contacts($contacts, $from)
{
    foreach ((array) $contacts as $c) {
        $final_contact = preg_replace("/\s+/", "", $c["msisdn"]);
        $final_contact = str_replace("-", "", $final_contact);
        Contact::firstOrCreate([
            'cName' => $c["name"],
            'msisdn' => $final_contact,
            'owner_uid' => $from
        ]);
    }
}


function ErMessage($message)
{
    return ["status" => "error", "message" => $message];
}


function getPossibleContacts($owner)
{
    // $a = [];
    // foreach(Contact::all() as $contact){
    //     array_push($a, ["name" => $contact->cName, "msisdn" => $contact->msisdn]);
    // }
    // return $a;


    // start from here
    $adjacent = [];


    foreach ($owner->contacts as $con) {
        // array_push($adjacent, ["name" => $con->cName, "msisdn" => $con->msisdn, "canSendHello" => random_int(0,1) == 0, "distance" => random_int(10, 30) ]);

        // get possible contact uid
        $possibleUser = ContactOwner::where("msisdn", $con->msisdn)->first();
        if (!$possibleUser) continue;

        // does possible contact have owner in contact list: if has adding to array name, number and fcm of contact
        if (Contact::where(["msisdn" => $owner->msisdn, "owner_uid" => $possibleUser->uid])->count() > 0) {
            $contact = Contact::where(["owner_uid" => $owner->uid, "msisdn" => $possibleUser->msisdn])->first();

                $contact_info = ContactOwner::where('msisdn', $contact->msisdn)->first();

                $owner_last_location = $owner->locations->last();
                $peer_last_location = $contact_info->locations->last();
                
                
                

                $distance = haversineGreatCircleDistance($owner_last_location->lat, $owner_last_location->long, $peer_last_location->lat, $peer_last_location->long);
                // $distance = 10;
                if($distance < 25) {
                    array_push($adjacent, ["name" => $contact->cName, "msisdn" => $possibleUser->msisdn, "canSendHello" => true, "distance" => round($distance) ]);
                }
            
        }

    }
    return $adjacent;
}


function haversineGreatCircleDistance(
    $latitudeFrom, $longitudeFrom, $latitudeTo, $longitudeTo, $earthRadius = 6371000)
  {
    // convert from degrees to radians
    $latFrom = deg2rad($latitudeFrom);
    $lonFrom = deg2rad($longitudeFrom);
    $latTo = deg2rad($latitudeTo);
    $lonTo = deg2rad($longitudeTo);
  
    $latDelta = $latTo - $latFrom;
    $lonDelta = $lonTo - $lonFrom;
  
    $angle = 2 * asin(sqrt(pow(sin($latDelta / 2), 2) +
      cos($latFrom) * cos($latTo) * pow(sin($lonDelta / 2), 2)));
    return $angle * $earthRadius;
  }
