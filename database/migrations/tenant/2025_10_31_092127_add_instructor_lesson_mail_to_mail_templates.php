<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('mail_templates')->insert([
            'mailable' => 'App\\Mail\\Admin\\InstructorLessonReminderMail',
            'subject' => 'Reminder for {{ lessonName }}',
            'html_template' => '
                <p><strong>{{ name }}</strong> this is to notify you that you\'ve been registered into the following event</p>
                <strong>Lesson Description:</strong> {{ description }}.
                <strong>Your Scheduled Slots:</strong>
                {{ formattedSlots }}
                    <p><strong>Additional Notes:</strong> {{ notes }}</p>
            ',
            'text_template' => 'Hello {{ name }}, this is a reminder that you have a lesson scheduled.',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
         DB::table('mail_templates')
            ->where('mailable', 'App\\Mail\\Admin\\InstructorLessonReminderMail')
            ->delete();
    }
};
