<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Facades\UtilityFacades;
use App\Models\Plan;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Mail\Admin\ConatctMail;
use App\Models\AlbumCategory;
use App\Models\Posts;
use Spatie\MailTemplates\Models\MailTemplate;
use App\Models\Faq;
use App\Models\FooterSetting;
use App\Models\Instructor;
use App\Models\NotificationsSetting;
use App\Models\Role;
use App\Models\Testimonial;
use App\Notifications\Admin\ConatctNotification;
use Illuminate\Support\Facades\Cookie;
use App\Models\Lesson;

class LandingController extends Controller
{

    public function landingPage()
    {

        $centralDomain = config('tenancy.central_domains')[0];
        $currentDomain = tenant('domains');
        if (!empty($currentDomain)) {
            $currentDomain = $currentDomain->pluck('domain')->toArray()[0];
        }
        if ($currentDomain == null) {
            if (!file_exists(storage_path() . "/installed")) {
                header('location:install');
                die;
            }
            $lang   = UtilityFacades::getActiveLanguage();
            \App::setLocale($lang);
            $plans  = Plan::where('active_status', 1)->get();
            return view('welcome-admin', compact('plans', 'lang'));
        } else {
            $lang = UtilityFacades::getActiveLanguage();
            \App::setLocale($lang);


            $instructors = User::with(['lessons' => function ($q) {
                $q->with('packages')
                    ->where('active_status', 1)
                    ->select('lessons.id as id', 'lesson_name', 'type', 'lesson_price', 'created_by', 'required_time', 'long_description', 'lesson_description', 'is_package_lesson', 'logo');
            }])
                ->where('type', Role::ROLE_INSTRUCTOR)
                ->where('tenant_id', tenant()->id)
                ->get();

            $admin = User::where('type', Role::ROLE_ADMIN)
                ->first();
            $albums = AlbumCategory::get();
            if (UtilityFacades::getsettings('landing_page_status') == '1') {
                $bio_heading = UtilityFacades::getsettings('bio_heading');
                $instructor_heading = UtilityFacades::getsettings('instructor_heading');
                // dd("test");
                return view('welcome', compact(
                    'lang',
                    'admin',
                    'bio_heading',
                    'instructors',
                    'instructor_heading',
                    'albums'
                ));
            } else {
                return redirect()->route('home');
            }
        }
    }


    public function details(Request $request)
    {
        $instructor = User::with(['lessons' => function ($q) {
            $q->select('id', 'lesson_name', 'lesson_price', 'created_by');
        }])->find($request->id);

        if (!$instructor) {
            return response()->json(['error' => 'Instructor not found'], 404);
        }

        return response()->json([
            'name'   => $instructor->name,
            'image'  => $instructor->profile_image ?? asset('default.png'),
            'bio'    => $instructor->bio,
            'lessons' => $instructor->lessons->map(function ($lesson) {
                return [
                    'lesson_name'  => $lesson->lesson_name,
                    'lesson_price' => $lesson->lesson_price,
                ];
            }),
        ]);
    }



    public function getCategoryPost(Request $request)
    {
        $post       = Posts::where('category_id', $request->category)->get();
        return response()->json($post, 200);
    }

    public function postDetails($slug, Request $request)
    {
        $post           = Posts::where('slug', $slug)->first();
        $randomPosts    = Posts::where('slug', '!=', $slug)->limit(3)->get();
        return view('admin.posts.details', compact('post', 'randomPosts'));
    }

    public function contactUs()
    {
        $lang       = UtilityFacades::getActiveLanguage();
        \App::setLocale($lang);
        return view('contactus', compact('lang'));
    }

    public function termsAndConditions()
    {
        $lang       = UtilityFacades::getActiveLanguage();
        \App::setLocale($lang);
        return view('terms-and-conditions', compact('lang'));
    }

    public function faqs()
    {
        $lang       = UtilityFacades::getActiveLanguage();
        \App::setLocale($lang);
        $faqs       = Faq::orderBy('order')->get();
        return view('faq', compact('lang', 'faqs'));
    }

    public function contactMail(Request $request)
    {
        if (UtilityFacades::getsettings('contact_us_recaptcha_status') == '1') {
            request()->validate([
                'g-recaptcha-response' => 'required',
            ]);
        }
        $user   = User::where('tenant_id', tenant('id'))->first();
        $notify = NotificationsSetting::where('title', 'New Enquiry Details')->first();
        if (UtilityFacades::getsettings('email_setting_enable') == 'on') {
            if (isset($notify)) {
                if ($notify->notify = '1') {
                    $user->notify(new ConatctNotification($request));
                }
            }
        }
        if (UtilityFacades::getsettings('email_setting_enable') == 'on'  && UtilityFacades::getsettings('contact_email') != '') {
            if (isset($notify)) {
                if ($notify->email_notification == '1') {
                    if (UtilityFacades::getsettings('email_setting_enable') == 'on' && UtilityFacades::getsettings('contact_email') != '') {
                        if (MailTemplate::where('mailable', ConatctMail::class)->first()) {
                            try {
                                Mail::to(UtilityFacades::getsettings('contact_email'))->send(new ConatctMail($request->all()));
                            } catch (\Exception $e) {
                                return redirect()->back()->with('errors', $e->getMessage());
                            }
                        }
                    }
                }
            }
        }
        return redirect()->back()->with('success', __('Enquiry details send successfully'));
    }

    public function changeLang($lang = '')
    {
        if ($lang == '') {
            $lang   = UtilityFacades::getActiveLanguage();
        }
        Cookie::queue('lang', $lang, 120);
        return redirect()->back()->with('success', __('Language successfully changed.'));
    }
}
