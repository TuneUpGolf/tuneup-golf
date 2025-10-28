<?php

namespace App\Mail\Admin;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Spatie\MailTemplates\TemplateMailable;

class LessonReminderMail extends TemplateMailable
{
    use Queueable, SerializesModels;

    public $studentName;
    public $lessonName;
    public $date;
    public $time;
    public $instructorName;

    public function __construct($studentName, $lessonName, $date, $time, $instructorName)
    {
        $this->studentName = $studentName;
        $this->lessonName = $lessonName;
        $this->date = $date;
        $this->time = $time;
        $this->instructorName = $instructorName;
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
