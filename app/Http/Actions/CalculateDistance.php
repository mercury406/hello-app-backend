<?php

namespace App\Http\Actions;

use DateTime;
use App\Models\ContactOwner;

class CalculateDistance {

    const MAX_TIMEDIFFERENCE_IN_MINUTES = 15; // TimeDiff between Locations

    public function __construct(ContactOwner $main = null, ContactOwner $candidate = null)
    {
        $this->main = $main;
        $this->candidate = $candidate;
    }

    /**
     * @param ContactOwner $main
     * @param ContactOwner $candidate
     * 
     * @return int
     */
    public function __invoke()
    {
        if($this->main == null) return -1;
        if($this->candidate == null) return -1;
        if($this->main->location == null) return -1;
        if($this->candidate->location == null) return -1;
        if($this->main->location->updated_at == null) return -1;
        if($this->candidate->location->updated_at == null) return -1;

        // if($main == null || $candidate == null || $main->location == null || $candidate->location == null || $main->location->updated_at == null || $candidate->location->updated_at == null) return -1;

        $time_of_main = new DateTime($this->main->location->updated_at);
        $time_of_candidate = new DateTime($this->candidate->location->updated_at);

        $difference = $time_of_main->diff($time_of_candidate);

        if($difference->format("%h") > 0 || $difference->format("%i") > self::MAX_TIMEDIFFERENCE_IN_MINUTES) return -1;
        
        return $this->haversineGreatCircleDistance(
            $this->main->location->lat, 
            $this->main->location->long, 
            $this->candidate->location->lat, 
            $this->candidate->location->long
        );
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



}