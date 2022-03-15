<?php

namespace App\Http\Actions;

use App\Models\Contact;
use App\Http\Actions\CanSendHello;


class SendNotification {

    public function __construct($token, $to, $from) {
        $this->token = $token;
        $this->to = $to;
        $this->from = $from;
        $this->firebase_key = env('FIREBASE_KEY');
    }

    public function __invoke()
    {
        $contact = Contact::where(["owner_uid" => $this->to->uid, "msisdn" => $this->from->msisdn])->first() ?? abort(404);
        $path_to_firebase_cm = 'https://fcm.googleapis.com/fcm/send';
        $fields = array(
            'to' => $this->token,
            'data' => [
                "event" => "newHelloNotification", 
                "name" => $contact->cName, 
                "number" => $contact->msisdn, 
                "date" => "".date("H:i, d-m-Y"), 
                "canSend" => new CanSendHello($this->to, $this->from, 0) ? 'y' : 'n'
            ],
        );

        $headers = array(
            'Authorization:key=' . $this->firebase_key,
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