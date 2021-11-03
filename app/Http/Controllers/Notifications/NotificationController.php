<?php

namespace App\Http\Controllers\Notifications;

use App\Models\Contact;
use App\Models\Greeting;
use App\Models\ContactOwner;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Services\ContactService;

class NotificationController extends Controller
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

    public function store(Request $request)
    {
        
        $uid = $request->from;
        $msisdn = $request->to;
        $notifyingUsers = ContactOwner::where("msisdn", $msisdn)->get();
        $notifyer = ContactOwner::find($uid) ?? abort(403); // who sends notification
        
        foreach ($notifyingUsers as $user){
            Greeting::firstOrCreate(['from' => $uid, 'to' => $msisdn])->touch();
            $token = $user->fcm; // who will get the notification
            info($user->msisdn);

            $this->helloNotification($token, $user, $notifyer);
            // $contact_of_user = Contact::where(['owner_uid' => $uid, 'msisdn' => $user->msisdn])->first();
        }

        return response()->json(["status" => "ok"], 201);
    }


    /**
     * Calculates if user ContactOwner send hello to another Contact
     * 
     * @param string $token
     * @param ContactOwner $to
     * @param ContactOwner $from
     */
    function helloNotification($token, $to, $from)
    {
        
        $contact = Contact::where(["owner_uid" => $to->uid, "msisdn" => $from->msisdn])->first() ?? abort(404);
        $path_to_firebase_cm = 'https://fcm.googleapis.com/fcm/send';
        $fields = array(
            'to' => $token,
            'data' => [
                "event" => "newHelloNotification", 
                "name" => $contact->cName, 
                "number" => $contact->msisdn, 
                "date" => "".date("H:i, d-m-Y"), 
                "canSend" => $this->contact_service->canSendHello($to, $from, 0) ? 'y' : 'n'
            ],
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
