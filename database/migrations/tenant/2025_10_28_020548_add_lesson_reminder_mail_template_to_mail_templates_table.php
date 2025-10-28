<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

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
            'mailable' => 'App\\Mail\\Admin\\LessonReminderMail',
            'subject' => 'Reminder for {{lesson}}',
            'html_template' => '
                <p>Hello, {{name}}.</p>
                <p>Hello {{name}}, this is a reminder that you have a lesson scheduled with <strong>{{lesson}}</strong> on 
                <strong>Date:</strong> {{date}} at <strong>{{time}}</strong>.</p>
            ',
            'text_template' => 'Hello {{name}}, this is a reminder that you have a lesson scheduled with {{lesson}} on Date: {{date}} at {{time}}.',
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
            ->where('mailable', 'App\\Mail\\Admin\\LessonReminderMail')
            ->delete();
    }
};
