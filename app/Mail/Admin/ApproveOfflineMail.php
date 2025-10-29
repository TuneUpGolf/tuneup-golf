<?php

namespace App\Mail\Admin;

use App\Models\OfflineRequest;

use Spatie\MailTemplates\TemplateMailable;
use App\Mail\Base\InstructorTemplateMailable;

class ApproveOfflineMail extends InstructorTemplateMailable
{

    public $name;
    public $email;


    public function __construct(OfflineRequest $offline,$user)
    {
        $this->name = $user->name;
        $this->email = $offline->email;

    }

   public function getHtmlLayout(): string
    {
        return view('mails.layout')->render();
    }
}
