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
                <p><strong>{{name}}<strong> this is to notify you that you’ve been registered into the following event</p>
                <p><strong>Lesson Description::</strong> {{description}}.</p>
                  <h3>Your Scheduled Slots:</h3>
                    @foreach($slots as $slot)
                        <div style="margin-bottom: 15px; padding: 10px; border: 1px solid #ddd;">
                            <p><strong>Date:</strong> {{ $slot["date"] }}</p>
                            <p><strong>Time:</strong> {{ $slot["time"] }}</p>
                            <p><strong>Location:</strong> {{ $slot["location"] }}</p>
                        </div>
                    @endforeach
                @if($notes)
                <p><strong>Additional Notes:</strong> {{ $notes }}</p>
                @endif

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
