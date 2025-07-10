<?php

namespace App\Mail\Admin;

use Spatie\MailTemplates\TemplateMailable;

class SlotBookedByStudentMail extends TemplateMailable
{

    public $name;
    public $date;
    public $time;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($name, $date, $time)
    {
        $this->name = $name;
        $this->date = $date;
        $this->time = $time;
    }

    public function build()
    {
        return $this->html($this->buildView()); // Ensure HTML is sent
    }

    public function getHtmlLayout(): string
    {
        return view('mails.layout')->render();
    }
}
