<?php

namespace App\Mail\Admin;

use Spatie\MailTemplates\TemplateMailable;
use App\Mail\Base\InstructorTemplateMailable;

class RegisterMail extends InstructorTemplateMailable
{

    public $name;
    public $email;


    public function __construct($data)
    {
        $this->name = $data['name'];
        $this->email = $data['email'];
    }

   public function getHtmlLayout(): string
    {
        return view('mails.layout')->render();
    }
}
