@php
    $languages = \App\Facades\UtilityFacades::languages();
    $currency = tenancy()->central(function ($tenant) {
    return Utility::getsettings('currency_symbol');
    });
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
        <img class="w-full" src="{{ $instructorDetails->banner_image ?? asset('assets/images/landing-page-images/banner1.png') }}" alt="hero-banner">
    </div>
</section>
<section class="lession-sec">
    <div class="container ctm-container">
        <h2 class="font-bold text-4xl mb-2">{{ $instructorDetails->name }}</h2>
        <p class="text-xl max-w-2xl text-gray-600">{{ $instructorDetails->bio }}</p>
    </div>
    <div class="container-fluid lessions-slider pt-5">
        @if(!$instructorDetails->lessons->isEmpty())
            @foreach ($instructorDetails->lessons as $lesson)
                @if($lesson->active_status)
                    <div class="px-3 py-4">
                        <div class=" bg-white rounded-lg shadow h-100  flex flex-col">
                            <div class="relative text-center p-3 flex gap-3">
                                <img src="{{ asset('storage/'.tenant()->id.'/'.$instructorDetails->avatar) }}"
                                    alt="{{ $instructorDetails->name }}"
                                    class="hover:shadow-lg cursor-pointer rounded-lg h-32 w-24 object-cover">
                                <div class="text-left">
                                    <a class="font-bold text-dark text-xl"
                                        href="{{ route('login') }}">
                                        {{ $instructorDetails->name }}
                                    </a>
                                    <div class="text-lg font-bold tracking-tight text-primary">
                                        {{ $currency}} {{ $lesson->lesson_price }} (USD)
                                    </div>
                                </div>
                            </div>

                            <div class="px-3 pb-4 mt-1 flex flex-col flex-grow">
                                <span class="text-xl font-semibold text-dark">{{ $lesson->lesson_name }}</span>
                                <div class="description-wrapper relative">
                                    <div class="short-text break-all clamp-text" id="lessonDesc">
                                        {!! $lesson->lesson_description !!}
                                    </div>
                                    <a href="#" class="read-toggle text-blue-600 font-medium mt-1 inline-block" onclick="toggleRead(this); return false;">
                                        ...Read More >>
                                    </a>
                                </div>

                                <div class="mt-2 bg-gray-200 gap-2 rounded-lg px-4 py-3">
                                    <div class="text-center">
                                        <span class="text-xl font-bold">{{ $lesson->required_time }} Days</span>
                                        <div class="text-sm rtl:space-x-reverse">Expected Response Time</div>
                                    </div>
                                </div>
                                <div class="w-100 mt-3">
                                    {{-- <form method="POST"
                                        action="{{ route('login') }}"
                                        accept-charset="UTF-8" enctype="multipart/form-data" class="form-horizontal"
                                        data-validate="" novalidate="true"><input name="_token" type="hidden"
                                            value="0DKCSNAoSKqudQ5rlJIX6LimpNfZ5JMl0QoWqaGH">
                                        <button type="submit" class="lesson-btn py-2">Purchase</button>
                                    </form> --}}
                                    <a href="{{ route('login') }}">
                                        <button type="submit" class="lesson-btn py-2">Purchase</button>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            @endforeach
        @endif
    </div>
</section>

<section class="lession-sec subscription-sec">
    <div class="container ctm-container">
        <h2 class="font-bold text-4xl mb-2">Subscription Plans</h2>
        <p class="text-xl text-gray-600">
            Subscription plans give you full access to your coach's posts, training content, and the ability to connect directly.
        </p>
        <div class="subscription-slider pt-5">
            @if(!$plans->isEmpty())
                @foreach($plans as $plan)
                    <div class="px-3 py-4">
                        <div class="bg-white subs-feature rounded-lg shadow popular-wrap position-relative h-100">
                            @if($plan->is_chat_enabled && $plan->is_feed_enabled)
                                <div class="rounded-pill px-4 py-2 popular-plan w-auto bg-primary text-white font-bold position-absolute" style="top: -22px; left: 50%; transform: translateX(-50%);">
                                    POPULAR
                                </div>
                            @endif
                            <div class="relative px-3 py-4  flex flex-col">
                                <p class="text-3xl font-bold mb-1">{{ $plan->name }}</p>
                                <div class="flex gap-2 items-center my-2 ">
                                    <p class=" text-6xl font-bold">{{ $currency . ' ' . $plan->price }} /</p>
                                    <p class="text-2xl text-gray-600">{{ $plan->duration . ' ' . $plan->durationtype }}</p>
                                    
                                </div>
                                <a href="{{ route('login') }}" class="lesson-btn text-center font-bold text-lg mt-auto">
                                    Purchase
                                </a>
                            </div>
                            <div class="border-t border-gray-300"></div>
                            <div class="p-3">
                                <p class="font-semibold text-xl mb-2">Features</p>
                                <p class="text-gray-600">
                                {!! $plan->description !!}
                                </p>
                            </div>
                        </div>
                    </div>
                @endforeach
            @endif
        </div>
    </div>
</section>

