<?php

namespace App\Http\Actions;

use App\Models\Contact;
use App\Models\ContactOwner;

class AddContacts{

    /**
     * 
     * @param array $contacts
     * @param string $from
     * 
     * @return array
     * 
     */

    public function __construct($contacts, $from)   {
        $this->contacts = $contacts;
        $this->from = $from;
    }

    public function __invoke()
    {
        $owner = ContactOwner::find($this->from);
        $owners = ContactOwner::lazy();
        
        if(!$owner) return;

        $mutualContacts = [];

        foreach ((array) $this->contacts as $c) {
            $final_contact = preg_replace("/\s+/", "", $c["msisdn"]);
            $final_contact = str_replace("-", "", $final_contact);
            $added_contact = Contact::updateOrCreate(
                ['owner_uid' => $this->from, 'msisdn' => $final_contact], 
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

}