<?php

namespace App\Http\Actions;

class ErrorMessage
{
    /*
     *@var message
     *
     * @return array
     */
    
    public function __construct($message)
    {
        $this->message = $message;
    }


    public function __invoke()
    {
        return ["status" => "error", "message" => $this->messagemessage];
    }
}
