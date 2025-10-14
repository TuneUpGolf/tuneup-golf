@php
    $languages = \App\Facades\UtilityFacades::languages();
    $currency = tenancy()->central(function ($tenant) {
        return Utility::getsettings('currency_symbol');
    });
    $currencySymbol = tenancy()->central(function ($tenant) {
        return Utility::getsettings('currency');
    });
    $tenantId = tenant('id');
    $banner = Utility::getsettings('banner_image');
@endphp

@extends('layouts.main-landing')
@section('content')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.9.0/slick.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.9.0/slick-theme.css" />
    <link rel="stylesheet" href="{{ asset('vendor/tailwind.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/css/customizer.css') }}">

    <style>
        .cancel-btn {
            background-color: #ff3a6e !important;
            transition: color 0.2s ease !important;
        }

        .cancel-btn:hover {
            background-color: #d9315c !important;
        }

        .lesson-btn:disabled {
            background: rgba(0, 113, 206, 0.5);
            /* faded version */
            cursor: not-allowed;
            opacity: 0.6;
        }
        #longDescModal {
            z-index: 1082 !important;
        }
    </style>

    <header>
        <nav class="navbar navbar-expand-lg navbar-dark bg-white px-0 py-3">
            <div class="container ctm-container">
                <a class="navbar-brand" href="/">
                    <img src="{{ asset('assets/images/landing-page-images/logo-1.png') }}" class="h-8" alt="...">
                </a>
                <button class="request-text border-0 rounded-pill demo px-4 py-2 bg-primary"
                    style="background-color: #0071ce !important;">
                    <a class="text-white font-bold" href="{{ route('login') }}" style="text-decoration: none">
                        Login/Signup</a>
                </button>
            </div>
        </nav>
    </header>

    <section class="landing-hero">
        <div class="hero-sec">
            <img class="w-full" src="{{ $banner != '' ? $banner : asset('assets/images/landing-page-images/banner1.png') }}"
                alt="hero-banner">
        </div>
    </section>
    <section class="lession-sec">
        <div class="container ctm-container py-2">
            <h2 class="font-bold text-4xl mb-2">{{ !empty($bio_heading) ? $bio_heading : $admin->name }}</h2>
            <p class="text-xl text-gray-600">{{ $admin->bio }}</p>
        </div>
    </section>


    @if (!$instructors->isEmpty())
        @if (count($instructors) == 1)
            <section class="container">

                <div class="row g-4 py-4">
                    <h3 style="font-size:22px;font-weight:bold;text-align:center">{{ $instructor_heading ?? '' }}</h3>
                    @forelse($instructors[0]->lessons as $lesson)
                        @if ($lesson->is_package_lesson == 0)
                            <div class="col-md-4">
                                <div class="bg-gray rounded-lg shadow flex flex-col h-full w-full">
                                    <div class="relative text-center p-3 flex gap-3">
                                        {{-- @dd(\Schema::getColumnListing('lessons')); --}}
                                        {{-- @dd($lesson, $instructors[0]) --}}
                                        <img src="{{ $lesson->logo != null ? asset('storage/' . tenant()->id . '/' . $lesson->logo) : asset('/storage/' . tenant('id') . '/' . $lesson->user->avatar) }}"
                                            alt="{{ $instructors[0]->name }}"
                                            class="hover:shadow-lg cursor-pointer rounded-lg h-32 w-24 object-cover">
                                        <div class="text-left">
                                            <a class="font-bold text-dark text-xl" href="{{ route('login') }}"
                                                tabindex="0">
                                                {{ $instructors[0]->name }}
                                            </a>
                                            <div class="text-lg font-bold tracking-tight text-primary">
                                                ${{ $lesson?->lesson_price }}
                                            </div>
                                        </div>
                                    </div>

                                    @php
                                        $description = html_entity_decode($lesson->lesson_description);
                                        $cleanDescription = strip_tags(
                                            $description,
                                            '<ul><ol><li><span><a><strong><em><b><i>',
                                        );
                                        $cleanShortDescription = strip_tags($description, '<ul><ol><li><strong><b><i>');
                                        $shortDescription = \Illuminate\Support\Str::limit(
                                            $cleanShortDescription,
                                            80,
                                            '...',
                                        );
                                    @endphp
                                    <div class="text-gray-500 text-md px-2 mb-2">
                                        <h3 style="font-size:18px;font-weight:bold;margin-left:10px;"
                                            class="font-weight-bolder">
                                            {{ $lesson->lesson_name }}
                                        </h3>
                                    </div>
                                    {{-- <div class="text-gray-500 text-md description font-medium ctm-min-h px-3 mb-2"> --}}
                                        @if (!empty($description))
                                                <div class="hidden short-text text-gray-600">
                                                    {!! $description !!}
                                                </div>
                                            @endif
                                        {{-- <div title="{{ strip_tags($description) }}"  class="short-text text-gray-600" style="
                                                font-size: 15px;
                                                display: -webkit-box;
                                                -webkit-line-clamp: 2;
                                                -webkit-box-orient: vertical;
                                                overflow: hidden;
                                                text-overflow: ellipsis; ">
                                            {!! $description !!}
                                        </div> --}}
                                        {{--  @if (!empty($description) && strlen(strip_tags($description)) >= 20)
                                            <div class="hidden full-text text-gray-600"
                                                style="font-size: 15px; max-height: auto; overflow-y: auto;">
                                                {!! $cleanDescription !!}
                                            </div>
                                            <a href="javascript:void(0);" style="font-size: 15px"
                                                class="text-blue-600 toggle-read-more font-semibold"
                                                onclick="toggleDescription(this, event)">View Lesson Description</a>
                                        @endif  --}}
                                    {{-- </div> --}}
                                    <div class="px-3 mt-1 flex flex-col flex-grow">
                                        <div class="description-wrapper relative mb-[10px]">
                                            {{-- @if (!empty($lesson?->long_description) || !is_null($lesson?->long_description)) --}}
                                                <div class="hidden long-text text-gray-600"
                                                    style="font-size: 15px; max-height: 100px; overflow-y: auto;">
                                                    {!! $lesson->long_description !!}
                                                </div>
                                                <a href="javascript:void(0)"  style="font-size: 15px;margin-bottom:10px"
                                                    data-long_description="{{ e($lesson->long_description) }}"
                                                    class="text-blue-600 font-medium mt-1 inline-block viewDescription"
                                                    tabindex="0">View Description </a>
                                            {{-- @endif --}}
                                        </div>
                                        @if ($lesson?->type == 'online')
                                            <div class="p-3 h-full bg-gray-200 gap-2 rounded-lg flex justify-center items-center">
                                                <div class="text-center">
                                                    <span class="text-xl font-bold">{{ $lesson?->required_time }}
                                                        Days</span>
                                                    <div class="text-sm rtl:space-x-reverse">Expected Response Time</div>
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                    <div class="w-100 my-3 px-3">
                                        @php
                                            $button_text = 'Purchase';
                                            if ($lesson->type == 'package') {
                                                $button_text = 'Schedule Lesson';
                                            } elseif ($lesson->type == 'inPerson') {
                                                $button_text = 'Sign Up';
                                            }
                                        @endphp
                                        <a href="{{ route('login') }}" tabindex="0">
                                            <button type="submit" class="lesson-btn"
                                                tabindex="0">{{ $button_text }}</button>
                                        </a>
                                    </div>
                                </div>
                            </div>



                            {{-- Post Section End --}}
                        @else
                            @php
                                $slots = 0;
                                $slots += $lesson?->packages->sum('number_of_slot');
                                $packages_array = [];
                                foreach ($lesson?->packages as $package) {
                                    $packages_array[] =
                                        $package->number_of_slot .
                                        ' Pack ' .
                                        ' - ' .
                                        $currency .
                                        $package->price .
                                        ' ' .
                                        $currencySymbol;
                                }
                            @endphp
                            <div class="col-md-4">
                                <div
                                    class="w-full bg-gray border border-gray-200 rounded-lg shadow dark:bg-gray-800 dark:border-gray-700 flex flex-col h-full">
                                    <div class="relative text-center p-3 flex gap-3">

                                        <img src="{{ isset($lesson->logo) ? asset('storage/' . tenant()->id . '/' . $lesson->logo) : asset('/storage/' . tenant('id') . '/' . $lesson->user->avatar) }}"
                                            class="hover:shadow-lg cursor-pointer rounded-lg h-32 w-24 object-cover">
                                        <div class="text-left">
                                            <a class="font-bold text-dark text-xl" href="{{ route('login') }}">
                                                {{ $instructors[0]->name }}
                                            </a>
                                            <div class="text-lg font-bold tracking-tight text-primary">
                                                @if ($lesson?->packages->min('price'))
                                                    {{ $currency }}{{ $lesson?->packages->min('price') }}
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                    @php
                                        $description = html_entity_decode($lesson->lesson_description);
                                        $cleanDescription = strip_tags($description, '<ul><ol><li><span><a><em><b><i>');
                                        $cleanShortDescription = strip_tags($description, '<ul><ol><li><strong><b><i>');
                                        $shortDescription = \Illuminate\Support\Str::limit(
                                            $cleanShortDescription,
                                            80,
                                            '...',
                                        );
                                    @endphp
                                    <div class="text-gray-500 text-md px-2 mb-2">
                                        <h3 style="font-size:18px;font-weight:bold;margin-left:10px"
                                            class="font-weight-bolder">
                                            {{ $lesson->lesson_name }}</h3>
                                    </div>
                                    {{-- <div class="text-gray-500 text-md description font-medium ctm-min-h px-3 mb-2"> --}}
                                          @if (!empty($description))
                                                <div class="hidden short-text text-gray-600">
                                                    {!! $description !!}
                                                </div>
                                            @endif
                                        {{-- <div title="{{ strip_tags($description) }}"  class="short-text text-gray-600"
                                             style="
                                                font-size: 15px;
                                                display: -webkit-box;
                                                -webkit-line-clamp: 2;
                                                -webkit-box-orient: vertical;
                                                overflow: hidden;
                                                text-overflow: ellipsis; ">
                                            {!! $description !!}
                                        </div> --}}
                                        {{--  @if (!empty($description) && strlen($description) >= 20)
                                            <div class="hidden full-text text-gray-600"
                                                style="font-size: 15px; max-height: auto; overflow-y: auto;">
                                                {!! $cleanDescription !!}
                                            </div>
                                            <a href="javascript:void(0);" style="font-size: 15px"
                                                class="text-blue-600 toggle-read-more font-semibold"
                                                onclick="toggleDescription(this, event)">View Lesson Description</a>
                                        @endif  --}}
                                    {{-- </div> --}}

                                    <div class="px-3 flex flex-col flex-grow">

                                        @if (!empty($lesson?->long_description) || $lesson?->long_description != '' || $lesson?->long_description != null)
                                            <div class="hidden long-text text-gray-600"
                                                style="font-size: 15px; max-height: 100px; overflow-y: auto;">
                                                {!! $lesson->long_description !!}
                                            </div>
                                            <a href="javascript:void(0)" style="font-size: 15px;margin-bottom:10px"
                                                data-long_description="{!! $lesson?->long_description !!}"
                                                class=" text-blue-600 font-medium mt-1 inline-block viewDescription"
                                                tabindex="0"> View
                                                Description</a>
                                        @endif

                                        <div class="p-3 border rounded-lg shadow-sm bg-white">
                                            <h2 class="text-lg font-semibold flex items-center mb-2">
                                                <svg class="w-5 h-5 mr-2 text-gray-700" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-width="2"
                                                        d="M8 7V3m8 4V3m-9 8h10m-10 4h6m4 8H5a2 2 0 01-2-2V7a2 2 0 012-2h3l2-2h4l2 2h3a2 2 0 012 2v12a2 2 0 01-2 2z">
                                                    </path>
                                                </svg>
                                                Package Options Available
                                            </h2>
                                            {{-- <p class="text-sm text-gray-500 mb-2">Save more with multi-lesson packages</p> --}}
                                            <select name="package_slot"
                                                class="no-nice-select w-full border rounded-lg p-2 text-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                                                <option value="--">Select Option</option>
                                                @foreach ($packages_array as $package)
                                                    <option value="{{ $package }}">{{ $package }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="w-100 my-3 px-3">
                                          @php
                                        $button_text = 'Purchase';
                                        if ($lesson->type == 'package') {
                                            $button_text = 'Schedule Lesson';
                                        } elseif ($lesson->type == 'inPerson') {
                                            $button_text = 'Sign Up';
                                        }
                                    @endphp
                                        <a class="lesson-btn text-center" href="{{ route('login') }}">
                                            {{ $button_text }}
                                        </a>
                                    </div>
                                </div>
                            </div>
                        @endif
                    @empty
                        <h3>No Lessons Found</h3>
                    @endforelse
                </div>
            </section>

            {{-- Subscription Section --}}
            @if (!$instructors[0]->plans->isEmpty())
                <section class="lession-sec subscription-sec">
                    <div class="container ctm-container">
                        <h2 class="font-bold text-4xl mb-2">
                            Subscription Offerings
                        </h2>
                        <p class="text-xl text-gray-600">
                            Subscription plans give you full access to your coach's posts, training content, and
                            the ability to connect
                            directly.
                        </p>
                        @if (!$instructors[0]->plans->isEmpty())
                            <div class="row">
                                @foreach ($instructors[0]->plans as $plan)
                                    @if ($plan->active_status == 1)
                                        <div class="col-xl-3 col-md-6 py-4">
                                            <div class="card price-card price-1 wow animate__fadeInUp ani-fade m-0 h-100"
                                                data-wow-delay="0.2s">
                                                <div class="rounded-lg shadow popular-wrap h-100">
                                                    <div class="px-3 pt-4 ">
                                                        <p class="text-2xl font-bold mb-1">
                                                            {{ $plan->name }}
                                                        </p>

                                                        <span class="text-gray-600"><strong>Instructor:
                                                                {{ $plan->instructor->name }}</strong></span>
                                                        <br>
                                                        <span class="text-gray-600"><strong>Total Duration:
                                                                {{ $plan->duration . ' ' . $plan->durationtype }}
                                                            </strong></span>
                                                            <br>
                                                             <span class="text-gray-600"><strong>Online Lesson Limit:
                                                                {{ $plan->lesson_limit_label  }}
                                                            </strong></span>
                                                        <div class="flex gap-1 items-center mt-2 ">
                                                            <p class="text-4xl font-bold">
                                                                {{ '$' . $plan->price }}/</p>
                                                            <p class="text-2xl text-gray-600">
                                                                Month
                                                            </p>
                                                        </div>
                                                        
                                                    </div>
                                                    <div class="border-t border-gray-300"></div>
                                                    <div class="px-3 py-4">
                                                        @if ($plan->id != 1)
                                                            {{-- @if ($plan->id == $user->plan_id && !empty($user->plan_expired_date) && Carbon::parse($user->plan_expired_date)->gte(now()))
                                                                        <a href="javascript:void(0)"
                                                                            data-id="{{ $plan->id }}"
                                                                            class="lesson-btn text-center font-bold text-lg mt-auto"
                                                                            data-amount="{{ $plan->price }}">{{ __('Expire at') }}
                                                                            {{ Carbon::parse($user->plan_expired_date)->format('d/m/Y') }}</a>
                                                                    @else
                                                                        <a href="{{ route('payment', \Illuminate\Support\Facades\Crypt::encrypt($plan->id)) }}"
                                                                            class="lesson-btn text-center font-bold text-lg mt-auto">
                                                                            @if ($plan->id == $user->plan_id)
                                                                                {{ __('Renew') }}
                                                                            @else
                                                                                {{ __('Buy Plan') }}
                                                                            @endif
                                                                        </a>
                                                                    @endif --}}
                                                            {{-- @dd(auth('student')->user()) --}}
                                                            {{-- @if (auth('student')->user())
                                                            @if (auth('student')->user()->plan_id != null)
                                                                @if ($plan->id == auth('student')->user()->plan_id)
                                                                    @if (!empty(auth('student')->user()->plan_expired_date) && \Carbon\Carbon::parse(auth('student')->user()->plan_expired_date)->gte(now()))
                                                                        <a href="javascript:void(0)"
                                                                            data-id="{{ $plan->id }}"
                                                                            class="lesson-btn text-center font-bold text-lg mt-auto"
                                                                            data-amount="{{ $plan->price }}">{{ __('Expire at') }}
                                                                            {{ \Carbon\Carbon::parse(auth('student')->user()->plan_expired_date)->format('d/m/Y') }}</a>
                                                                        <a href="{{ route('plans.cancel', \Illuminate\Support\Facades\Crypt::encrypt($plan->id)) }}"
                                                                            class="lesson-btn text-center font-bold text-lg mt-2 cancel-btn">Cancel
                                                                            Plan</a>
                                                                    @else
                                                                        <a href="{{ route('payment', \Illuminate\Support\Facades\Crypt::encrypt($plan->id)) }}"
                                                                            class="lesson-btn text-center font-bold text-lg mt-auto">
                                                                            @if ($plan->id == auth('student')->user()->plan_id)
                                                                                {{ __('Renew') }}
                                                                            @else
                                                                                {{ __('Buy Plan') }}
                                                                            @endif
                                                                        </a>
                                                                    @endif
                                                                @else
                                                                  

                                                                    <button disabled
                                                                        class="lesson-btn text-center font-bold text-lg mt-auto">
                                                                        {{ __('Buy Plan') }}
                                                                    </button>
                                                                @endif
                                                            @else
                                                                <a href="{{ route('payment', \Illuminate\Support\Facades\Crypt::encrypt($plan->id)) }}"
                                                                    class="lesson-btn text-center font-bold text-lg mt-auto">
                                                                    @if ($plan->id == auth('student')->user()->plan_id)
                                                                        {{ __('Renew') }}
                                                                    @else
                                                                        {{ __('Buy Plan') }}
                                                                    @endif
                                                                </a>
                                                            @endif
                                                        @else
                                                            @if (auth('web')->user() || auth('instructors')->user())
                                                                <button disabled
                                                                    class="lesson-btn text-center font-bold text-lg mt-auto">
                                                                    {{ __('Buy Plan') }}
                                                                </button>
                                                            @else
                                                                <a href="{{ route('login') }}"
                                                                    class="lesson-btn text-center font-bold text-lg mt-auto">
                                                                    {{ __('Buy Plan') }}
                                                                </a>
                                                            @endif
                                                        @endif --}}

                                                            @php
                                                                $student = auth('student')->user();
                                                                $webUser = auth('web')->user();
                                                                $instructor = auth('instructors')->user();

                                                                $hasStudent = !is_null($student);
                                                                $hasPlan = $hasStudent && !is_null($student->plan_id);
                                                                $isCurrentPlan =
                                                                    $hasPlan && $plan->id == $student->plan_id;
                                                                $isActive =
                                                                    $isCurrentPlan &&
                                                                    !empty($student->plan_expired_date) &&
                                                                    \Carbon\Carbon::parse(
                                                                        $student->plan_expired_date,
                                                                    )->gte(now());
                                                            @endphp

                                                            @if ($hasStudent)
                                                                @if ($isCurrentPlan)
                                                                    @if ($isActive)
                                                                        {{-- ✅ Current active plan --}}
                                                                        <a href="javascript:void(0)"
                                                                            data-id="{{ $plan->id }}"
                                                                            class="lesson-btn text-center font-bold text-lg mt-auto"
                                                                            data-amount="{{ $plan->price }}">
                                                                            {{ __('Expire at') }}
                                                                            {{ \Carbon\Carbon::parse($student->plan_expired_date)->format('d/m/Y') }}
                                                                        </a>
                                                                        <a href="{{ route('plans.cancel', \Illuminate\Support\Facades\Crypt::encrypt($plan->id)) }}"
                                                                            class="lesson-btn text-center font-bold text-lg mt-2 cancel-btn">
                                                                            {{ __('Cancel Plan') }}
                                                                        </a>
                                                                    @else
                                                                        {{-- 🔁 Expired plan → Renew --}}
                                                                        <a href="{{ route('payment', \Illuminate\Support\Facades\Crypt::encrypt($plan->id)) }}"
                                                                            class="lesson-btn text-center font-bold text-lg mt-auto">
                                                                            {{ __('Renew') }}
                                                                        </a>
                                                                    @endif
                                                                @elseif ($hasPlan)
                                                                    {{-- 🚫 User has another plan --}}
                                                                    <button disabled
                                                                        class="lesson-btn text-center font-bold text-lg mt-auto">
                                                                        {{ __('Buy Plan') }}
                                                                    </button>
                                                                @else
                                                                    {{-- 🛒 No plan yet --}}
                                                                    <a href="{{ route('payment', \Illuminate\Support\Facades\Crypt::encrypt($plan->id)) }}"
                                                                        class="lesson-btn text-center font-bold text-lg mt-auto">
                                                                        {{ __('Buy Plan') }}
                                                                    </a>
                                                                @endif
                                                            @elseif ($webUser || $instructor)
                                                                {{-- 🚷 Logged in as non-student --}}
                                                                <button disabled
                                                                    class="lesson-btn text-center font-bold text-lg mt-auto">
                                                                    {{ __('Buy Plan') }}
                                                                </button>
                                                            @else
                                                                {{-- 🔐 Guest user --}}
                                                                <a href="{{ route('login') }}"
                                                                    class="lesson-btn text-center font-bold text-lg mt-auto">
                                                                    {{ __('Buy Plan') }}
                                                                </a>
                                                            @endif
                                                        @endif
                                                        <p class="font-semibold text-xl mb-2 mt-2">Includes:</p>
                                                        <p class="text-gray-600">
                                                            {!! $plan->description !!}
                                                        </p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                        @else
                            <h3>No Subscriptions Found</h3>
                        @endif
                    </div>
                </section>
            @endif
            {{-- End Subscription Section --}}


            {{-- Post Secton  --}}
            @if (!$instructors[0]->post->isEmpty())
                <section class="lession-sec feed-sec">
                    <div class="container ctm-container">
                        <h2 class="font-bold text-4xl mb-2">Coaches Corner</h2>

                        <div class="flex flex-wrap gap-5 w-100">
                            @each('admin.posts.blog', $instructors[0]->post, 'post')
                        </div>
                    </div>
                </section>
            @endif
        @else
            <div class="container ctm-container">
                <h2 class="font-bold text-4xl mb-2">{{ $instructor_heading ?? 'Instructors' }}</h2>
                <div class="flex flex-wrap gap-5 w-full my-10">
                    @foreach ($instructors as $instructor)
                        @php
                            $imgSrc =
                                $instructor->avatar == "storage/$tenantId/logo/app-favicon-logo.png"
                                    ? asset("storage/$tenantId/logo/app-favicon-logo.png")
                                    : asset('storage/' . $tenantId . '/' . $instructor->avatar);
                        @endphp
                        <div class="flex flex-col items-center text-center">
                            <a href="{{ url('/login') }}" title="{{ $instructor->name }}">
                                <img class="custom-instructor-avatar rounded" src="{{ $imgSrc }}"
                                    alt="Instructor Avatar">
                                <h1 class="text-xl font-bold truncate mt-2">{{ $instructor->name }}</h1>
                            </a>
                            <div class="py-2">
                                <button
                                    onclick='openInstructorPopup(
                                        @json($instructor->name),
                                        @json($imgSrc),
                                        @json($instructor->bio),
                                        @json(
                                            $instructor->lessons->map(fn($lesson) => [
                                                    'name' => $lesson->lesson_name,
                                                    'price' => $lesson->lesson_price,
                                                ])),
                                        @json($currency)
                                    )'
                                    class="read-more-btn text-blue-600 hover:text-blue-800 underline text-sm">
                                    View Bio
                                </button>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    @endif









    <!-- Instructor Popup Modal -->
    <div id="instructorPopup"
        class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-lg max-w-md w-full max-h-[90vh] overflow-y-auto overflow-x-hidden">
            <div class="relative">
                <!-- Close Button -->
                <button onclick="closeInstructorPopup()"
                    class="absolute bg-gray-900 flex font-bold h-8 items-center justify-center m-2 right-2 rounded-full shadow-md text-2xl top-2 w-8 z-10">
                    ×
                </button>

                <!-- Instructor Image -->
                <div class="w-full overflow-hidden rounded-t-lg">
                    <img id="popupInstructorImage" class="w-full h-auto max-h-64 object-contain" src=""
                        alt="Instructor">
                </div>

                <!-- Instructor Info -->
                <div class="p-6">
                    <h3 id="popupInstructorName" class="text-xl font-bold mb-3 text-gray-800"></h3>
                    <p id="popupInstructorBio" class="text-gray-600 leading-relaxed mb-4"></p>

                    <!-- Lessons Badges -->
                    <div id="popupInstructorLessons" class="flex flex-wrap gap-2"></div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal " id="longDescModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title font-bold" style="font-size: 20px">Description</h1>
                    <button type="button"
                        class="bg-gray-900 flex font-bold h-8 items-center justify-center m-2 right-2 rounded-full shadow-md text-2xl top-2 w-8 z-10"
                        onclick="closeLongDescModal()" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="longDescContent"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="lesson-btn" onclick="closeLongDescModal()">Close</button>
                </div>
            </div>
        </div>
    </div>

    <footer class="foot mt-0">
        <div class="text-center container ctm-container footer-one">
            <div class="flex justify-center">
                <img src="{{ asset('assets/images/landing-page-images/logo-1.png') }}" class="img-fluid"
                    alt="" />
            </div>
        </div>
    </footer>
    <footer class="foot-two">
        <div class="flex justify-content-sm-between justify-center align-items-center container footer-two">
            <div class="text-white m-0">
                <p class="fot-p">© 2025 Tuneup. All rights reserved.</p>
            </div>
            <div class="icon flex mt-2 sm-mt-0 text-3xl flex gap-3">
                @if ($socialUrlFb = Utility::getsettings('social_url_fb'))
                    <a href="{{ $socialUrlFb }}" class="text-gray-800"><i class="ti ti-brand-facebook"></i></a>
                @endif
                @if ($socialUrlX = Utility::getsettings('social_url_x'))
                    <a href="{{ $socialUrlX }}" class="text-gray-800"><i class="ti ti-brand-twitter"></i></a>
                @endif
                @if ($socialUrlIg = Utility::getsettings('social_url_ig'))
                    <a href="{{ $socialUrlIg }}" class="text-gray-800"><i class="ti ti-brand-instagram"></i></a>
                @endif
                @if ($socialUrlYt = Utility::getsettings('social_url_yt'))
                    <a href="{{ $socialUrlYt }}" class="text-gray-800"><i class="ti ti-brand-youtube"></i></a>
                @endif
                @if ($socialUrlLn = Utility::getsettings('social_url_ln'))
                    <a href="{{ $socialUrlLn }}" class="text-gray-800"><i class="ti ti-brand-linkedin"></i></a>
                @endif
            </div>
        </div>
    </footer>
