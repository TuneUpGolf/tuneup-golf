<?php

namespace App\Mail\Base;

use Spatie\MailTemplates\TemplateMailable;
use Spatie\MailTemplates\Models\MailTemplate;

abstract class InstructorTemplateMailable extends TemplateMailable
{
    protected $customTemplate;
    protected $instructorId = null;

    /**
     * Attach a specific instructor's template.
     */
    public function useMailTemplate(MailTemplate $template)
    {
        $this->customTemplate = $template;
        $this->instructorId = $template->instructor_id;
        return $this;
    }

    /**
     * Override how Spatie resolves which template to use.
     */
    protected function resolveTemplateModel(): MailTemplate
    {
        // If a specific template was injected, use that.
        if ($this->customTemplate) {
            return $this->mailTemplate = $this->customTemplate;
        }

        // Otherwise, fallback to instructor-aware search
        $query = MailTemplate::where('mailable', get_class($this));

        if ($this->instructorId) {
            $template = (clone $query)
                ->where('instructor_id', $this->instructorId)
                ->first();

            if ($template) {
                return $this->mailTemplate = $template;
            }
        }

        // Fallback to global
        return $this->mailTemplate = $query->whereNull('instructor_id')->first();
    }
}
