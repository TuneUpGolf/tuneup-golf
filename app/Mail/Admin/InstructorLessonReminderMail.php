<?php

namespace App\Mail\Admin;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Spatie\MailTemplates\TemplateMailable;
use App\Mail\Base\InstructorTemplateMailable;

class InstructorLessonReminderMail extends InstructorTemplateMailable
{
    use Queueable, SerializesModels;

    public $studentName;
    public $lessonName;
    public $date;
    public $time;
    public $instructorName;
    public $slot_location;

    public function __construct($studentName, $lessonName, $date, $time, $instructorName, $slot_location)
    {
        $this->studentName = $studentName;
        $this->lessonName = $lessonName;
        $this->date = $date;
        $this->time = $time;
        $this->instructorName = $instructorName;
        $this->slot_location = $slot_location;
    }

    public function getHtmlLayout(): string
    {
        return view('mails.layout')->render();
    }

    // public function build()
    // {
    //     return $this->subject('Lesson Reminder')
    //         ->view('emails.lesson_reminder');
    // }
}