@endsection

@push('css')
    <style>
        .lessions-slider .slick-track {
            display: flex !important;
        }

        .lessions-slider .slick-slide {
            height: inherit !important;
        }

        .read-more-btn {
            transition: all 0.3s ease;
        }

        .read-more-btn:hover {
            transform: translateY(-1px);
        }

        #instructorPopup {
            transition: opacity 0.3s ease;
        }

        #instructorPopup.show {
            opacity: 1;
        }

        .description ul,
        .description ol {
            list-style-type: disc;
            margin-left: 20px;
            padding-left: 20px;
        }

        .description li {
            display: list-item;
            margin-bottom: 5px;
        }

        .description b,
        .description strong {
            font-weight: bold;
        }

        .description i,
        .description em {
            font-style: italic;
        }

        .description {
            display: block !important;
        }

        .hidden {
            display: none;
        }

        .longDescContent ul {
            list-style: disc;
            padding-left: 1.5rem;
        }

        .longDescContent table {
            width: 100% !important;
        }

        .longDescContent table {
            width: 100%;
            border: 1px solid #000;
            border-collapse: collapse;
        }

        .longDescContent th,
        .longDescContent td {
            border: 1px solid #000;
            padding: 6px 10px;
            text-align: left;
        }

        .short-text ul {
            margin-bottom: 0px !important;
        }
    </style>
