@php
    $languages = \App\Facades\UtilityFacades::languages();
    $currency = tenancy()->central(function ($tenant) {
        return Utility::getsettings('currency_symbol');
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

    <header>
        <nav class="navbar navbar-expand-lg navbar-dark bg-white px-0 py-3">
            <div class="container ctm-container">
                <a class="navbar-brand" href="/">
                    <img src="{{ asset('assets/images/landing-page-images/logo-1.png') }}" class="h-8" alt="...">
                </a>
                <button class="request-text border-0 rounded-pill demo px-4 py-2 bg-primary">
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

    @if (trim($admin->bio) != '')
        <section class="lession-sec">
            <div class="container ctm-container">
                <h2 class="font-bold text-4xl mb-2">{{ $bio_heading ?? $admin->name }}</h2>
                <p class="text-xl text-gray-600">{{ $admin->bio }}</p>
            </div>
        </section>
    @endif
    @if (!$instructors->isEmpty())
        <section class="container">
            @if (count($instructors) == 1)
                <div class="row g-4 py-4">
                    @forelse($instructors[0]->lessons as $lesson)
                        @if ($lesson?->is_package_lesson == 0)
                            <div class="col-md-4">
                                <div class=" bg-gray rounded-lg shadow h-100  flex flex-col">
                                    <div class="relative text-center p-3 flex gap-3">
                                        <img src="{{ asset('storage/' . tenant()->id . '/' . ($instructors[0]->avatar ?? $instructors[0]->dp)) }}"
                                            alt="{{ $instructors[0]->name }}"
                                            class="hover:shadow-lg cursor-pointer rounded-lg h-32 w-24 object-cover">
                                        <div class="text-left">
                                            <a class="font-bold text-dark text-xl" href="{{ route('login') }}"
                                                tabindex="0">
                                                {{ $instructors[0]->name }}
                                            </a>
                                            <div class="text-lg font-bold tracking-tight text-primary">
                                                $ {{ $lesson?->lesson_price }}
                                            </div>
                                        </div>
                                    </div>

                                    <div class="px-3 pb-4 mt-1 flex flex-col flex-grow">
                                        <div class="description-wrapper relative expanded">
                                            <div class="short-text break-all clamp-text" id="lessonDesc">
                                                {!! $lesson?->lesson_description !!}
                                            </div>
                                            <a href="#"
                                                class="read-toggle text-blue-600 font-medium mt-1 inline-block"
                                                onclick="toggleRead(this); return false;" tabindex="0">&lt;&lt; Read
                                                Less</a>
                                        </div>

                                        <div class="mt-2 bg-gray-200 gap-2 rounded-lg px-4 py-3">
                                            <div class="text-center">
                                                <span class="text-xl font-bold">{{ $lesson?->required_time }} Days</span>
                                                <div class="text-sm rtl:space-x-reverse">Expected Response Time</div>
                                            </div>
                                        </div>
                                        <div class="w-100 mt-2">

                                            <a href="{{ route('login') }}" tabindex="0">
                                                <button type="submit" class="lesson-btn py-2"
                                                    tabindex="0">Purchase</button>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @else
                            <div class="col-md-4">
                                <div
                                    class="w-full max-w-sm bg-white border border-gray-200 rounded-lg shadow dark:bg-gray-800 dark:border-gray-700 flex flex-col h-full">
                                    <div class="relative text-center p-3 flex gap-3">

                                        <img src="https://t1.collegegolfrecruitingportal.com/storage/10/storage/10/logo/app-favicon-logo.png"
                                            alt="https://t1.collegegolfrecruitingportal.com/storage/10/storage/10/logo/app-favicon-logo.png"
                                            class="hover:shadow-lg cursor-pointer rounded-lg h-32 w-24 object-cover">
                                        <div class="text-left">
                                            <a class="font-bold text-dark text-xl"
                                                href="https://t1.collegegolfrecruitingportal.com/instructor/profile/get?instructor_id=2">
                                                Colin Mosley
                                            </a>
                                            <div class="text-lg font-bold tracking-tight text-primary">
                                                $ 0.00 (USD)
                                                <p>8 Slots available.</p>
                                            </div>
                                            <div class="text-sm font-medium text-gray-500 italic">

                                                <div class="flex flex-row justify-between">
                                                    <div
                                                        class="bg-green-500 text-white text-sm font-bold px-2 py-1 rounded-full">
                                                        Package
                                                        Lesson
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="px-3 pb-4 mt-1 flex flex-col flex-grow">

                                        <span class="text-xl font-semibold text-dark">kkjk</span>
                                        <div
                                            class="font-thin text-gray-600 overflow-hidden whitespace-nowrap overflow-ellipsis ck-content">
                                            <p>ghjg</p>

                                        </div>

                                        <div class="mb-3 p-3 border rounded-lg shadow-sm bg-white">
                                            <h2 class="text-lg font-semibold flex items-center mb-2">
                                                <svg class="w-5 h-5 mr-2 text-gray-700" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-width="2"
                                                        d="M8 7V3m8 4V3m-9 8h10m-10 4h6m4 8H5a2 2 0 01-2-2V7a2 2 0 012-2h3l2-2h4l2 2h3a2 2 0 012 2v12a2 2 0 01-2 2z">
                                                    </path>
                                                </svg>
                                                Package Options Available
                                            </h2>
                                            <p class="text-sm text-gray-500 mb-3">Save more with multi-lesson packages</p>
                                            <form class="space-y-3">
                                                <select class="form-select" name="package_slot" id="package_slot_3">
                                                    <option value="0">Select Package</option>
                                                    <option value="1234">1 Pack &nbsp;-&nbsp;
                                                        $ 1234 USD</option>
                                                </select>
                                            </form>
                                        </div>

                                        <div class="w-100 mt-3">

                                            <button class="lesson-btn"
                                                onclick="openBookingPopup([{&quot;id&quot;:37,&quot;lesson_id&quot;:3,&quot;date_time&quot;:&quot;2025-09-14 13:00:00&quot;,&quot;location&quot;:&quot;ghyug&quot;,&quot;is_completed&quot;:0,&quot;is_active&quot;:1,&quot;cancelled&quot;:0,&quot;tenant_id&quot;:&quot;10&quot;,&quot;student&quot;:[],&quot;lesson&quot;:{&quot;id&quot;:3,&quot;lesson_name&quot;:&quot;kkjk&quot;,&quot;lesson_description&quot;:&quot;&lt;p&gt;ghjg&lt;\/p&gt;\r\n&quot;,&quot;lesson_price&quot;:&quot;0.00&quot;,&quot;lesson_duration&quot;:0.5,&quot;tenant_id&quot;:&quot;10&quot;,&quot;lesson_quantity&quot;:1,&quot;required_time&quot;:0,&quot;created_by&quot;:2,&quot;active_status&quot;:1,&quot;type&quot;:&quot;package&quot;,&quot;is_package_lesson&quot;:1,&quot;payment_method&quot;:&quot;cash&quot;,&quot;created_at&quot;:&quot;2025-09-04T06:40:49.000000Z&quot;,&quot;updated_at&quot;:&quot;2025-09-04T06:56:08.000000Z&quot;,&quot;max_students&quot;:8}},{&quot;id&quot;:38,&quot;lesson_id&quot;:3,&quot;date_time&quot;:&quot;2025-09-14 13:30:00&quot;,&quot;location&quot;:&quot;ghyug&quot;,&quot;is_completed&quot;:0,&quot;is_active&quot;:1,&quot;cancelled&quot;:0,&quot;tenant_id&quot;:&quot;10&quot;,&quot;student&quot;:[],&quot;lesson&quot;:{&quot;id&quot;:3,&quot;lesson_name&quot;:&quot;kkjk&quot;,&quot;lesson_description&quot;:&quot;&lt;p&gt;ghjg&lt;\/p&gt;\r\n&quot;,&quot;lesson_price&quot;:&quot;0.00&quot;,&quot;lesson_duration&quot;:0.5,&quot;tenant_id&quot;:&quot;10&quot;,&quot;lesson_quantity&quot;:1,&quot;required_time&quot;:0,&quot;created_by&quot;:2,&quot;active_status&quot;:1,&quot;type&quot;:&quot;package&quot;,&quot;is_package_lesson&quot;:1,&quot;payment_method&quot;:&quot;cash&quot;,&quot;created_at&quot;:&quot;2025-09-04T06:40:49.000000Z&quot;,&quot;updated_at&quot;:&quot;2025-09-04T06:56:08.000000Z&quot;,&quot;max_students&quot;:8}},{&quot;id&quot;:39,&quot;lesson_id&quot;:3,&quot;date_time&quot;:&quot;2025-09-14 14:00:00&quot;,&quot;location&quot;:&quot;ghyug&quot;,&quot;is_completed&quot;:0,&quot;is_active&quot;:1,&quot;cancelled&quot;:0,&quot;tenant_id&quot;:&quot;10&quot;,&quot;student&quot;:[],&quot;lesson&quot;:{&quot;id&quot;:3,&quot;lesson_name&quot;:&quot;kkjk&quot;,&quot;lesson_description&quot;:&quot;&lt;p&gt;ghjg&lt;\/p&gt;\r\n&quot;,&quot;lesson_price&quot;:&quot;0.00&quot;,&quot;lesson_duration&quot;:0.5,&quot;tenant_id&quot;:&quot;10&quot;,&quot;lesson_quantity&quot;:1,&quot;required_time&quot;:0,&quot;created_by&quot;:2,&quot;active_status&quot;:1,&quot;type&quot;:&quot;package&quot;,&quot;is_package_lesson&quot;:1,&quot;payment_method&quot;:&quot;cash&quot;,&quot;created_at&quot;:&quot;2025-09-04T06:40:49.000000Z&quot;,&quot;updated_at&quot;:&quot;2025-09-04T06:56:08.000000Z&quot;,&quot;max_students&quot;:8}},{&quot;id&quot;:40,&quot;lesson_id&quot;:3,&quot;date_time&quot;:&quot;2025-09-14 14:30:00&quot;,&quot;location&quot;:&quot;ghyug&quot;,&quot;is_completed&quot;:0,&quot;is_active&quot;:1,&quot;cancelled&quot;:0,&quot;tenant_id&quot;:&quot;10&quot;,&quot;student&quot;:[],&quot;lesson&quot;:{&quot;id&quot;:3,&quot;lesson_name&quot;:&quot;kkjk&quot;,&quot;lesson_description&quot;:&quot;&lt;p&gt;ghjg&lt;\/p&gt;\r\n&quot;,&quot;lesson_price&quot;:&quot;0.00&quot;,&quot;lesson_duration&quot;:0.5,&quot;tenant_id&quot;:&quot;10&quot;,&quot;lesson_quantity&quot;:1,&quot;required_time&quot;:0,&quot;created_by&quot;:2,&quot;active_status&quot;:1,&quot;type&quot;:&quot;package&quot;,&quot;is_package_lesson&quot;:1,&quot;payment_method&quot;:&quot;cash&quot;,&quot;created_at&quot;:&quot;2025-09-04T06:40:49.000000Z&quot;,&quot;updated_at&quot;:&quot;2025-09-04T06:56:08.000000Z&quot;,&quot;max_students&quot;:8}},{&quot;id&quot;:41,&quot;lesson_id&quot;:3,&quot;date_time&quot;:&quot;2025-09-15 13:00:00&quot;,&quot;location&quot;:&quot;ghyug&quot;,&quot;is_completed&quot;:0,&quot;is_active&quot;:1,&quot;cancelled&quot;:0,&quot;tenant_id&quot;:&quot;10&quot;,&quot;student&quot;:[],&quot;lesson&quot;:{&quot;id&quot;:3,&quot;lesson_name&quot;:&quot;kkjk&quot;,&quot;lesson_description&quot;:&quot;&lt;p&gt;ghjg&lt;\/p&gt;\r\n&quot;,&quot;lesson_price&quot;:&quot;0.00&quot;,&quot;lesson_duration&quot;:0.5,&quot;tenant_id&quot;:&quot;10&quot;,&quot;lesson_quantity&quot;:1,&quot;required_time&quot;:0,&quot;created_by&quot;:2,&quot;active_status&quot;:1,&quot;type&quot;:&quot;package&quot;,&quot;is_package_lesson&quot;:1,&quot;payment_method&quot;:&quot;cash&quot;,&quot;created_at&quot;:&quot;2025-09-04T06:40:49.000000Z&quot;,&quot;updated_at&quot;:&quot;2025-09-04T06:56:08.000000Z&quot;,&quot;max_students&quot;:8}},{&quot;id&quot;:42,&quot;lesson_id&quot;:3,&quot;date_time&quot;:&quot;2025-09-15 13:30:00&quot;,&quot;location&quot;:&quot;ghyug&quot;,&quot;is_completed&quot;:0,&quot;is_active&quot;:1,&quot;cancelled&quot;:0,&quot;tenant_id&quot;:&quot;10&quot;,&quot;student&quot;:[],&quot;lesson&quot;:{&quot;id&quot;:3,&quot;lesson_name&quot;:&quot;kkjk&quot;,&quot;lesson_description&quot;:&quot;&lt;p&gt;ghjg&lt;\/p&gt;\r\n&quot;,&quot;lesson_price&quot;:&quot;0.00&quot;,&quot;lesson_duration&quot;:0.5,&quot;tenant_id&quot;:&quot;10&quot;,&quot;lesson_quantity&quot;:1,&quot;required_time&quot;:0,&quot;created_by&quot;:2,&quot;active_status&quot;:1,&quot;type&quot;:&quot;package&quot;,&quot;is_package_lesson&quot;:1,&quot;payment_method&quot;:&quot;cash&quot;,&quot;created_at&quot;:&quot;2025-09-04T06:40:49.000000Z&quot;,&quot;updated_at&quot;:&quot;2025-09-04T06:56:08.000000Z&quot;,&quot;max_students&quot;:8}},{&quot;id&quot;:43,&quot;lesson_id&quot;:3,&quot;date_time&quot;:&quot;2025-09-15 14:00:00&quot;,&quot;location&quot;:&quot;ghyug&quot;,&quot;is_completed&quot;:0,&quot;is_active&quot;:1,&quot;cancelled&quot;:0,&quot;tenant_id&quot;:&quot;10&quot;,&quot;student&quot;:[],&quot;lesson&quot;:{&quot;id&quot;:3,&quot;lesson_name&quot;:&quot;kkjk&quot;,&quot;lesson_description&quot;:&quot;&lt;p&gt;ghjg&lt;\/p&gt;\r\n&quot;,&quot;lesson_price&quot;:&quot;0.00&quot;,&quot;lesson_duration&quot;:0.5,&quot;tenant_id&quot;:&quot;10&quot;,&quot;lesson_quantity&quot;:1,&quot;required_time&quot;:0,&quot;created_by&quot;:2,&quot;active_status&quot;:1,&quot;type&quot;:&quot;package&quot;,&quot;is_package_lesson&quot;:1,&quot;payment_method&quot;:&quot;cash&quot;,&quot;created_at&quot;:&quot;2025-09-04T06:40:49.000000Z&quot;,&quot;updated_at&quot;:&quot;2025-09-04T06:56:08.000000Z&quot;,&quot;max_students&quot;:8}},{&quot;id&quot;:44,&quot;lesson_id&quot;:3,&quot;date_time&quot;:&quot;2025-09-15 14:30:00&quot;,&quot;location&quot;:&quot;ghyug&quot;,&quot;is_completed&quot;:0,&quot;is_active&quot;:1,&quot;cancelled&quot;:0,&quot;tenant_id&quot;:&quot;10&quot;,&quot;student&quot;:[],&quot;lesson&quot;:{&quot;id&quot;:3,&quot;lesson_name&quot;:&quot;kkjk&quot;,&quot;lesson_description&quot;:&quot;&lt;p&gt;ghjg&lt;\/p&gt;\r\n&quot;,&quot;lesson_price&quot;:&quot;0.00&quot;,&quot;lesson_duration&quot;:0.5,&quot;tenant_id&quot;:&quot;10&quot;,&quot;lesson_quantity&quot;:1,&quot;required_time&quot;:0,&quot;created_by&quot;:2,&quot;active_status&quot;:1,&quot;type&quot;:&quot;package&quot;,&quot;is_package_lesson&quot;:1,&quot;payment_method&quot;:&quot;cash&quot;,&quot;created_at&quot;:&quot;2025-09-04T06:40:49.000000Z&quot;,&quot;updated_at&quot;:&quot;2025-09-04T06:56:08.000000Z&quot;,&quot;max_students&quot;:8}},{&quot;id&quot;:45,&quot;lesson_id&quot;:3,&quot;date_time&quot;:&quot;2025-09-16 13:00:00&quot;,&quot;location&quot;:&quot;ghyug&quot;,&quot;is_completed&quot;:0,&quot;is_active&quot;:1,&quot;cancelled&quot;:0,&quot;tenant_id&quot;:&quot;10&quot;,&quot;student&quot;:[],&quot;lesson&quot;:{&quot;id&quot;:3,&quot;lesson_name&quot;:&quot;kkjk&quot;,&quot;lesson_description&quot;:&quot;&lt;p&gt;ghjg&lt;\/p&gt;\r\n&quot;,&quot;lesson_price&quot;:&quot;0.00&quot;,&quot;lesson_duration&quot;:0.5,&quot;tenant_id&quot;:&quot;10&quot;,&quot;lesson_quantity&quot;:1,&quot;required_time&quot;:0,&quot;created_by&quot;:2,&quot;active_status&quot;:1,&quot;type&quot;:&quot;package&quot;,&quot;is_package_lesson&quot;:1,&quot;payment_method&quot;:&quot;cash&quot;,&quot;created_at&quot;:&quot;2025-09-04T06:40:49.000000Z&quot;,&quot;updated_at&quot;:&quot;2025-09-04T06:56:08.000000Z&quot;,&quot;max_students&quot;:8}},{&quot;id&quot;:46,&quot;lesson_id&quot;:3,&quot;date_time&quot;:&quot;2025-09-16 13:30:00&quot;,&quot;location&quot;:&quot;ghyug&quot;,&quot;is_completed&quot;:0,&quot;is_active&quot;:1,&quot;cancelled&quot;:0,&quot;tenant_id&quot;:&quot;10&quot;,&quot;student&quot;:[],&quot;lesson&quot;:{&quot;id&quot;:3,&quot;lesson_name&quot;:&quot;kkjk&quot;,&quot;lesson_description&quot;:&quot;&lt;p&gt;ghjg&lt;\/p&gt;\r\n&quot;,&quot;lesson_price&quot;:&quot;0.00&quot;,&quot;lesson_duration&quot;:0.5,&quot;tenant_id&quot;:&quot;10&quot;,&quot;lesson_quantity&quot;:1,&quot;required_time&quot;:0,&quot;created_by&quot;:2,&quot;active_status&quot;:1,&quot;type&quot;:&quot;package&quot;,&quot;is_package_lesson&quot;:1,&quot;payment_method&quot;:&quot;cash&quot;,&quot;created_at&quot;:&quot;2025-09-04T06:40:49.000000Z&quot;,&quot;updated_at&quot;:&quot;2025-09-04T06:56:08.000000Z&quot;,&quot;max_students&quot;:8}},{&quot;id&quot;:47,&quot;lesson_id&quot;:3,&quot;date_time&quot;:&quot;2025-09-16 14:00:00&quot;,&quot;location&quot;:&quot;ghyug&quot;,&quot;is_completed&quot;:0,&quot;is_active&quot;:1,&quot;cancelled&quot;:0,&quot;tenant_id&quot;:&quot;10&quot;,&quot;student&quot;:[],&quot;lesson&quot;:{&quot;id&quot;:3,&quot;lesson_name&quot;:&quot;kkjk&quot;,&quot;lesson_description&quot;:&quot;&lt;p&gt;ghjg&lt;\/p&gt;\r\n&quot;,&quot;lesson_price&quot;:&quot;0.00&quot;,&quot;lesson_duration&quot;:0.5,&quot;tenant_id&quot;:&quot;10&quot;,&quot;lesson_quantity&quot;:1,&quot;required_time&quot;:0,&quot;created_by&quot;:2,&quot;active_status&quot;:1,&quot;type&quot;:&quot;package&quot;,&quot;is_package_lesson&quot;:1,&quot;payment_method&quot;:&quot;cash&quot;,&quot;created_at&quot;:&quot;2025-09-04T06:40:49.000000Z&quot;,&quot;updated_at&quot;:&quot;2025-09-04T06:56:08.000000Z&quot;,&quot;max_students&quot;:8}},{&quot;id&quot;:48,&quot;lesson_id&quot;:3,&quot;date_time&quot;:&quot;2025-09-16 14:30:00&quot;,&quot;location&quot;:&quot;ghyug&quot;,&quot;is_completed&quot;:0,&quot;is_active&quot;:1,&quot;cancelled&quot;:0,&quot;tenant_id&quot;:&quot;10&quot;,&quot;student&quot;:[],&quot;lesson&quot;:{&quot;id&quot;:3,&quot;lesson_name&quot;:&quot;kkjk&quot;,&quot;lesson_description&quot;:&quot;&lt;p&gt;ghjg&lt;\/p&gt;\r\n&quot;,&quot;lesson_price&quot;:&quot;0.00&quot;,&quot;lesson_duration&quot;:0.5,&quot;tenant_id&quot;:&quot;10&quot;,&quot;lesson_quantity&quot;:1,&quot;required_time&quot;:0,&quot;created_by&quot;:2,&quot;active_status&quot;:1,&quot;type&quot;:&quot;package&quot;,&quot;is_package_lesson&quot;:1,&quot;payment_method&quot;:&quot;cash&quot;,&quot;created_at&quot;:&quot;2025-09-04T06:40:49.000000Z&quot;,&quot;updated_at&quot;:&quot;2025-09-04T06:56:08.000000Z&quot;,&quot;max_students&quot;:8}},{&quot;id&quot;:49,&quot;lesson_id&quot;:3,&quot;date_time&quot;:&quot;2025-09-17 13:00:00&quot;,&quot;location&quot;:&quot;ghyug&quot;,&quot;is_completed&quot;:0,&quot;is_active&quot;:1,&quot;cancelled&quot;:0,&quot;tenant_id&quot;:&quot;10&quot;,&quot;student&quot;:[],&quot;lesson&quot;:{&quot;id&quot;:3,&quot;lesson_name&quot;:&quot;kkjk&quot;,&quot;lesson_description&quot;:&quot;&lt;p&gt;ghjg&lt;\/p&gt;\r\n&quot;,&quot;lesson_price&quot;:&quot;0.00&quot;,&quot;lesson_duration&quot;:0.5,&quot;tenant_id&quot;:&quot;10&quot;,&quot;lesson_quantity&quot;:1,&quot;required_time&quot;:0,&quot;created_by&quot;:2,&quot;active_status&quot;:1,&quot;type&quot;:&quot;package&quot;,&quot;is_package_lesson&quot;:1,&quot;payment_method&quot;:&quot;cash&quot;,&quot;created_at&quot;:&quot;2025-09-04T06:40:49.000000Z&quot;,&quot;updated_at&quot;:&quot;2025-09-04T06:56:08.000000Z&quot;,&quot;max_students&quot;:8}},{&quot;id&quot;:50,&quot;lesson_id&quot;:3,&quot;date_time&quot;:&quot;2025-09-17 13:30:00&quot;,&quot;location&quot;:&quot;ghyug&quot;,&quot;is_completed&quot;:0,&quot;is_active&quot;:1,&quot;cancelled&quot;:0,&quot;tenant_id&quot;:&quot;10&quot;,&quot;student&quot;:[],&quot;lesson&quot;:{&quot;id&quot;:3,&quot;lesson_name&quot;:&quot;kkjk&quot;,&quot;lesson_description&quot;:&quot;&lt;p&gt;ghjg&lt;\/p&gt;\r\n&quot;,&quot;lesson_price&quot;:&quot;0.00&quot;,&quot;lesson_duration&quot;:0.5,&quot;tenant_id&quot;:&quot;10&quot;,&quot;lesson_quantity&quot;:1,&quot;required_time&quot;:0,&quot;created_by&quot;:2,&quot;active_status&quot;:1,&quot;type&quot;:&quot;package&quot;,&quot;is_package_lesson&quot;:1,&quot;payment_method&quot;:&quot;cash&quot;,&quot;created_at&quot;:&quot;2025-09-04T06:40:49.000000Z&quot;,&quot;updated_at&quot;:&quot;2025-09-04T06:56:08.000000Z&quot;,&quot;max_students&quot;:8}},{&quot;id&quot;:51,&quot;lesson_id&quot;:3,&quot;date_time&quot;:&quot;2025-09-17 14:00:00&quot;,&quot;location&quot;:&quot;ghyug&quot;,&quot;is_completed&quot;:0,&quot;is_active&quot;:1,&quot;cancelled&quot;:0,&quot;tenant_id&quot;:&quot;10&quot;,&quot;student&quot;:[],&quot;lesson&quot;:{&quot;id&quot;:3,&quot;lesson_name&quot;:&quot;kkjk&quot;,&quot;lesson_description&quot;:&quot;&lt;p&gt;ghjg&lt;\/p&gt;\r\n&quot;,&quot;lesson_price&quot;:&quot;0.00&quot;,&quot;lesson_duration&quot;:0.5,&quot;tenant_id&quot;:&quot;10&quot;,&quot;lesson_quantity&quot;:1,&quot;required_time&quot;:0,&quot;created_by&quot;:2,&quot;active_status&quot;:1,&quot;type&quot;:&quot;package&quot;,&quot;is_package_lesson&quot;:1,&quot;payment_method&quot;:&quot;cash&quot;,&quot;created_at&quot;:&quot;2025-09-04T06:40:49.000000Z&quot;,&quot;updated_at&quot;:&quot;2025-09-04T06:56:08.000000Z&quot;,&quot;max_students&quot;:8}},{&quot;id&quot;:52,&quot;lesson_id&quot;:3,&quot;date_time&quot;:&quot;2025-09-17 14:30:00&quot;,&quot;location&quot;:&quot;ghyug&quot;,&quot;is_completed&quot;:0,&quot;is_active&quot;:1,&quot;cancelled&quot;:0,&quot;tenant_id&quot;:&quot;10&quot;,&quot;student&quot;:[],&quot;lesson&quot;:{&quot;id&quot;:3,&quot;lesson_name&quot;:&quot;kkjk&quot;,&quot;lesson_description&quot;:&quot;&lt;p&gt;ghjg&lt;\/p&gt;\r\n&quot;,&quot;lesson_price&quot;:&quot;0.00&quot;,&quot;lesson_duration&quot;:0.5,&quot;tenant_id&quot;:&quot;10&quot;,&quot;lesson_quantity&quot;:1,&quot;required_time&quot;:0,&quot;created_by&quot;:2,&quot;active_status&quot;:1,&quot;type&quot;:&quot;package&quot;,&quot;is_package_lesson&quot;:1,&quot;payment_method&quot;:&quot;cash&quot;,&quot;created_at&quot;:&quot;2025-09-04T06:40:49.000000Z&quot;,&quot;updated_at&quot;:&quot;2025-09-04T06:56:08.000000Z&quot;,&quot;max_students&quot;:8}},{&quot;id&quot;:53,&quot;lesson_id&quot;:3,&quot;date_time&quot;:&quot;2025-09-18 13:00:00&quot;,&quot;location&quot;:&quot;ghyug&quot;,&quot;is_completed&quot;:0,&quot;is_active&quot;:1,&quot;cancelled&quot;:0,&quot;tenant_id&quot;:&quot;10&quot;,&quot;student&quot;:[],&quot;lesson&quot;:{&quot;id&quot;:3,&quot;lesson_name&quot;:&quot;kkjk&quot;,&quot;lesson_description&quot;:&quot;&lt;p&gt;ghjg&lt;\/p&gt;\r\n&quot;,&quot;lesson_price&quot;:&quot;0.00&quot;,&quot;lesson_duration&quot;:0.5,&quot;tenant_id&quot;:&quot;10&quot;,&quot;lesson_quantity&quot;:1,&quot;required_time&quot;:0,&quot;created_by&quot;:2,&quot;active_status&quot;:1,&quot;type&quot;:&quot;package&quot;,&quot;is_package_lesson&quot;:1,&quot;payment_method&quot;:&quot;cash&quot;,&quot;created_at&quot;:&quot;2025-09-04T06:40:49.000000Z&quot;,&quot;updated_at&quot;:&quot;2025-09-04T06:56:08.000000Z&quot;,&quot;max_students&quot;:8}},{&quot;id&quot;:54,&quot;lesson_id&quot;:3,&quot;date_time&quot;:&quot;2025-09-18 13:30:00&quot;,&quot;location&quot;:&quot;ghyug&quot;,&quot;is_completed&quot;:0,&quot;is_active&quot;:1,&quot;cancelled&quot;:0,&quot;tenant_id&quot;:&quot;10&quot;,&quot;student&quot;:[],&quot;lesson&quot;:{&quot;id&quot;:3,&quot;lesson_name&quot;:&quot;kkjk&quot;,&quot;lesson_description&quot;:&quot;&lt;p&gt;ghjg&lt;\/p&gt;\r\n&quot;,&quot;lesson_price&quot;:&quot;0.00&quot;,&quot;lesson_duration&quot;:0.5,&quot;tenant_id&quot;:&quot;10&quot;,&quot;lesson_quantity&quot;:1,&quot;required_time&quot;:0,&quot;created_by&quot;:2,&quot;active_status&quot;:1,&quot;type&quot;:&quot;package&quot;,&quot;is_package_lesson&quot;:1,&quot;payment_method&quot;:&quot;cash&quot;,&quot;created_at&quot;:&quot;2025-09-04T06:40:49.000000Z&quot;,&quot;updated_at&quot;:&quot;2025-09-04T06:56:08.000000Z&quot;,&quot;max_students&quot;:8}},{&quot;id&quot;:55,&quot;lesson_id&quot;:3,&quot;date_time&quot;:&quot;2025-09-18 14:00:00&quot;,&quot;location&quot;:&quot;ghyug&quot;,&quot;is_completed&quot;:0,&quot;is_active&quot;:1,&quot;cancelled&quot;:0,&quot;tenant_id&quot;:&quot;10&quot;,&quot;student&quot;:[],&quot;lesson&quot;:{&quot;id&quot;:3,&quot;lesson_name&quot;:&quot;kkjk&quot;,&quot;lesson_description&quot;:&quot;&lt;p&gt;ghjg&lt;\/p&gt;\r\n&quot;,&quot;lesson_price&quot;:&quot;0.00&quot;,&quot;lesson_duration&quot;:0.5,&quot;tenant_id&quot;:&quot;10&quot;,&quot;lesson_quantity&quot;:1,&quot;required_time&quot;:0,&quot;created_by&quot;:2,&quot;active_status&quot;:1,&quot;type&quot;:&quot;package&quot;,&quot;is_package_lesson&quot;:1,&quot;payment_method&quot;:&quot;cash&quot;,&quot;created_at&quot;:&quot;2025-09-04T06:40:49.000000Z&quot;,&quot;updated_at&quot;:&quot;2025-09-04T06:56:08.000000Z&quot;,&quot;max_students&quot;:8}},{&quot;id&quot;:56,&quot;lesson_id&quot;:3,&quot;date_time&quot;:&quot;2025-09-18 14:30:00&quot;,&quot;location&quot;:&quot;ghyug&quot;,&quot;is_completed&quot;:0,&quot;is_active&quot;:1,&quot;cancelled&quot;:0,&quot;tenant_id&quot;:&quot;10&quot;,&quot;student&quot;:[],&quot;lesson&quot;:{&quot;id&quot;:3,&quot;lesson_name&quot;:&quot;kkjk&quot;,&quot;lesson_description&quot;:&quot;&lt;p&gt;ghjg&lt;\/p&gt;\r\n&quot;,&quot;lesson_price&quot;:&quot;0.00&quot;,&quot;lesson_duration&quot;:0.5,&quot;tenant_id&quot;:&quot;10&quot;,&quot;lesson_quantity&quot;:1,&quot;required_time&quot;:0,&quot;created_by&quot;:2,&quot;active_status&quot;:1,&quot;type&quot;:&quot;package&quot;,&quot;is_package_lesson&quot;:1,&quot;payment_method&quot;:&quot;cash&quot;,&quot;created_at&quot;:&quot;2025-09-04T06:40:49.000000Z&quot;,&quot;updated_at&quot;:&quot;2025-09-04T06:56:08.000000Z&quot;,&quot;max_students&quot;:8}},{&quot;id&quot;:57,&quot;lesson_id&quot;:3,&quot;date_time&quot;:&quot;2025-09-19 13:00:00&quot;,&quot;location&quot;:&quot;ghyug&quot;,&quot;is_completed&quot;:0,&quot;is_active&quot;:1,&quot;cancelled&quot;:0,&quot;tenant_id&quot;:&quot;10&quot;,&quot;student&quot;:[],&quot;lesson&quot;:{&quot;id&quot;:3,&quot;lesson_name&quot;:&quot;kkjk&quot;,&quot;lesson_description&quot;:&quot;&lt;p&gt;ghjg&lt;\/p&gt;\r\n&quot;,&quot;lesson_price&quot;:&quot;0.00&quot;,&quot;lesson_duration&quot;:0.5,&quot;tenant_id&quot;:&quot;10&quot;,&quot;lesson_quantity&quot;:1,&quot;required_time&quot;:0,&quot;created_by&quot;:2,&quot;active_status&quot;:1,&quot;type&quot;:&quot;package&quot;,&quot;is_package_lesson&quot;:1,&quot;payment_method&quot;:&quot;cash&quot;,&quot;created_at&quot;:&quot;2025-09-04T06:40:49.000000Z&quot;,&quot;updated_at&quot;:&quot;2025-09-04T06:56:08.000000Z&quot;,&quot;max_students&quot;:8}},{&quot;id&quot;:58,&quot;lesson_id&quot;:3,&quot;date_time&quot;:&quot;2025-09-19 13:30:00&quot;,&quot;location&quot;:&quot;ghyug&quot;,&quot;is_completed&quot;:0,&quot;is_active&quot;:1,&quot;cancelled&quot;:0,&quot;tenant_id&quot;:&quot;10&quot;,&quot;student&quot;:[],&quot;lesson&quot;:{&quot;id&quot;:3,&quot;lesson_name&quot;:&quot;kkjk&quot;,&quot;lesson_description&quot;:&quot;&lt;p&gt;ghjg&lt;\/p&gt;\r\n&quot;,&quot;lesson_price&quot;:&quot;0.00&quot;,&quot;lesson_duration&quot;:0.5,&quot;tenant_id&quot;:&quot;10&quot;,&quot;lesson_quantity&quot;:1,&quot;required_time&quot;:0,&quot;created_by&quot;:2,&quot;active_status&quot;:1,&quot;type&quot;:&quot;package&quot;,&quot;is_package_lesson&quot;:1,&quot;payment_method&quot;:&quot;cash&quot;,&quot;created_at&quot;:&quot;2025-09-04T06:40:49.000000Z&quot;,&quot;updated_at&quot;:&quot;2025-09-04T06:56:08.000000Z&quot;,&quot;max_students&quot;:8}},{&quot;id&quot;:59,&quot;lesson_id&quot;:3,&quot;date_time&quot;:&quot;2025-09-19 14:00:00&quot;,&quot;location&quot;:&quot;ghyug&quot;,&quot;is_completed&quot;:0,&quot;is_active&quot;:1,&quot;cancelled&quot;:0,&quot;tenant_id&quot;:&quot;10&quot;,&quot;student&quot;:[],&quot;lesson&quot;:{&quot;id&quot;:3,&quot;lesson_name&quot;:&quot;kkjk&quot;,&quot;lesson_description&quot;:&quot;&lt;p&gt;ghjg&lt;\/p&gt;\r\n&quot;,&quot;lesson_price&quot;:&quot;0.00&quot;,&quot;lesson_duration&quot;:0.5,&quot;tenant_id&quot;:&quot;10&quot;,&quot;lesson_quantity&quot;:1,&quot;required_time&quot;:0,&quot;created_by&quot;:2,&quot;active_status&quot;:1,&quot;type&quot;:&quot;package&quot;,&quot;is_package_lesson&quot;:1,&quot;payment_method&quot;:&quot;cash&quot;,&quot;created_at&quot;:&quot;2025-09-04T06:40:49.000000Z&quot;,&quot;updated_at&quot;:&quot;2025-09-04T06:56:08.000000Z&quot;,&quot;max_students&quot;:8}},{&quot;id&quot;:60,&quot;lesson_id&quot;:3,&quot;date_time&quot;:&quot;2025-09-19 14:30:00&quot;,&quot;location&quot;:&quot;ghyug&quot;,&quot;is_completed&quot;:0,&quot;is_active&quot;:1,&quot;cancelled&quot;:0,&quot;tenant_id&quot;:&quot;10&quot;,&quot;student&quot;:[],&quot;lesson&quot;:{&quot;id&quot;:3,&quot;lesson_name&quot;:&quot;kkjk&quot;,&quot;lesson_description&quot;:&quot;&lt;p&gt;ghjg&lt;\/p&gt;\r\n&quot;,&quot;lesson_price&quot;:&quot;0.00&quot;,&quot;lesson_duration&quot;:0.5,&quot;tenant_id&quot;:&quot;10&quot;,&quot;lesson_quantity&quot;:1,&quot;required_time&quot;:0,&quot;created_by&quot;:2,&quot;active_status&quot;:1,&quot;type&quot;:&quot;package&quot;,&quot;is_package_lesson&quot;:1,&quot;payment_method&quot;:&quot;cash&quot;,&quot;created_at&quot;:&quot;2025-09-04T06:40:49.000000Z&quot;,&quot;updated_at&quot;:&quot;2025-09-04T06:56:08.000000Z&quot;,&quot;max_students&quot;:8}},{&quot;id&quot;:77,&quot;lesson_id&quot;:3,&quot;date_time&quot;:&quot;2025-09-10 14:00:00&quot;,&quot;location&quot;:&quot;nk&quot;,&quot;is_completed&quot;:0,&quot;is_active&quot;:1,&quot;cancelled&quot;:0,&quot;tenant_id&quot;:&quot;10&quot;,&quot;student&quot;:[],&quot;lesson&quot;:{&quot;id&quot;:3,&quot;lesson_name&quot;:&quot;kkjk&quot;,&quot;lesson_description&quot;:&quot;&lt;p&gt;ghjg&lt;\/p&gt;\r\n&quot;,&quot;lesson_price&quot;:&quot;0.00&quot;,&quot;lesson_duration&quot;:0.5,&quot;tenant_id&quot;:&quot;10&quot;,&quot;lesson_quantity&quot;:1,&quot;required_time&quot;:0,&quot;created_by&quot;:2,&quot;active_status&quot;:1,&quot;type&quot;:&quot;package&quot;,&quot;is_package_lesson&quot;:1,&quot;payment_method&quot;:&quot;cash&quot;,&quot;created_at&quot;:&quot;2025-09-04T06:40:49.000000Z&quot;,&quot;updated_at&quot;:&quot;2025-09-04T06:56:08.000000Z&quot;,&quot;max_students&quot;:8}},{&quot;id&quot;:78,&quot;lesson_id&quot;:3,&quot;date_time&quot;:&quot;2025-09-10 14:30:00&quot;,&quot;location&quot;:&quot;nk&quot;,&quot;is_completed&quot;:0,&quot;is_active&quot;:1,&quot;cancelled&quot;:0,&quot;tenant_id&quot;:&quot;10&quot;,&quot;student&quot;:[],&quot;lesson&quot;:{&quot;id&quot;:3,&quot;lesson_name&quot;:&quot;kkjk&quot;,&quot;lesson_description&quot;:&quot;&lt;p&gt;ghjg&lt;\/p&gt;\r\n&quot;,&quot;lesson_price&quot;:&quot;0.00&quot;,&quot;lesson_duration&quot;:0.5,&quot;tenant_id&quot;:&quot;10&quot;,&quot;lesson_quantity&quot;:1,&quot;required_time&quot;:0,&quot;created_by&quot;:2,&quot;active_status&quot;:1,&quot;type&quot;:&quot;package&quot;,&quot;is_package_lesson&quot;:1,&quot;payment_method&quot;:&quot;cash&quot;,&quot;created_at&quot;:&quot;2025-09-04T06:40:49.000000Z&quot;,&quot;updated_at&quot;:&quot;2025-09-04T06:56:08.000000Z&quot;,&quot;max_students&quot;:8}},{&quot;id&quot;:79,&quot;lesson_id&quot;:3,&quot;date_time&quot;:&quot;2025-09-05 14:00:00&quot;,&quot;location&quot;:&quot;nk&quot;,&quot;is_completed&quot;:0,&quot;is_active&quot;:1,&quot;cancelled&quot;:0,&quot;tenant_id&quot;:&quot;10&quot;,&quot;student&quot;:[],&quot;lesson&quot;:{&quot;id&quot;:3,&quot;lesson_name&quot;:&quot;kkjk&quot;,&quot;lesson_description&quot;:&quot;&lt;p&gt;ghjg&lt;\/p&gt;\r\n&quot;,&quot;lesson_price&quot;:&quot;0.00&quot;,&quot;lesson_duration&quot;:0.5,&quot;tenant_id&quot;:&quot;10&quot;,&quot;lesson_quantity&quot;:1,&quot;required_time&quot;:0,&quot;created_by&quot;:2,&quot;active_status&quot;:1,&quot;type&quot;:&quot;package&quot;,&quot;is_package_lesson&quot;:1,&quot;payment_method&quot;:&quot;cash&quot;,&quot;created_at&quot;:&quot;2025-09-04T06:40:49.000000Z&quot;,&quot;updated_at&quot;:&quot;2025-09-04T06:56:08.000000Z&quot;,&quot;max_students&quot;:8}},{&quot;id&quot;:80,&quot;lesson_id&quot;:3,&quot;date_time&quot;:&quot;2025-09-05 14:30:00&quot;,&quot;location&quot;:&quot;nk&quot;,&quot;is_completed&quot;:0,&quot;is_active&quot;:1,&quot;cancelled&quot;:0,&quot;tenant_id&quot;:&quot;10&quot;,&quot;student&quot;:[],&quot;lesson&quot;:{&quot;id&quot;:3,&quot;lesson_name&quot;:&quot;kkjk&quot;,&quot;lesson_description&quot;:&quot;&lt;p&gt;ghjg&lt;\/p&gt;\r\n&quot;,&quot;lesson_price&quot;:&quot;0.00&quot;,&quot;lesson_duration&quot;:0.5,&quot;tenant_id&quot;:&quot;10&quot;,&quot;lesson_quantity&quot;:1,&quot;required_time&quot;:0,&quot;created_by&quot;:2,&quot;active_status&quot;:1,&quot;type&quot;:&quot;package&quot;,&quot;is_package_lesson&quot;:1,&quot;payment_method&quot;:&quot;cash&quot;,&quot;created_at&quot;:&quot;2025-09-04T06:40:49.000000Z&quot;,&quot;updated_at&quot;:&quot;2025-09-04T06:56:08.000000Z&quot;,&quot;max_students&quot;:8}},{&quot;id&quot;:81,&quot;lesson_id&quot;:3,&quot;date_time&quot;:&quot;2025-09-04 14:00:00&quot;,&quot;location&quot;:&quot;nk&quot;,&quot;is_completed&quot;:0,&quot;is_active&quot;:1,&quot;cancelled&quot;:0,&quot;tenant_id&quot;:&quot;10&quot;,&quot;student&quot;:[],&quot;lesson&quot;:{&quot;id&quot;:3,&quot;lesson_name&quot;:&quot;kkjk&quot;,&quot;lesson_description&quot;:&quot;&lt;p&gt;ghjg&lt;\/p&gt;\r\n&quot;,&quot;lesson_price&quot;:&quot;0.00&quot;,&quot;lesson_duration&quot;:0.5,&quot;tenant_id&quot;:&quot;10&quot;,&quot;lesson_quantity&quot;:1,&quot;required_time&quot;:0,&quot;created_by&quot;:2,&quot;active_status&quot;:1,&quot;type&quot;:&quot;package&quot;,&quot;is_package_lesson&quot;:1,&quot;payment_method&quot;:&quot;cash&quot;,&quot;created_at&quot;:&quot;2025-09-04T06:40:49.000000Z&quot;,&quot;updated_at&quot;:&quot;2025-09-04T06:56:08.000000Z&quot;,&quot;max_students&quot;:8}},{&quot;id&quot;:82,&quot;lesson_id&quot;:3,&quot;date_time&quot;:&quot;2025-09-04 14:30:00&quot;,&quot;location&quot;:&quot;nk&quot;,&quot;is_completed&quot;:0,&quot;is_active&quot;:1,&quot;cancelled&quot;:0,&quot;tenant_id&quot;:&quot;10&quot;,&quot;student&quot;:[],&quot;lesson&quot;:{&quot;id&quot;:3,&quot;lesson_name&quot;:&quot;kkjk&quot;,&quot;lesson_description&quot;:&quot;&lt;p&gt;ghjg&lt;\/p&gt;\r\n&quot;,&quot;lesson_price&quot;:&quot;0.00&quot;,&quot;lesson_duration&quot;:0.5,&quot;tenant_id&quot;:&quot;10&quot;,&quot;lesson_quantity&quot;:1,&quot;required_time&quot;:0,&quot;created_by&quot;:2,&quot;active_status&quot;:1,&quot;type&quot;:&quot;package&quot;,&quot;is_package_lesson&quot;:1,&quot;payment_method&quot;:&quot;cash&quot;,&quot;created_at&quot;:&quot;2025-09-04T06:40:49.000000Z&quot;,&quot;updated_at&quot;:&quot;2025-09-04T06:56:08.000000Z&quot;,&quot;max_students&quot;:8}},{&quot;id&quot;:83,&quot;lesson_id&quot;:3,&quot;date_time&quot;:&quot;2025-09-06 14:00:00&quot;,&quot;location&quot;:&quot;nk&quot;,&quot;is_completed&quot;:0,&quot;is_active&quot;:1,&quot;cancelled&quot;:0,&quot;tenant_id&quot;:&quot;10&quot;,&quot;student&quot;:[],&quot;lesson&quot;:{&quot;id&quot;:3,&quot;lesson_name&quot;:&quot;kkjk&quot;,&quot;lesson_description&quot;:&quot;&lt;p&gt;ghjg&lt;\/p&gt;\r\n&quot;,&quot;lesson_price&quot;:&quot;0.00&quot;,&quot;lesson_duration&quot;:0.5,&quot;tenant_id&quot;:&quot;10&quot;,&quot;lesson_quantity&quot;:1,&quot;required_time&quot;:0,&quot;created_by&quot;:2,&quot;active_status&quot;:1,&quot;type&quot;:&quot;package&quot;,&quot;is_package_lesson&quot;:1,&quot;payment_method&quot;:&quot;cash&quot;,&quot;created_at&quot;:&quot;2025-09-04T06:40:49.000000Z&quot;,&quot;updated_at&quot;:&quot;2025-09-04T06:56:08.000000Z&quot;,&quot;max_students&quot;:8}},{&quot;id&quot;:84,&quot;lesson_id&quot;:3,&quot;date_time&quot;:&quot;2025-09-06 14:30:00&quot;,&quot;location&quot;:&quot;nk&quot;,&quot;is_completed&quot;:0,&quot;is_active&quot;:1,&quot;cancelled&quot;:0,&quot;tenant_id&quot;:&quot;10&quot;,&quot;student&quot;:[],&quot;lesson&quot;:{&quot;id&quot;:3,&quot;lesson_name&quot;:&quot;kkjk&quot;,&quot;lesson_description&quot;:&quot;&lt;p&gt;ghjg&lt;\/p&gt;\r\n&quot;,&quot;lesson_price&quot;:&quot;0.00&quot;,&quot;lesson_duration&quot;:0.5,&quot;tenant_id&quot;:&quot;10&quot;,&quot;lesson_quantity&quot;:1,&quot;required_time&quot;:0,&quot;created_by&quot;:2,&quot;active_status&quot;:1,&quot;type&quot;:&quot;package&quot;,&quot;is_package_lesson&quot;:1,&quot;payment_method&quot;:&quot;cash&quot;,&quot;created_at&quot;:&quot;2025-09-04T06:40:49.000000Z&quot;,&quot;updated_at&quot;:&quot;2025-09-04T06:56:08.000000Z&quot;,&quot;max_students&quot;:8}},{&quot;id&quot;:85,&quot;lesson_id&quot;:3,&quot;date_time&quot;:&quot;2025-09-04 05:01:00&quot;,&quot;location&quot;:&quot;as&quot;,&quot;is_completed&quot;:0,&quot;is_active&quot;:1,&quot;cancelled&quot;:0,&quot;tenant_id&quot;:&quot;10&quot;,&quot;student&quot;:[{&quot;id&quot;:2,&quot;chat_user_id&quot;:&quot;68b93035e1239f76e98c5b08&quot;,&quot;uuid&quot;:&quot;9960d386-9706-4f0e-971e-4b907020765e&quot;,&quot;name&quot;:&quot;Alexandra Preston&quot;,&quot;email&quot;:&quot;student1@mailinator.com&quot;,&quot;tenant_id&quot;:&quot;10&quot;,&quot;type&quot;:&quot;Student&quot;,&quot;isGuest&quot;:0,&quot;active_status&quot;:1,&quot;country_code&quot;:&quot;&quot;,&quot;dial_code&quot;:&quot;&quot;,&quot;phone&quot;:&quot;+1(383)535-1537&quot;,&quot;dp&quot;:null,&quot;created_by&quot;:&quot;signup&quot;,&quot;email_verified_at&quot;:&quot;2025-09-04T06:22:43.000000Z&quot;,&quot;phone_verified_at&quot;:&quot;2025-09-04T06:22:43.000000Z&quot;,&quot;bio&quot;:null,&quot;stripe_cus_id&quot;:null,&quot;remember_token&quot;:null,&quot;created_at&quot;:&quot;2025-09-04T06:22:43.000000Z&quot;,&quot;updated_at&quot;:&quot;2025-09-04T06:22:46.000000Z&quot;,&quot;social_url_ig&quot;:null,&quot;social_url_fb&quot;:null,&quot;social_url_x&quot;:null,&quot;plan_id&quot;:null,&quot;plan_expired_date&quot;:null,&quot;chat_status&quot;:0,&quot;group_id&quot;:&quot;68b93036e1239f76e98c5b0d&quot;,&quot;chat_enabled_by&quot;:null,&quot;pivot&quot;:{&quot;slot_id&quot;:85,&quot;student_id&quot;:2,&quot;isFriend&quot;:0,&quot;friend_name&quot;:null,&quot;created_at&quot;:&quot;2025-09-04T14:25:17.000000Z&quot;,&quot;updated_at&quot;:&quot;2025-09-04T14:25:17.000000Z&quot;}}],&quot;lesson&quot;:{&quot;id&quot;:3,&quot;lesson_name&quot;:&quot;kkjk&quot;,&quot;lesson_description&quot;:&quot;&lt;p&gt;ghjg&lt;\/p&gt;\r\n&quot;,&quot;lesson_price&quot;:&quot;0.00&quot;,&quot;lesson_duration&quot;:0.5,&quot;tenant_id&quot;:&quot;10&quot;,&quot;lesson_quantity&quot;:1,&quot;required_time&quot;:0,&quot;created_by&quot;:2,&quot;active_status&quot;:1,&quot;type&quot;:&quot;package&quot;,&quot;is_package_lesson&quot;:1,&quot;payment_method&quot;:&quot;cash&quot;,&quot;created_at&quot;:&quot;2025-09-04T06:40:49.000000Z&quot;,&quot;updated_at&quot;:&quot;2025-09-04T06:56:08.000000Z&quot;,&quot;max_students&quot;:8}},{&quot;id&quot;:86,&quot;lesson_id&quot;:3,&quot;date_time&quot;:&quot;2025-09-04 05:31:00&quot;,&quot;location&quot;:&quot;as&quot;,&quot;is_completed&quot;:0,&quot;is_active&quot;:1,&quot;cancelled&quot;:0,&quot;tenant_id&quot;:&quot;10&quot;,&quot;student&quot;:[],&quot;lesson&quot;:{&quot;id&quot;:3,&quot;lesson_name&quot;:&quot;kkjk&quot;,&quot;lesson_description&quot;:&quot;&lt;p&gt;ghjg&lt;\/p&gt;\r\n&quot;,&quot;lesson_price&quot;:&quot;0.00&quot;,&quot;lesson_duration&quot;:0.5,&quot;tenant_id&quot;:&quot;10&quot;,&quot;lesson_quantity&quot;:1,&quot;required_time&quot;:0,&quot;created_by&quot;:2,&quot;active_status&quot;:1,&quot;type&quot;:&quot;package&quot;,&quot;is_package_lesson&quot;:1,&quot;payment_method&quot;:&quot;cash&quot;,&quot;created_at&quot;:&quot;2025-09-04T06:40:49.000000Z&quot;,&quot;updated_at&quot;:&quot;2025-09-04T06:56:08.000000Z&quot;,&quot;max_students&quot;:8}},{&quot;id&quot;:87,&quot;lesson_id&quot;:3,&quot;date_time&quot;:&quot;2025-09-05 05:01:00&quot;,&quot;location&quot;:&quot;as&quot;,&quot;is_completed&quot;:0,&quot;is_active&quot;:1,&quot;cancelled&quot;:0,&quot;tenant_id&quot;:&quot;10&quot;,&quot;student&quot;:[],&quot;lesson&quot;:{&quot;id&quot;:3,&quot;lesson_name&quot;:&quot;kkjk&quot;,&quot;lesson_description&quot;:&quot;&lt;p&gt;ghjg&lt;\/p&gt;\r\n&quot;,&quot;lesson_price&quot;:&quot;0.00&quot;,&quot;lesson_duration&quot;:0.5,&quot;tenant_id&quot;:&quot;10&quot;,&quot;lesson_quantity&quot;:1,&quot;required_time&quot;:0,&quot;created_by&quot;:2,&quot;active_status&quot;:1,&quot;type&quot;:&quot;package&quot;,&quot;is_package_lesson&quot;:1,&quot;payment_method&quot;:&quot;cash&quot;,&quot;created_at&quot;:&quot;2025-09-04T06:40:49.000000Z&quot;,&quot;updated_at&quot;:&quot;2025-09-04T06:56:08.000000Z&quot;,&quot;max_students&quot;:8}},{&quot;id&quot;:88,&quot;lesson_id&quot;:3,&quot;date_time&quot;:&quot;2025-09-05 05:31:00&quot;,&quot;location&quot;:&quot;as&quot;,&quot;is_completed&quot;:0,&quot;is_active&quot;:1,&quot;cancelled&quot;:0,&quot;tenant_id&quot;:&quot;10&quot;,&quot;student&quot;:[],&quot;lesson&quot;:{&quot;id&quot;:3,&quot;lesson_name&quot;:&quot;kkjk&quot;,&quot;lesson_description&quot;:&quot;&lt;p&gt;ghjg&lt;\/p&gt;\r\n&quot;,&quot;lesson_price&quot;:&quot;0.00&quot;,&quot;lesson_duration&quot;:0.5,&quot;tenant_id&quot;:&quot;10&quot;,&quot;lesson_quantity&quot;:1,&quot;required_time&quot;:0,&quot;created_by&quot;:2,&quot;active_status&quot;:1,&quot;type&quot;:&quot;package&quot;,&quot;is_package_lesson&quot;:1,&quot;payment_method&quot;:&quot;cash&quot;,&quot;created_at&quot;:&quot;2025-09-04T06:40:49.000000Z&quot;,&quot;updated_at&quot;:&quot;2025-09-04T06:56:08.000000Z&quot;,&quot;max_students&quot;:8}},{&quot;id&quot;:89,&quot;lesson_id&quot;:3,&quot;date_time&quot;:&quot;2025-09-06 05:01:00&quot;,&quot;location&quot;:&quot;as&quot;,&quot;is_completed&quot;:0,&quot;is_active&quot;:1,&quot;cancelled&quot;:0,&quot;tenant_id&quot;:&quot;10&quot;,&quot;student&quot;:[],&quot;lesson&quot;:{&quot;id&quot;:3,&quot;lesson_name&quot;:&quot;kkjk&quot;,&quot;lesson_description&quot;:&quot;&lt;p&gt;ghjg&lt;\/p&gt;\r\n&quot;,&quot;lesson_price&quot;:&quot;0.00&quot;,&quot;lesson_duration&quot;:0.5,&quot;tenant_id&quot;:&quot;10&quot;,&quot;lesson_quantity&quot;:1,&quot;required_time&quot;:0,&quot;created_by&quot;:2,&quot;active_status&quot;:1,&quot;type&quot;:&quot;package&quot;,&quot;is_package_lesson&quot;:1,&quot;payment_method&quot;:&quot;cash&quot;,&quot;created_at&quot;:&quot;2025-09-04T06:40:49.000000Z&quot;,&quot;updated_at&quot;:&quot;2025-09-04T06:56:08.000000Z&quot;,&quot;max_students&quot;:8}},{&quot;id&quot;:90,&quot;lesson_id&quot;:3,&quot;date_time&quot;:&quot;2025-09-06 05:31:00&quot;,&quot;location&quot;:&quot;as&quot;,&quot;is_completed&quot;:0,&quot;is_active&quot;:1,&quot;cancelled&quot;:0,&quot;tenant_id&quot;:&quot;10&quot;,&quot;student&quot;:[],&quot;lesson&quot;:{&quot;id&quot;:3,&quot;lesson_name&quot;:&quot;kkjk&quot;,&quot;lesson_description&quot;:&quot;&lt;p&gt;ghjg&lt;\/p&gt;\r\n&quot;,&quot;lesson_price&quot;:&quot;0.00&quot;,&quot;lesson_duration&quot;:0.5,&quot;tenant_id&quot;:&quot;10&quot;,&quot;lesson_quantity&quot;:1,&quot;required_time&quot;:0,&quot;created_by&quot;:2,&quot;active_status&quot;:1,&quot;type&quot;:&quot;package&quot;,&quot;is_package_lesson&quot;:1,&quot;payment_method&quot;:&quot;cash&quot;,&quot;created_at&quot;:&quot;2025-09-04T06:40:49.000000Z&quot;,&quot;updated_at&quot;:&quot;2025-09-04T06:56:08.000000Z&quot;,&quot;max_students&quot;:8}},{&quot;id&quot;:91,&quot;lesson_id&quot;:3,&quot;date_time&quot;:&quot;2025-09-07 05:01:00&quot;,&quot;location&quot;:&quot;as&quot;,&quot;is_completed&quot;:0,&quot;is_active&quot;:1,&quot;cancelled&quot;:0,&quot;tenant_id&quot;:&quot;10&quot;,&quot;student&quot;:[],&quot;lesson&quot;:{&quot;id&quot;:3,&quot;lesson_name&quot;:&quot;kkjk&quot;,&quot;lesson_description&quot;:&quot;&lt;p&gt;ghjg&lt;\/p&gt;\r\n&quot;,&quot;lesson_price&quot;:&quot;0.00&quot;,&quot;lesson_duration&quot;:0.5,&quot;tenant_id&quot;:&quot;10&quot;,&quot;lesson_quantity&quot;:1,&quot;required_time&quot;:0,&quot;created_by&quot;:2,&quot;active_status&quot;:1,&quot;type&quot;:&quot;package&quot;,&quot;is_package_lesson&quot;:1,&quot;payment_method&quot;:&quot;cash&quot;,&quot;created_at&quot;:&quot;2025-09-04T06:40:49.000000Z&quot;,&quot;updated_at&quot;:&quot;2025-09-04T06:56:08.000000Z&quot;,&quot;max_students&quot;:8}},{&quot;id&quot;:92,&quot;lesson_id&quot;:3,&quot;date_time&quot;:&quot;2025-09-07 05:31:00&quot;,&quot;location&quot;:&quot;as&quot;,&quot;is_completed&quot;:0,&quot;is_active&quot;:1,&quot;cancelled&quot;:0,&quot;tenant_id&quot;:&quot;10&quot;,&quot;student&quot;:[],&quot;lesson&quot;:{&quot;id&quot;:3,&quot;lesson_name&quot;:&quot;kkjk&quot;,&quot;lesson_description&quot;:&quot;&lt;p&gt;ghjg&lt;\/p&gt;\r\n&quot;,&quot;lesson_price&quot;:&quot;0.00&quot;,&quot;lesson_duration&quot;:0.5,&quot;tenant_id&quot;:&quot;10&quot;,&quot;lesson_quantity&quot;:1,&quot;required_time&quot;:0,&quot;created_by&quot;:2,&quot;active_status&quot;:1,&quot;type&quot;:&quot;package&quot;,&quot;is_package_lesson&quot;:1,&quot;payment_method&quot;:&quot;cash&quot;,&quot;created_at&quot;:&quot;2025-09-04T06:40:49.000000Z&quot;,&quot;updated_at&quot;:&quot;2025-09-04T06:56:08.000000Z&quot;,&quot;max_students&quot;:8}}], 'package', 1 ,'0.00', 3)">
                                                Purchase
                                            </button>


                                        </div>
                                    </div>
                                    <form id="bookingForm" method="POST"
                                        action="https://t1.collegegolfrecruitingportal.com/lesson/slot/booking?redirect=1">
                                        <input type="hidden" name="_token"
                                            value="Kk6k2iEnrgODkVya6l2oUYiM43GdKQZLfH4fbkSx"> <input type="hidden"
                                            id="packagePrice" name="package_price">
                                        <input type="hidden" id="slotIdInput" name="slot_id">
                                        <input type="hidden" id="friendNamesInput" name="friend_names">
                                    </form>
                                </div>


                            </div>
                        @endif
                    @empty
                        <h3>No Lessons Found</h3>
                    @endforelse
                    {{--  <div class="col-md-3">
                        <div class=" bg-gray rounded-lg shadow h-100  flex flex-col">
                            <div class="relative text-center p-3 flex gap-3">
                                <img src="https://tune-golf.nyc3.digitaloceanspaces.com/fitnessgoals.tuneupclub.com/uploads/avatar/influencer/2/1750961833.png"
                                    alt="Sarah Smith"
                                    class="hover:shadow-lg cursor-pointer rounded-lg h-32 w-24 object-cover">
                                <div class="text-left">
                                    <a class="font-bold text-dark text-xl" href="https://fitnessgoals.tuneupclub.com/login"
                                        tabindex="0">
                                        Sarah Smith
                                    </a>
                                    <div class="text-lg font-bold tracking-tight text-primary">
                                        $ 50.00 (USD)
                                    </div>
                                </div>
                            </div>

                            <div class="px-3 pb-4 mt-1 flex flex-col flex-grow">
                                <span class="text-xl font-semibold text-dark">Putting Lesson</span>
                                <div class="description-wrapper relative expanded">
                                    <div class="short-text break-all clamp-text" id="lessonDesc">
                                        <ul>
                                            <li>Upload a video from down-the-line and face-on</li>
                                            <li>Breakdown of stroke path, face angle, and rhythm</li>
                                            <li>Simple fixes and drills to improve consistency</li>
                                        </ul>

                                    </div>
                                    <a href="#" class="read-toggle text-blue-600 font-medium mt-1 inline-block"
                                        onclick="toggleRead(this); return false;" tabindex="0">&lt;&lt; Read Less</a>
                                </div>

                                <div class="mt-2 bg-gray-200 gap-2 rounded-lg px-4 py-3">
                                    <div class="text-center">
                                        <span class="text-xl font-bold">2 Days</span>
                                        <div class="text-sm rtl:space-x-reverse">Expected Response Time</div>
                                    </div>
                                </div>
                                <div class="w-100 mt-3">

                                    <a href="https://fitnessgoals.tuneupclub.com/login" tabindex="0">
                                        <button type="submit" class="lesson-btn py-2" tabindex="0">Purchase</button>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class=" bg-gray rounded-lg shadow h-100  flex flex-col">
                            <div class="relative text-center p-3 flex gap-3">
                                <img src="https://tune-golf.nyc3.digitaloceanspaces.com/fitnessgoals.tuneupclub.com/uploads/avatar/influencer/2/1750961833.png"
                                    alt="Sarah Smith"
                                    class="hover:shadow-lg cursor-pointer rounded-lg h-32 w-24 object-cover">
                                <div class="text-left">
                                    <a class="font-bold text-dark text-xl" href="https://fitnessgoals.tuneupclub.com/login"
                                        tabindex="0">
                                        Sarah Smith
                                    </a>
                                    <div class="text-lg font-bold tracking-tight text-primary">
                                        $ 50.00 (USD)
                                    </div>
                                </div>
                            </div>

                            <div class="px-3 pb-4 mt-1 flex flex-col flex-grow">
                                <span class="text-xl font-semibold text-dark">Putting Lesson</span>
                                <div class="description-wrapper relative expanded">
                                    <div class="short-text break-all clamp-text" id="lessonDesc">
                                        <ul>
                                            <li>Upload a video from down-the-line and face-on</li>
                                            <li>Breakdown of stroke path, face angle, and rhythm</li>
                                            <li>Simple fixes and drills to improve consistency</li>
                                        </ul>

                                    </div>
                                    <a href="#" class="read-toggle text-blue-600 font-medium mt-1 inline-block"
                                        onclick="toggleRead(this); return false;" tabindex="0">&lt;&lt; Read Less</a>
                                </div>

                                <div class="mt-2 bg-gray-200 gap-2 rounded-lg px-4 py-3">
                                    <div class="text-center">
                                        <span class="text-xl font-bold">2 Days</span>
                                        <div class="text-sm rtl:space-x-reverse">Expected Response Time</div>
                                    </div>
                                </div>
                                <div class="w-100 mt-3">

                                    <a href="https://fitnessgoals.tuneupclub.com/login" tabindex="0">
                                        <button type="submit" class="lesson-btn py-2" tabindex="0">Purchase</button>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class=" bg-gray rounded-lg shadow h-100  flex flex-col">
                            <div class="relative text-center p-3 flex gap-3">
                                <img src="https://tune-golf.nyc3.digitaloceanspaces.com/fitnessgoals.tuneupclub.com/uploads/avatar/influencer/2/1750961833.png"
                                    alt="Sarah Smith"
                                    class="hover:shadow-lg cursor-pointer rounded-lg h-32 w-24 object-cover">
                                <div class="text-left">
                                    <a class="font-bold text-dark text-xl"
                                        href="https://fitnessgoals.tuneupclub.com/login" tabindex="0">
                                        Sarah Smith
                                    </a>
                                    <div class="text-lg font-bold tracking-tight text-primary">
                                        $ 50.00 (USD)
                                    </div>
                                </div>
                            </div>

                            <div class="px-3 pb-4 mt-1 flex flex-col flex-grow">
                                <span class="text-xl font-semibold text-dark">Putting Lesson</span>
                                <div class="description-wrapper relative expanded">
                                    <div class="short-text break-all clamp-text" id="lessonDesc">
                                        <ul>
                                            <li>Upload a video from down-the-line and face-on</li>
                                            <li>Breakdown of stroke path, face angle, and rhythm</li>
                                            <li>Simple fixes and drills to improve consistency</li>
                                        </ul>

                                    </div>
                                    <a href="#" class="read-toggle text-blue-600 font-medium mt-1 inline-block"
                                        onclick="toggleRead(this); return false;" tabindex="0">&lt;&lt; Read Less</a>
                                </div>

                                <div class="mt-2 bg-gray-200 gap-2 rounded-lg px-4 py-3">
                                    <div class="text-center">
                                        <span class="text-xl font-bold">2 Days</span>
                                        <div class="text-sm rtl:space-x-reverse">Expected Response Time</div>
                                    </div>
                                </div>
                                <div class="w-100 mt-3">

                                    <a href="https://fitnessgoals.tuneupclub.com/login" tabindex="0">
                                        <button type="submit" class="lesson-btn py-2" tabindex="0">Purchase</button>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class=" bg-gray rounded-lg shadow h-100  flex flex-col">
                            <div class="relative text-center p-3 flex gap-3">
                                <img src="https://tune-golf.nyc3.digitaloceanspaces.com/fitnessgoals.tuneupclub.com/uploads/avatar/influencer/2/1750961833.png"
                                    alt="Sarah Smith"
                                    class="hover:shadow-lg cursor-pointer rounded-lg h-32 w-24 object-cover">
                                <div class="text-left">
                                    <a class="font-bold text-dark text-xl"
                                        href="https://fitnessgoals.tuneupclub.com/login" tabindex="0">
                                        Sarah Smith
                                    </a>
                                    <div class="text-lg font-bold tracking-tight text-primary">
                                        $ 50.00 (USD)
                                    </div>
                                </div>
                            </div>

                            <div class="px-3 pb-4 mt-1 flex flex-col flex-grow">
                                <span class="text-xl font-semibold text-dark">Putting Lesson</span>
                                <div class="description-wrapper relative expanded">
                                    <div class="short-text break-all clamp-text" id="lessonDesc">
                                        <ul>
                                            <li>Upload a video from down-the-line and face-on</li>
                                            <li>Breakdown of stroke path, face angle, and rhythm</li>
                                            <li>Simple fixes and drills to improve consistency</li>
                                        </ul>

                                    </div>
                                    <a href="#" class="read-toggle text-blue-600 font-medium mt-1 inline-block"
                                        onclick="toggleRead(this); return false;" tabindex="0">&lt;&lt; Read Less</a>
                                </div>

                                <div class="mt-2 bg-gray-200 gap-2 rounded-lg px-4 py-3">
                                    <div class="text-center">
                                        <span class="text-xl font-bold">2 Days</span>
                                        <div class="text-sm rtl:space-x-reverse">Expected Response Time</div>
                                    </div>
                                </div>
                                <div class="w-100 mt-3">

                                    <a href="https://fitnessgoals.tuneupclub.com/login" tabindex="0">
                                        <button type="submit" class="lesson-btn py-2" tabindex="0">Purchase</button>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>  --}}
                </div>
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
                                {{--  <div class="py-2">
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
                                </div>  --}}
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </section>
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
    </style>
@endpush

@push('javascript')
    <script>
        console.log("Popup JS Loaded");

        function openInstructorPopup(name, imageSrc, bio, lessons = [], currency = '') {
            console.log("Popup clicked:", name);

            // Fill content
            document.getElementById('popupInstructorName').textContent = name;
            document.getElementById('popupInstructorImage').src = imageSrc;
            document.getElementById('popupInstructorImage').alt = name;
            document.getElementById('popupInstructorBio').textContent = bio;

            // Render lessons
            const lessonsContainer = document.getElementById('popupInstructorLessons');
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
            }

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
