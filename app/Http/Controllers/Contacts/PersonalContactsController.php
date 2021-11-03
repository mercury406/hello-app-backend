<?php

namespace App\Http\Controllers\Contacts;

use Carbon\Carbon;
use App\Models\Contact;
use App\Models\ContactOwner;
use Illuminate\Http\Request;
use App\Models\PersonalContact;
use App\Http\Controllers\Controller;
use App\Http\Services\ContactService;

class PersonalContactsController extends Controller
{



    /**
     * The contact service instance.
     */
    protected $contact_service;

    /**
     * Create a new controller instance.
     * 
     * @param \App\Http\Services\ContactService
     * 
     */


    public function __construct(ContactService $contact_service) {
        $this->contact_service = $contact_service;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $from = $request->uid;
        $contacts = json_decode($request->contacts, true);
        

        if (!$contacts) return $this->contact_service->ErMessage("json error");
        
        $contacts = $this->contact_service->add_contacts($contacts, $from);
        
        // $owner = ContactOwner::find($from);
        // $mutual = $this->contact_service->add_contacts($contacts, $owner);
        // return ["status" => "ok", "contacts" => $mutual];
        return response()->json(['status' => 'created', "contacts" => $contacts], 201);
    }

    
}