@endpush

@push('javascript')
    <script src="{{ asset('assets/js/jquery.min.js') }}"></script>

    <script>
        //function toggleDescription(button) {
        //    let parent = button.closest('.description');
        //    let shortText = parent.querySelector('.short-text');
        //    let fullText = parent.querySelector('.full-text');

        //    if (shortText.classList.contains('hidden')) {
        //        shortText.classList.remove('hidden');
        //        fullText.classList.add('hidden');
        //        button.innerText = "View Lesson Description";
        //    } else {
        //        shortText.classList.add('hidden');
        //        fullText.classList.remove('hidden');
        //        button.innerText = "Show Less";
        //    }
        //}

        function toggleDescription(button, event) {
            event.stopPropagation();
            let parent = button.closest('.description');
            let shortText = parent.querySelector('.short-text');
            let fullText = parent.querySelector('.full-text');

            parent.style.display = 'block';

            if (!shortText || !fullText) {
                console.error('Short text or full text element not found in .description', {
                    parent,
                    shortText,
                    fullText
                });
                return;
            }

            if (shortText.classList.contains('hidden')) {
                shortText.classList.remove('hidden');
                fullText.classList.add('hidden');
                button.innerText = "View Lesson Description";
            } else {
                shortText.classList.add('hidden');
                fullText.classList.remove('hidden');
                button.innerText = "Show Less";
            }
        }
      $(document).on('click', '.viewDescription', function () {
            const parent = $(this).closest('.bg-gray, .w-full, .shadow');
            const title = parent.find('h3').first().text() || 'Description';
            const shortDesc = parent.find('.short-text').html() || '';
            const longDesc = $(this).siblings('.long-text').html() || '';

            $('#longDescModal').find('.lesson-title').text(title);

            let modalContent = '';

            if (shortDesc) {
                modalContent += `
                    <div class="shortDescSection border-b pb-4">
                        <h3 class="text-lg font-medium mb-2 text-gray-900">
                            Short Description:
                        </h3>
                        <div class="shortDesc" style="font-size:15px; color:#555; line-height:1.6;">
                            ${shortDesc}
                        </div>
                    </div>
                `;
            }

            if (longDesc) {
                modalContent += `
                    <div class="longDescSection mt-4">
                        <h3 class="text-lg font-medium mb-2 text-gray-900">
                            Long Description:
                        </h3>
                        <div class="longDesc" style="font-size:15px; color:#333; line-height:1.6;">
                            ${longDesc}
                        </div>
                    </div>
                `;
            }

            // If both are empty, show fallback text
            if (!shortDesc && !longDesc) {
                modalContent = `
                    <p class="text-gray-600 italic">No description available for this lesson.</p>
                `;
            }

            $('#longDescModal').modal('show');
            $('.longDescContent').html(modalContent);
        });

        function closeLongDescModal() {
            $('#longDescModal').modal('hide');
        }

        function openInstructorPopup(name, imageSrc, bio, lessons = [], currency = '') {
            console.log("Popup clicked:", name);

            // Fill content
            document.getElementById('popupInstructorName').textContent = name;
            document.getElementById('popupInstructorImage').src = imageSrc;
            document.getElementById('popupInstructorImage').alt = name;
            document.getElementById('popupInstructorBio').textContent = bio;

            // Render lessons
            {{--  const lessonsContainer = document.getElementById('popupInstructorLessons');
            lessonsContainer.innerHTML = '';
            if (lessons.length > 0) {
                lessons.forEach(lesson => {
                    const badge = document.createElement('span');
                    badge.className =
                        "inline-block bg-blue-600 text-white text-xs font-medium px-3 py-1 rounded-full";
                    badge.textContent = `${lesson.name} - ${currency}${lesson.price}`;
                    lessonsContainer.appendChild(badge);
                });
            } else {
                lessonsContainer.innerHTML =
                    '<span class="text-gray-500 text-sm">No lessons available</span>';
            }  --}}

            // Show popup
            const popup = document.getElementById('instructorPopup');
            popup.classList.remove('hidden');
            popup.classList.add('show');
            document.body.style.overflow = 'hidden';
        }

        function closeInstructorPopup() {
            const popup = document.getElementById('instructorPopup');
            popup.classList.add('hidden');
            popup.classList.remove('show');
            document.body.style.overflow = 'auto';
        }

        // Click outside to close
        document.getElementById('instructorPopup').addEventListener('click', function(e) {
            if (e.target === this) {
                closeInstructorPopup();
            }
        });

        // Escape key to close
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeInstructorPopup();
            }
        });
    </script>
@endpush
