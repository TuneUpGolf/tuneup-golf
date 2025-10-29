<?php

namespace App\Mail\Admin;

use Spatie\MailTemplates\TemplateMailable;
use App\Mail\Base\InstructorTemplateMailable;

class TestMail extends InstructorTemplateMailable
{
    public function __construct()
    {
    }

    public function getHtmlLayout(): string
    {
        return view('mails.layout')->render();
    }
}
