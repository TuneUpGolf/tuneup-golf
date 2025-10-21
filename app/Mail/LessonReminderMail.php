<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class LessonReminderMail extends Mailable
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

    public function build()
    {
        return $this->subject('Lesson Reminder')
            ->view('emails.lesson_reminder');
    }
}
