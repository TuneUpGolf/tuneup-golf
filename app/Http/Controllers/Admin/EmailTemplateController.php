<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Spatie\MailTemplates\Models\MailTemplate;
use App\DataTables\Admin\EmailTemplateDataTable;

class EmailTemplateController extends Controller
{
    public function index(EmailTemplateDataTable $dataTable)
    {
        if (\Auth::user()->can('manage-email-template')) {
            return $dataTable->render('admin.email-template.index');
        } else {
            return redirect()->back()->with('failed', __('Permission denied.'));
        }
    }

    // public function edit($id)
    // {
    //     if (\Auth::user()->can('edit-email-template')) {
    //         $user = Auth::user();
    //         $mailTemplate   = MailTemplate::find($id);
    //         return view('admin.email-template.edit', compact('mailTemplate'));
    //     } else {
    //         return redirect()->back()->with('failed', __('Permission denied.'));
    //     }
    // }

    public function edit($id)
    {
        $user = Auth::user();

        if ($user->can('edit-email-template')) {
            $globalTemplate = MailTemplate::findOrFail($id);

            // If instructor, check for their personalized version
            if ($user->type == 'Instructor') {
                $mailTemplate = MailTemplate::where('mailable', $globalTemplate->mailable)
                    ->where('instructor_id', $user->id)
                    ->first();

                // If instructor-specific version doesn't exist, show global (read-only)
                if (!$mailTemplate) {
                    $mailTemplate = $globalTemplate;
                    $mailTemplate->is_global = true; // flag for view
                }
            } else {
                $mailTemplate = $globalTemplate;
            }

            return view('admin.email-template.edit', compact('mailTemplate'));
        }

        return redirect()->back()->with('failed', __('Permission denied.'));
    }

    public function update(Request $request, $id)
    {
        $user = Auth::user();

        if ($user->can('edit-email-template')) {
            $request->validate([
                'subject'       => 'required',
                'html_template' => 'required',
            ]);

            $globalTemplate = MailTemplate::findOrFail($id);

            // Instructor updating template
            if ($user->type == 'Instructor') {
                $existingInstructorTemplate = MailTemplate::where('mailable', $globalTemplate->mailable)
                    ->where('instructor_id', $user->id)
                    ->first();

                if ($existingInstructorTemplate) {
                    // Update their own copy
                    $existingInstructorTemplate->update([
                        'html_template' => $_POST['html_template'],
                    ]);
                } else {
                    // Create new copy for instructor
                    MailTemplate::create([
                        // 'name' => $globalTemplate->name,
                        'subject' => $request->subject,
                        // 'html_template' => $request->html_template,
                        'html_template' => $_POST['html_template'],
                        'text_template' => $request->text_template,
                        'mailable' => $globalTemplate->mailable,
                        'instructor_id' => $user->id,
                    ]);
                }
            } else {
                // Admin updating original
                $globalTemplate->update($request->only([
                    'html_template' => $_POST['html_template'],
                ]));
            }

            return redirect()->route('email-template.index')
                ->with('success', __('Email template updated successfully.'));
        }

        return redirect()->back()->with('failed', __('Permission denied.'));
    }



    // public function update(Request $request, $id)
    // {
    //     if (\Auth::user()->can('edit-email-template')) {
    //         request()->validate([
    //             'subject'       => 'required',
    //             'html_template' => 'required',
    //         ]);
    //         $mailTemplates  = $request->all();
    //         $mailTemplate   = MailTemplate::find($id);
    //         $mailTemplate->update($mailTemplates);
    //         return redirect()->route('email-template.index')->with('success', __('Email template updated successfully.'));
    //     } else {
    //         return redirect()->back()->with('failed', __('Permission denied.'));
    //     }
    // }
}
