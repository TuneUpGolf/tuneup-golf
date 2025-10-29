<?php

namespace App\Mail\Admin;

use App\Models\OfflineRequest;


use Spatie\MailTemplates\TemplateMailable;
use App\Mail\Base\InstructorTemplateMailable;

class OfflineMail extends InstructorTemplateMailable
{
   
    public $disapprove_reason;
    public $email;
    public $name;


    public function __construct(OfflineRequest $offlinerequest ,$user)
    {
        $this->disapprove_reason = $offlinerequest->disapprove_reason;
        $this->email = $offlinerequest->email;
        $this->name = $user->name;

    }

   public function getHtmlLayout(): string
    {
        return view('mails.layout')->render();
    }
}

