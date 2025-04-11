<?php

namespace App\Mail;

namespace App\Mail\Admin;


use Spatie\MailTemplates\TemplateMailable;

class StudentPaymentLink extends TemplateMailable
{

    public $name;
    public $link;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($purchase, $link)
    {
        //
        $this->name = $purchase->student->name;
        $this->link = $link;
    }

    public function getHtmlLayout(): string
    {
        return view('mails.layout', ['data' => $this->name, $this->link])->render();
    }
}
