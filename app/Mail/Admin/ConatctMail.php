<?php

namespace App\Mail\Admin;


use Spatie\MailTemplates\TemplateMailable;
use App\Mail\Base\InstructorTemplateMailable;

class ConatctMail extends InstructorTemplateMailable
{

    public $email;
    public $name;
    public $contact_no;
    public $message;


    public function __construct($details)
    {
        $this->name = $details['name'];
        $this->email = $details['email'];
        $this->contact_no = $details['contact_no'];
        $this->message = $details['message'];
    }

   public function getHtmlLayout(): string
    {
        return view('mails.layout')->render();
    }
}

