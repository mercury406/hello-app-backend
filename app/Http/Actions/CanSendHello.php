<?php

namespace App\Http\Actions;

use Carbon\Carbon;

class CanSendHello{

    const MAX_TIMEDIFFERENCE_IN_MINUTES = 15; // TimeDiff between Locations

    const MAX_DISTANCE_IN_METERS = 25;
    const MIN_TIMEDIFFERENCE_IN_SECONDS = 12 * 60 * 60; // TimeDiff between Greetings

    
    /**
    * Calculates if user Contact send hello to another Contact
    * 
    * @param ContactOwner $owner
    * @param ContactOwner $candidate
    * @param int $distance
    */
    public function __construct($owner, $candidate, $distance)
    {
        $this->owner = $owner;
        $this->candidate = $candidate;
        $this->distance = $distance;
    }

    public function __invoke()
    {
        if($this->distance < 0) return false; 

        if( $this->distance > self::MAX_DISTANCE_IN_METERS ) return false;
     
        $greeting = $this->owner->greetings->where('to', $this->candidate->msisdn)->first();
        
        if(!$greeting) return true;
     
        return Carbon::parse($greeting->updated_at)->diffInSeconds(now()) > self::MIN_TIMEDIFFERENCE_IN_SECONDS;       
    }

}