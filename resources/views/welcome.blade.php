@php
$languages = \App\Facades\UtilityFacades::languages();
$currency = tenancy()->central(function ($tenant) {
return Utility::getsettings('currency_symbol');
});
$tenantId = tenant("id");
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
            @php
               $imgSrc = $instructor->avatar == "storage/$tenantId/logo/app-favicon-logo.png"?
               asset("storage/$tenantId/logo/app-favicon-logo.png") :
               asset('storage/' . $tenantId . '/' . $instructor->avatar);
            @endphp
            <div class="flex flex-col items-center text-center">
                <a href="{{ url('/login') }}" title="{{ $instructor->name }}">
                    <img class="custom-instructor-avatar rounded"
                    src="{{ $imgSrc }}"
                    alt="Instructor Avatar">
                    <h1 class="text-xl font-bold truncate mt-2">
                           {{ $instructor->name }}
                     </h1>
                  </a>
                  <div class="py-2">
                     <button
                           class="read-more-btn text-blue-600 hover:text-blue-800 underline text-sm"
                           onclick="openInstructorPopup('{{ $instructor->name }}', '{{ $imgSrc }}', '{{ addslashes($instructor->bio) }}')"
                     >
                           View Bio
                     </button>
                  </div>
            </div>
            @endforeach
         @endif
      </div>
   </div>
</section>

<!-- Instructor Popup Modal -->
<div id="instructorPopup" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-lg max-w-md w-full max-h-[90vh] overflow-y-auto overflow-x-hidden">
        <div class="relative">
            <!-- Close Button -->
            <button 
                onclick="closeInstructorPopup()" 
                class="absolute bg-gray-900 flex font-bold h-8 items-center justify-center m-2 right-2 rounded-full shadow-md text-2xl top-2 w-8 z-10"
            >
                ×
            </button>
            
            <!-- Instructor Image -->
            <div class="w-full overflow-hidden rounded-t-lg">
                <img id="popupInstructorImage" class="w-full h-auto max-h-64 object-contain" src="" alt="Instructor">
            </div>
            
            <!-- Instructor Info -->
            <div class="p-6">
                <h3 id="popupInstructorName" class="text-xl font-bold mb-3 text-gray-800"></h3>
                <p id="popupInstructorBio" class="text-gray-600 leading-relaxed"></p>
            </div>
        </div>
    </div>
</div>

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
   
   /* Popup Styles */
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
function openInstructorPopup(name, imageSrc, bio) {
    // Set popup content
    document.getElementById('popupInstructorName').textContent = name;
    document.getElementById('popupInstructorImage').src = imageSrc;
    document.getElementById('popupInstructorImage').alt = name;
    document.getElementById('popupInstructorBio').textContent = bio;
    
    // Show popup
    const popup = document.getElementById('instructorPopup');
    popup.classList.remove('hidden');
    popup.classList.add('show');
    
    // Prevent body scroll
    document.body.style.overflow = 'hidden';
}

function closeInstructorPopup() {
    const popup = document.getElementById('instructorPopup');
    popup.classList.add('hidden');
    popup.classList.remove('show');
    
    // Restore body scroll
    document.body.style.overflow = 'auto';
}

// Close popup when clicking outside
document.getElementById('instructorPopup').addEventListener('click', function(e) {
    if (e.target === this) {
        closeInstructorPopup();
    }
});

// Close popup with Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeInstructorPopup();
    }
});
</script>
@endpush