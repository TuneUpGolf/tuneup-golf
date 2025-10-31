<?php

namespace App\Mail\Admin;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Mail\Base\InstructorTemplateMailable;

class SlotBookedByInstructorMail extends InstructorTemplateMailable
{
    public $student_name;
    public $date;
    public $time;
    public $notes;
    public $lesson_name;
    public $student_email;
    public $student_phone;
    public $instructor_name;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($student_name, $date, $time, $notes = null, $lesson_name, $student_email, $student_phone, $instructor_name)
    {
        $this->student_name = $student_name;
        $this->date = $date;
        $this->time = $time;
        $this->notes = $notes;
        $this->lesson_name = $lesson_name;
        $this->student_email = $student_email;
        $this->student_phone = $student_phone;
        $this->instructor_name = $instructor_name;

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
