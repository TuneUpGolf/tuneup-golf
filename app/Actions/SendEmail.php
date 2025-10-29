<?php

namespace App\Actions;

use Error;
use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Lorisleiva\Actions\Concerns\AsAction;
use Spatie\MailTemplates\Models\MailTemplate;

class SendEmail
{
    use AsAction;

    public function handle($email, Mailable $mailable, $instructor_id = null)
    {
        Log::info($instructor_id);
        try {
            $emailEnabled = \App\Facades\UtilityFacades::getsettings('enable_email_notification') === "on";

            if (! $emailEnabled) {
                return;
            }

            $mailableClass = get_class($mailable);
            Log::info($mailableClass);
            // Try instructor-specific template first (if instructor_id provided)
            $query = MailTemplate::where('mailable', $mailableClass);

            if ($instructor_id) {
                $template = (clone $query)
                    ->where('instructor_id', $instructor_id)
                    ->first();

                // Fallback to global if instructor doesn't have one
                if (! $template) {
                    $template = (clone $query)
                        ->whereNull('instructor_id')
                        ->first();
                }
            } else {
                // No instructor context — use global
                $template = $query->whereNull('instructor_id')->first();
            }
            Log::info($template);
            // If any matching template exists, send the email
            if ($template) {
                // 👇 Tell Spatie which template to use explicitly
                // if (method_exists($mailable, 'useMailTemplate')) {
                    $mailable->useMailTemplate($template);
                // }

                Mail::mailer('smtp')->to($email)->send($mailable);
            }
        } catch (Error $e) {
            report($e);
            Log::error($e);
            return response($e->getMessage());
        } catch (\Throwable $th) {
            report($th);
            Log::error($th);
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }

    // public function handle($email, Mailable $mailable)
    // {
    //     try {

    //         $emailEnabled = \App\Facades\UtilityFacades::getsettings('enable_email_notification') == "on";
    //         if (MailTemplate::where('mailable', get_class($mailable))->first() && $emailEnabled) {
    //             Mail::mailer('smtp')->to($email)->send($mailable);   
    //         }
    //     } catch (Error $e) {
    //         report($e);
    //         Log::error($e);
    //         return response($e->getMessage());
    //     } catch (\Throwable $th) {
    //         report($th);
    //         Log::error($th);

    //         return response()->json([
    //             'status' => false,
    //             'message' => $th->getMessage()
    //         ], 500);
    //     }
    // }
}
