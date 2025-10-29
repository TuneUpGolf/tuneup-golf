<?php

namespace App\Mail\Admin;

use Spatie\MailTemplates\TemplateMailable;
use App\Mail\Base\InstructorTemplateMailable;

class SupportTicketMail extends InstructorTemplateMailable
{
    public $name;
    public $email;
    public $ticket_status;
    public $subject;
    public $description;
    public $ticket_id;


    public function __construct($order)
    {
        $this->name = $order['name'];
        $this->email = $order['email'];
        $this->subject = $order['subject'];
        $this->ticket_status = $order['ticket'];
        $this->description = $order['description'];
        $this->ticket_id = $order['ticket_id'];
    }

    public function getHtmlLayout(): string
    {
        return view('mails.layout')->render();
    }
}
