<?php

namespace App\Http\Controllers\Contacts;

use Illuminate\Http\Request;
use App\Http\Actions\AddContacts;
use App\Http\Actions\ErrorMessage;
use App\Http\Controllers\Controller;

class PersonalContactsController extends Controller
{

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
        if (!$contacts) return new ErrorMessage("json error");
        
        $contacts = new AddContacts($contacts, $from);
        return response()->json(['status' => 'created', "contacts" => $contacts], 201);
    }

    
}
