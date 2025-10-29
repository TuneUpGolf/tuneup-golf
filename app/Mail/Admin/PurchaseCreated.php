<?php

namespace App\Mail;

namespace App\Mail\Admin;

use App\Models\Purchase;
use Spatie\MailTemplates\TemplateMailable;
use App\Mail\Base\InstructorTemplateMailable;
use Spatie\MailTemplates\Models\MailTemplate;

class PurchaseCreated extends InstructorTemplateMailable
{

    public $name;
    public $amount;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(Purchase $purchase)
    {
        //
        $this->name = $purchase->student->name;
        $this->amount = $purchase->total_amount;
    }

    public function build()
    {
        return $this->html($this->buildView()); // Ensure HTML is sent
    }


    public function getHtmlLayout(): string
    {
        return view('mails.layout')->render();
    }
}
