<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use App\Models\ContactOwner;
use App\Models\Registration;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class RegisterControllerV2 extends Controller
{

    public function register(Request $request){

        if(!isset($request->phone) && !is_numeric($request->phone) && isset($request->code) && is_numeric($request->code)){
            return response()->json(["status"=> "error", "message" => "Wrong code format. Must be numeric", "code" => 400]);
        }

        if(strlen($request->phone) != 12 && strlen($request->code) != 4){
            return response()->json(["status"=> "error", "message" => "Wrong code formnat. Must be 4 characters long", "code" => 400]);
        }

        $opCodes = ['33', '88', '90', '91', '93', '94', '95', '97', '98', '99'];

        $phoneOp = substr($request->phone, 3, 2);
        $phoneCountryCode = substr($request->phone, 0, 3);

        if($phoneCountryCode != "998"){
            return response()->json(["status"=> "error", "message" => "Only Uzbekistan (+998) numbers allowed (temporary)", "code" => 400]);
        }
        
        if(!in_array($phoneOp, $opCodes)){
            return response()->json(["status"=> "error", "message" => "Wrong operator code", "code" => 400]);
        }

        $phone = $request->phone;
        $code = $request->code;
        
        $registration = Registration::where(["msisdn" => $phone, 'status' => '0'])->orderByDesc('id')->first();
        if($registration && $registration->count() > 0){
            if($registration->code == $code){
                $registration->status = 1;
                $registration->save();
                return response()->json(["status" => 'ok', "message" => "registered", "registration" => $registration->id, "code" => 201]);
            } else{
                return response()->json(["status"=> "error", "message" => "Wrong confirmation code", "code" => 400]);
            }
        } else{
            return response()->json(["status"=> "error", "message" => "Phone not found", "code" => 400]);
        }
    }
    //

    public function getCode(Request $request) {
        

        if(!isset($request->phone) && !is_numeric($request->phone)){
            return response()->json(["status"=> "error", "message" => "Wrong number. Must be numeric", "code" => 400]);
        }

        if(strlen($request->phone) != 12){
            return response()->json(["status"=> "error", "message" => "Wrong number. Must be 12 character long", "code" => 400]);
        }

        $counrtyCode = substr($request->phone, 0, 3);
        if($counrtyCode != "998"){
            return response()->json(["status"=> "error", "message" => "Only Uzbekistan number allowed (temporary).", "code" => 400]);
        }

        $opCodes = ['33', '88', '90', '91', '93', '94', '95', '97', '98', '99'];
        $phoneOp = substr($request->phone, 3, 2);
        
        if(!in_array($phoneOp, $opCodes)){
            return response()->json(["status"=> "error", "message" => "Wrong operator code", "code" => 400]);
        }

        $reg = Registration::firstOrNew([
            'msisdn' => $request->phone,
            'status' => 0
        ]);

        $reg->code = mt_rand(1000, 9999);
        $reg->save();
        $phone = $reg->msisdn;
        $code = $reg->code;

        $URL = "http://81.95.228.2:8080/sms_send.php?action=sms&msisdn=$phone&body=$code";
        $response = Http::get($URL);
        return response()->json(["status"=> "ok", "message" => "$response", "code" => 201]);
        // return $response;
    }

    public function saveUid(Registration $registration, Request $request)
    {
        $uid = $request->uid;
        $registration->after_reg_id = $uid;
        $registration->save();

        ContactOwner::create([
            "uid" => $uid,
            "msisdn" => "+".$registration->msisdn,
        ]);
        return response()->json(["status"=> "ok", "code" => 201]);
    }

    public function changeFCM(Request $request){
        if(isset($request->uid) && isset($request->fcm)){
            $owner = ContactOwner::findOrFail($request->uid);
            
            $owner->fcm = $request->fcm;
            $owner->save();
            return response()->json(["status" => 'ok', "message" => "changed", "code" => 201]);
            // if($owner){
                
            // } else{
            //     response()->json(["status" => 'ok', "message" => "Not found", "code" => 404]);
            // }
            
        } else{
            return response()->json(["status"=> "error", "message" => "Incorrect data", "code" => 422]);
        }
    }

    public function checkFCM(Request $request)
    {
        $c = ContactOwner::where(["uid" => $request->uid, "fcm" => Null])->count();
        return response()->json(["status"=> "ok", "message" => "$c"]);
    }
}
