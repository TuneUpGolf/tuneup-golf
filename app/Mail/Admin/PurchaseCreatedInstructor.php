<?php

namespace App\Mail;

namespace App\Mail\Admin;

use App\Models\Purchase;
use Spatie\MailTemplates\TemplateMailable;

class PurchaseCreatedInsructor extends TemplateMailable
{

    public $name;
    public $amount;
    public $lesson;
    public $studentName;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(Purchase $purchase)
    {
        //
        $this->name = $purchase->instructor->name;
        $this->studentName = $purchase->student->name;
        $this->lesson = $purchase->lesson->id;
        $this->amount = $purchase->total_amount;
    }

    public function getHtmlLayout(): string
    {
        return view('mails.layout', ['data' => [$this->name, $this->amount, $this->lesson, $this->studentName]])->render();
    }
}
