<?php

namespace App\Mail\Admin;

use Spatie\MailTemplates\TemplateMailable;
use App\Mail\Base\InstructorTemplateMailable;

class SlotCancelledMail extends InstructorTemplateMailable
{

    public $name;
    public $date;
    public $time;
    public $lesson;
    public $notes;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($name, $date, $time, $lesson, $notes=null)
    {
        $this->name = $name;
        $this->date = $date;
        $this->time = $time;
        $this->lesson = $lesson;
        $this->notes = $notes;
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
