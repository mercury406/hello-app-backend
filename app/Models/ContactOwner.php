<?php

namespace App\Models;

use App\Models\Location;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ContactOwner extends Model
{
    use HasFactory;

    protected $guarded = [];
    protected $table = "contact_owners";
    protected $primaryKey = "uid";
    protected $keyType = "string";

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function contacts()
    {
        return $this->hasMany(Contact::class, "owner_uid");
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function location() {
        return $this->hasOne(Location::class, "contact_id");
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function greetings() {
        return $this->hasMany(Greeting::class, 'from');
    }

    /**
     * Notifies about new friend connected to app
     * 
     * @return void
     */
    public function notifyAboutNewFriendConnected($contact){
        $this->newFriendNotification($this->fcm, $contact);
    }


    function newFriendNotification($token, $contact)
    {
        $path_to_firebase_cm = 'https://fcm.googleapis.com/fcm/send';
        $fields = array(
            'to' => $token,
            'data' => ['event' => 'newFriendConnected', "name" => "$contact->cName", "number" => "$contact->msisdn"]
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