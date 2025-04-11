<?php

namespace App\Mail;

namespace App\Mail\Admin;


use Spatie\MailTemplates\TemplateMailable;

class WelcomeMail extends TemplateMailable
{

    public $name;
    public $link;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($username)
    {
        //
        $this->name = $username;
        $this->link = route('login');
    }

    public function getHtmlLayout(): string
    {
        return view('mails.layout', ['data' => $this->name, $this->link])->render();
    }
}
