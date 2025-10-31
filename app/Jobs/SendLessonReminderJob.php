<?php

namespace App\Jobs;

use App\Models\Slots;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use App\Mail\LessonReminderMail;

class SendLessonReminderJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $slot;

    public function __construct(Slots $slot)
    {
        $this->slot = $slot;
    }

    public function handle()
    {
        // Reload fresh copy with relations
        $slot   = Slots::with(['lesson.user', 'student'])->find($this->slot->id);

        if (! $slot || ! $slot->lesson) {
            return;
        }

        $lesson         = $slot->lesson;
        $instructorName = $lesson->user->name ?? 'Instructor';
        $lessonName     = $lesson->title;
        $date           = \Carbon\Carbon::parse($slot->date_time)->format('Y-m-d');
        $time           = \Carbon\Carbon::parse($slot->date_time)->format('h:i A');

        // Send to all booked students
        foreach ($slot->student as $student) {
            Mail::to($student->email)->send(
                new LessonReminderMail(
                    $student->name,
                    $lessonName,
                    $date,
                    $time,
                    $instructorName,
                    $slot->location
                )
            );
        }
    }
}

