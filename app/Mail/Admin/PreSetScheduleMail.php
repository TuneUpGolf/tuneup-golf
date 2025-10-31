<?php

namespace App\Mail\Admin;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\DB;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Mail\Base\InstructorTemplateMailable;

class PreSetScheduleMail extends InstructorTemplateMailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public $name;
    public $title;
    public $slots;
    // public $time;
    public $notes;
    public $description;
    public $formattedSlots;

    public function __construct($name,$title,$slots,$notes=null,$description=null)
    {
        $this->name = $name;
        $this->title = $title;
        $this->slots = $slots;
        // $this->time = $time;
        $this->notes = $notes;
        $this->description = strip_tags($description);
        $this->formattedSlots = $this->formatSlots($slots);
    }

    /**
     * Get the message envelope.
     *
     * @return \Illuminate\Mail\Mailables\Envelope
     */
    // public function build()
    // {
    //     return $this->html($this->buildView()); // Ensure HTML is sent
    // }

    // public function build()
    // {
    //     // Get template from database
    //     $template = DB::table('mail_templates')
    //         ->where('mailable', 'App\\Mail\\Admin\\PreSetScheduleMail')
    //         ->first();

    //     if ($template) {
    //         $htmlContent = $this->renderBladeTemplate($template->html_template, [
    //             'name' => $this->name,
    //             'slots' => $this->slots,
    //             'notes' => $this->notes,
    //             'description' => $this->description,
    //         ]);

    //         return $this->html($htmlContent);
    //     }

    //     return $this->view('emails.pre_set_schedule'); // fallback
    // }

   public function getTemplateVariables()
    {
        return [
            'name' => $this->name,
            'title' => $this->title,
            'slots' => $this->slots,
            'notes' => $this->notes,
            'description' => $this->description,
            'formattedSlots' => $this->formattedSlots,
        ];
    }

    private function formatSlots($slots)
    {
           $text = '';
                foreach ($slots as $slot) {
                    $text .= '
            Date: ' . $slot['date'] . '
            Time: ' . $slot['time'] . '
            Location: ' . $slot['location'] . '

            ';
                }
                return $text;

    }



    public function getHtmlLayout(): string
    {
        return view('mails.layout')->render();
    }

    /**
     * Get the message content definition.
     *
     * @return \Illuminate\Mail\Mailables\Content
     */
    // public function content()
    // {
    //     return new Content(
    //         view: 'view.name',
    //     );
    // }

    /**
     * Get the attachments for the message.
     *
     * @return array
     */
    // public function attachments()
    // {
    //     return [];
    // }
}
