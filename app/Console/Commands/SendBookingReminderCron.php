<?php

namespace App\Console\Commands;

use Exception;
use Carbon\Carbon;
use App\Models\Slots;
use App\Models\Tenant;
use App\Actions\SendSMS;
use App\Actions\SendEmail;
use Illuminate\Support\Str;
use App\Facades\UtilityFacades;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use App\Actions\SendPushNotification;
use App\Mail\Admin\LessonReminderMail;
use Stancl\Tenancy\Concerns\HasATenantsOption;

class SendBookingReminderCron extends Command
{
    use HasATenantsOption;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'booking {--tenants=*}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send Booked Slots Reminder Prior to the session';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $tenants = $this->getTenants();

        if ($tenants->isEmpty()) {
            $this->error('No tenants found.');
            return;
        }

        tenancy()->runForMultiple(
            $tenants,
            function ($tenant) {
                $this->line("Tenant: {$tenant['id']}");
                $timezone = UtilityFacades::getValByName('default_timezone');

                $now = $timezone != '' ? Carbon::now($timezone) : Carbon::now();
                $oneHourLater = $now->copy()->addHour();

                Log::info($now);
                // Get slots within the next hour that haven't received a reminder
                $slots = Slots::whereHas('student')
                    ->whereBetween('date_time', [$now->format('Y-m-d H:i:s'), $oneHourLater->format('Y-m-d H:i:s')])
                    ->where('is_reminder_sent', 0)
                    ->get();



                // return ;

                try {
                    foreach ($slots as $slot) {
                        $slot->load('student'); // Load all students
                        $slot->load('lesson.user'); // Load lesson instructor

                        $date = Carbon::createFromFormat('Y-m-d H:i:s', $slot->date_time);
                        $lessonName = $slot->lesson?->lesson_name;
                        $instructor = $slot->lesson?->user;
                        $instructorName = $instructor?->name;

                        foreach ($slot->student as $student) {
                            $studentName = $student->pivot->friend_name ?? $student->name; // Show friend name if available
                            $messageStudent = __(
                                "Hey {$studentName} Reminder! You have an upcoming booking for {$date->toDayDateTimeString()} with {$instructorName} for the in-person lesson {$lessonName}."
                            );

                            // Send Push Notification to Student
                            if (isset($student->pushToken?->token)) {
                                SendPushNotification::dispatch($student->pushToken->token, 'Lesson Reminder', $messageStudent);
                            }

                            // Send SMS to Student
                            $studentPhone = Str::of($student->dial_code)->append($student->phone)->value();
                            $studentPhone = str_replace(['(', ')'], '', $studentPhone);
                            SendSMS::dispatch($studentPhone, $messageStudent);

                            Log::info($student->email);
                            if (!empty($student->email)) {

                                SendEmail::dispatch(
                                    $student->email,
                                    new LessonReminderMail(
                                        $studentName,
                                        $lessonName,
                                        $date->format('Y-m-d'),
                                        $date->format('h:i A'),
                                        $instructorName
                                    ),
                                    $instructor->id
                                );
                            }
                        }

                        // Notify Instructor
                        if ($instructor) {
                            $studentNames = $slot->student->pluck('name')->join(', ');
                            $messageInstructor = __(
                                "Reminder! You have an upcoming booking for {$date->toDayDateTimeString()} with students: {$studentNames} for the in-person lesson {$lessonName}."
                            );

                            // Send Push Notification to Instructor
                            if (isset($instructor->pushToken?->token)) {
                                SendPushNotification::dispatch($instructor->pushToken->token, 'Lesson Reminder', $messageInstructor);
                            }

                            // Send SMS to Instructor
                            $instructorPhone = Str::of($instructor->dial_code)->append($instructor->phone)->value();
                            $instructorPhone = str_replace(['(', ')'], '', $instructorPhone);
                            SendSMS::dispatch($instructorPhone, $messageInstructor);

                            if (!empty($instructor->email)) {
                                SendEmail::dispatch(
                                    $instructor->email,
                                    new LessonReminderMail(
                                        $instructorName,
                                        $lessonName,
                                        $date->format('Y-m-d'),
                                        $date->format('h:i A'),
                                        $instructorName
                                    ),
                                    $instructor->id
                                );
                            }
                        }
                    }
                    Slots::whereHas('student')
                        ->whereBetween('date_time', [$now->format('Y-m-d H:i:s'), $oneHourLater->format('Y-m-d H:i:s')])
                        ->update(['is_reminder_sent' => 1]);
                } catch (\Exception $e) {
                    return throw new Exception($e->getMessage(), $e->getCode());
                }
            }
        );

        return Command::SUCCESS;
    }
}