<section class="lession-sec feed-sec">
    <div class="container ctm-container">
        <h2 class="font-bold text-4xl mb-2">Feed</h2>

        <div class="flex flex-wrap gap-5 w-100">
            @if(!$instructorDetails->post->isEmpty())
                @foreach ($instructorDetails->post as $post)
                    <div class="max-w-sm w-full">
                        <div class="shadow rounded-2 overflow-hidden position-relative">
                            @if($post->paid && !isset($purchasePost))
                                <?php $cls  = 'p-3 position-absolute left-0 top-0 z-1 w-full'; ?>
                            @else
                                <?php $cls  = 'p-3 position-absolute left-0 top-0 z-1 w-full custom-gradient'; ?>
                            @endif
                            <div class="{{ $cls }}">
                                <div class="flex justify-between items-center w-full">
                                    <div class="flex items-center gap-3">
                                        <img class="w-16 h-16 rounded-full"
                                            src="{{ asset('storage/'.tenant()->id.'/'.$instructorDetails->avatar) }}"
                                            alt="Profile">
                                        <div>
                                            <p class="text-xl text-white font-bold mb-0 leading-tight">
                                                {{ $instructorDetails->name }}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @if ($post->file_type == 'image')
                                <div class="relative paid-post-wrap">
                                    <img class=" w-full post-thumbnail"
                                        src="{{ asset('storage/'.tenant()->id.'/'.$post->file) }}">
                                    @if($post->paid)
                                        <div class="absolute inset-0 flex justify-center items-center paid-post flex-col">
                                            <div
                                                class="ctm-icon-box bg-white rounded-full text-primary w-24 h-24 text-7xl flex items-center justify-content-center text-center border border-5 mb-3">
                                                <i class="ti ti-lock-open"></i>
                                            </div>
                                            <a href="{{ route('login') }}">
                                                <div
                                                    class="bg-orange text-white px-4 py-1 rounded-3xl w-full text-center flex items-center justify-center gap-1">
                                                    <i class="ti ti-lock-open text-2xl lh-sm"></i>
                                                    <button type="submit" class="btn p-0 pl-1 text-white border-0">Unlock for -
                                                        {{ $currency . ' ' . $post->price }}</button>
                                                </div>
                                            </a>
                                        </div>
                                    @endif
                                </div>
                            @else
                                @if ($post->paid && !isset($purchasePost))
                                    <div class="relative paid-post-wrap">
                                        <video class="w-full post-thumbnail pointer-events-none opacity-50">
                                            <source src="{{ asset('storage/'.tenant()->id.'/'.$post->file) }}" type="video/mp4">
                                        </video>
                                        <div class="absolute inset-0 flex justify-center items-center paid-post flex-col">
                                            <div
                                                class="ctm-icon-box bg-white rounded-full text-primary w-24 h-24 text-7xl flex items-center justify-content-center text-center border border-5 mb-3">
                                                <i class="ti ti-lock-open"></i>
                                            </div>

                                            <a href="{{ route('login') }}">
                                                <div
                                                    class="bg-orange text-white px-4 py-1 rounded-3xl w-full text-center flex items-center justify-center gap-1">
                                                    <i class="ti ti-lock-open text-2xl lh-sm"></i>
                                                    <button type="submit" class="btn p-0 pl-1 text-white border-0">Unlock for -
                                                        {{ $currency . ' ' . $post->price }}</button>
                                                </div>
                                            </a>
                                        </div>
                                    </div>
                                @else
                                    <video controls class="w-full post-thumbnail">
                                        <source src="{{ asset('storage/'.tenant()->id.'/'.$post->file) }}" type="video/mp4">
                                    </video>
                                @endif
                            @endif


                            <div class="px-4 py-2 border-t border-gray-500">
                                <h1 class="text-xl font-bold truncate">
                                    {{ $post->title }}
                                </h1>
                                <div class="description-wrapper relative">
                                    <div class="short-text clamp-text text-gray-500 text-md mt-1 font-medium">
                                        {!! $post->description !!}
                                    </div>
                                    <a href="#" class="read-toggle text-blue-600 font-medium mt-1 inline-block" onclick="toggleRead(this); return false;">
                                        ...Read More >>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            @endif
        </div>
    </div>
</section>

<footer class="foot mt-0">
    <div class="text-center container ctm-container footer-one">
        <div class="flex justify-center">
            <img src="{{ asset('assets/images/landing-page-images/logo-1.png') }}" class="img-fluid" alt="" />
        </div>
    </div>
</footer>
<footer class="foot-two">
    <div class="flex justify-content-sm-between justify-center align-items-center container footer-two">
        <div class="text-white m-0">
            <p class="fot-p">© 2025 Tuneup. All rights reserved.</p>
        </div>
        <div class="icon flex mt-2 sm-mt-0 text-3xl flex gap-3">
            <a href="{{ $instructorDetails->social_url_fb }}" class="text-gray-800"><i class="ti ti-brand-facebook"></i></a>
            <a href="{{ $instructorDetails->social_url_x }}" class="text-gray-800"><i class="ti ti-brand-twitter"></i></a>
            <a href="{{ $instructorDetails->social_url_ig }}" class="text-gray-800"><i class="ti ti-brand-instagram"></i></a>
            <a href="{{ $instructorDetails->social_url_yt }}" class="text-gray-800"><i class="ti ti-brand-youtube"></i></a>
            <a href="{{ $instructorDetails->social_url_ln }}" class="text-gray-800"><i class="ti ti-brand-linkedin"></i></a>
        </div>
    </div>
</footer>
@endsection
@push('css')
    <style>
        .lessions-slider  .slick-track
        {
            display: flex !important;
        }

        .lessions-slider  .slick-slide
        {
            height: inherit !important;
        }
    </style>
@endpush
