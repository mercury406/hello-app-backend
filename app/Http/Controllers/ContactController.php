<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use App\Models\ContactOwner;
use Illuminate\Http\Request;

class ContactController extends Controller
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
        $contacts = $request->contacts;
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

            $cName = Contact::where(["owner_uid" => $user->uid, "msisdn" => $notifyer->msisdn])->first()->cName;
            
            $title = "Someone sent you <Hello!>";
            $message = "$cName says <HELLO>";
            $this->notifications($token, $title, $message);
        }

    }

    function notifications($token = null, $title = null, $message = null)
    {
        $path_to_firebase_cm = 'https://fcm.googleapis.com/fcm/send';
        $fields = array(
            'to' => $token,
                'notification' => array('title' => $title, 'body' => $message, 'sound' => 'default', 'click_action' => 'FLUTTER_NOTIFICATION_CLICK', 'icon' => 'fcm_push_icon'),
            'data' => array('title' => $title, 'body' => $message, 'sound' => 'default', 'icon' => 'fcm_push_icon'),
        );
        $headers = array(
            'Authorization:key=' . 'AAAA0idj900:APA91bFCBd4sjPOHhxFP1mTc5vQjvow3asx56bjT_AEmPCD9cfVkdqmgyWuuoKYyYfaUbQi1lHe_Fe1EpynsnUK0OdPazaBXH563gGCYJVkjvn9LdKfa-1Eg_jV-NkXaaWvo-ryk1R5i',
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
    foreach ($contacts as $c) {
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
        // get possible contact uid
        $possibleUser = ContactOwner::where("msisdn", $con->msisdn)->first();
        if (!$possibleUser) continue;

        // does possible contact have owner in contact list: if has adding to array name, number and fcm of contact
        if (Contact::where(["msisdn" => $owner->msisdn, "owner_uid" => $possibleUser->uid])->count() > 0) {
            $contact = Contact::where(["owner_uid" => $owner->uid, "msisdn" => $possibleUser->msisdn])->first();
            array_push($adjacent, ["name" => $contact->cName, "msisdn" => $possibleUser->msisdn]);
        }
    }
    return $adjacent;
}
