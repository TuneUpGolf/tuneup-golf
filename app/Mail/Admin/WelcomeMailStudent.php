<?php

namespace App\Mail;

namespace App\Mail\Admin;


use Spatie\MailTemplates\TemplateMailable;

class WelcomeMailStudent extends TemplateMailable
{

    public $name;
    public $link;
    public $password;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($user, $password)
    {
        //
        $this->name = $user->name;
        $this->link = route('login-student');
        $this->password = $password;
    }

    public function getHtmlLayout(): string
    {
        return view('mails.layout', ['data' => $this->name, $this->password, $this->link])->render();
    }
}
