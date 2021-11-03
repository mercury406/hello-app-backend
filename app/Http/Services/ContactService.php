<?php

namespace App\Http\Services;

use DateTime;
use Carbon\Carbon;
use App\Models\Contact;
use App\Models\ContactOwner;


class ContactService {

    const MAX_TIMEDIFFERENCE_IN_MINUTES = 15;

    const MAX_DISTANCE_IN_METERS = 10000;
    const MIN_TIMEDIFFERENCE_IN_SECONDS = 4 * 60 * 60;

    /**
     * @param string $message
     * 
     * @return array
     */

    public function ErMessage($message)
    {
        return ["status" => "error", "message" => $message];
    }



    /**
     * 
     * @param array $contacts
     * @param string $from
     * 
     * @return array
     * 
     */

    public function add_contacts($contacts, $from)
    {
        $owner = ContactOwner::find($from);
        $owners = ContactOwner::lazy();
        
        if(!$owner) return;

        $mutualContacts = [];

        foreach ((array) $contacts as $c) {
            $final_contact = preg_replace("/\s+/", "", $c["msisdn"]);
            $final_contact = str_replace("-", "", $final_contact);
            $added_contact = Contact::updateOrCreate(
                ['owner_uid' => $from, 'msisdn' => $final_contact], 
                [ 'cName' => $c["name"] ]
            );

            $candidate = $owners->where('msisdn', $final_contact)->first();
            if($candidate) {
                $from_owner_contact_in_candidate_contact_list = $candidate->contacts->where('msisdn', $owner->msisdn)->first();
                if($from_owner_contact_in_candidate_contact_list) {
                    $candidate->notifyAboutNewFriendConnected($from_owner_contact_in_candidate_contact_list);
                }
                $mutualContacts[] = ['name' => $c["name"], 'msisdn' => $final_contact];
            }
            
        }

        return $mutualContacts;

        

    }


    /**
     * 
     * Calculating distance between coordinates
     * 
     * @param double $latitudeFrom
     * @param double $longitudeFrom
     * @param double $latitudeTo
     * @param double $longitudeTo
     * @param int $earthRadius = 6371000
     * 
     * @return int
     */
    private function haversineGreatCircleDistance($latitudeFrom, $longitudeFrom, $latitudeTo, $longitudeTo, $earthRadius = 6371000)
    {
        $latFrom = deg2rad($latitudeFrom);
        $lonFrom = deg2rad($longitudeFrom);
        $latTo = deg2rad($latitudeTo);
        $lonTo = deg2rad($longitudeTo);
      
        $latDelta = $latTo - $latFrom;
        $lonDelta = $lonTo - $lonFrom;
      
        $angle = 2 * asin(sqrt(pow(sin($latDelta / 2), 2) + cos($latFrom) * cos($latTo) * pow(sin($lonDelta / 2), 2)));
        return (int) round($angle * $earthRadius, 0);
    }


    /**
     * @param ContactOwner $main
     * @param ContactOwner $candidate
     * 
     * @return int
     */
    public function distanceCalculation(ContactOwner $main = null, ContactOwner $candidate = null)
    {
        if($main == null || $candidate == null) return -1;

        $time_of_main = new DateTime($main->location->updated_at);

        $time_of_candidate = new DateTime($candidate->location->updated_at);

        // return $this->haversineGreatCircleDistance();
        $difference = $time_of_main->diff($time_of_candidate);

        if($difference->format("%h") > 0 || $difference->format("%i") > self::MAX_TIMEDIFFERENCE_IN_MINUTES) return -1;
        
        return $this->haversineGreatCircleDistance(
            $main->location->lat, 
            $main->location->long, 
            $candidate->location->lat, 
            $candidate->location->long
        );
    }



    /**
     * Calculates if user Contact send hello to another Contact
     * 
     * @param ContactOwner $owner
     * @param ContactOwner $candidate
     * @param int $distance
     */
    public function canSendHello($owner, $candidate, $distance){

        if($distance < 0) return false; 

        if( $distance > self::MAX_DISTANCE_IN_METERS ) return false;

        $greeting = $owner->greetings->where('to', $candidate->msisdn)->first();
        
        if(!$greeting) return true;

        return Carbon::parse($greeting->updated_at)->diffInSeconds(now()) > self::MIN_TIMEDIFFERENCE_IN_SECONDS;        
    }


}