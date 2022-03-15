<?php

namespace App\Http\Controllers\Notifications;

use App\Models\Contact;
use App\Models\Greeting;
use App\Models\ContactOwner;
use Illuminate\Http\Request;
use App\Http\Actions\CanSendHello;
use App\Http\Controllers\Controller;
use App\Http\Actions\SendNotification;

class NotificationController extends Controller
{
    public function store(Request $request)
    {
        $uid = $request->from;
        $msisdn = $request->to;
        $notifyingUsers = ContactOwner::where("msisdn", $msisdn)->get();
        $notifyer = ContactOwner::find($uid) ?? abort(403); // who sends notification
        
        foreach ($notifyingUsers as $user){
            Greeting::firstOrCreate(['from' => $uid, 'to' => $msisdn])->touch();
            $token = $user->fcm; // who will get the notification

            new SendNotification($token, $user, $notifyer);
        }

        return response()->json(["status" => "ok"], 201);
    }


    
}
