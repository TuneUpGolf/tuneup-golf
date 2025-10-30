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
            'mailable' => 'App\\Mail\\Admin\\PreSetScheduleMail',
            'subject' => 'Reminder for {{lesson}}',
            'html_template' => '
                <p>Hello, this is to notify you that you’ve been registered into the following event {{name}}.</p>
                <p>Hello This is the Lesson Description that the instructor has entered. <strong>{{lesson}}</strong> on 
                <strong>Lesson Date:</strong> {{date}} at <strong>{{time}}</strong>.</p>
                <strong>Lesson Description::</strong> {{description}}.</p>
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
            ->where('mailable', 'App\\Mail\\Admin\\PreSetScheduleMail')
            ->delete();
    }
};
