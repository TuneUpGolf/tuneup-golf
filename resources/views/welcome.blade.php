@php
$languages = \App\Facades\UtilityFacades::languages();
$currency = tenancy()->central(function ($tenant) {
return Utility::getsettings('currency_symbol');
});
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
      <img class="w-full" src="{{ $banner != '' ? $banner : asset('assets/images/landing-page-images/banner1.png') }}" alt="hero-banner">
   </div>
</section>
<section class="lession-sec feed-sec">
   <div class="container ctm-container">
      <h2 class="font-bold text-4xl mb-2">Instructors</h2>
      <div class="flex flex-wrap gap-5 w-full mt-10">
         @if(!$instructors->isEmpty())
            @foreach ($instructors as $instructor)
            <div class="max-w-sm flex flex-col items-center text-center">
                <a href="{{ url('/login') }}" title="{{ $instructor->name }}">
                    <img class="h-30 rounded-full"
                    src="{{ asset('storage/' . tenant()->id . '/' . $instructor->avatar) }}"
                    alt="Instructor Avatar">
                    <div class="py-2">
                        <h1 class="text-xl font-bold truncate">
                            {{ $instructor->name }}
                        </h1>
                    </div>
                </a>
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
         @if($socialUrlFb = Utility::getsettings('social_url_fb')) <a href="{{ $socialUrlFb }}" class="text-gray-800"><i class="ti ti-brand-facebook"></i></a>@endif
         @if($socialUrlX = Utility::getsettings('social_url_x')) <a href="{{ $socialUrlX }}" class="text-gray-800"><i class="ti ti-brand-twitter"></i></a>@endif
         @if($socialUrlIg = Utility::getsettings('social_url_ig')) <a href="{{ $socialUrlIg }}" class="text-gray-800"><i class="ti ti-brand-instagram"></i></a>@endif
         @if($socialUrlYt = Utility::getsettings('social_url_yt')) <a href="{{ $socialUrlYt }}" class="text-gray-800"><i class="ti ti-brand-youtube"></i></a>@endif
         @if($socialUrlLn = Utility::getsettings('social_url_ln')) <a href="{{ $socialUrlLn }}" class="text-gray-800"><i class="ti ti-brand-linkedin"></i></a>@endif
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