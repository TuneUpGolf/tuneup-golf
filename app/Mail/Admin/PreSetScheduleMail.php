<?php

namespace App\Mail\Admin;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
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
    public $slots;
    // public $time;
    public $notes;
    public $description;

    public function __construct($name,$slots,$notes=null,$description=null)
    {
        $this->name = $name;
        $this->slots = $slots;
        // $this->time = $time;
        $this->notes = $notes;
        $this->description = $description;
    }

    /**
     * Get the message envelope.
     *
     * @return \Illuminate\Mail\Mailables\Envelope
     */
    public function build()
    {
        return $this->html($this->buildView()); // Ensure HTML is sent
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
