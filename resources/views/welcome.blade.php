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
                                        <img src="{{ Utility::getsettings('app_logo') ? Utility::getpath('logo/app-logo.png') : asset('assets/images/app-logo.png') }}"
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
                            @php
                                $slots = 0;
                                $slots += $lesson?->packages->sum('number_of_slot');
                                $packages_array = [];
                                foreach ($lesson?->packages as $package) {
                                    $packages_array[] =
                                        $package->number_of_slot . ' Lesson ' . ' - ' . $package->price . ' USD ';
                                }
                            @endphp
                            <div class="col-md-4">
                                <div
                                    class="w-full bg-gray border border-gray-200 rounded-lg shadow dark:bg-gray-800 dark:border-gray-700 flex flex-col h-full">
                                    <div class="relative text-center p-3 flex gap-3">

                                        <img src="{{ Utility::getsettings('app_logo') ? Utility::getpath('logo/app-logo.png') : asset('assets/images/app-logo.png') }}"
                                            class="hover:shadow-lg cursor-pointer rounded-lg h-32 w-24 object-cover">
                                        <div class="text-left">
                                            <a class="font-bold text-dark text-xl" href="{{ route('login') }}">
                                                {{ $instructors[0]->name }}
                                            </a>
                                            <div class="text-lg font-bold tracking-tight text-primary">
                                                $ 0 (USD)
                                                <p>{{ $slots }} Slots available.</p>
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
                                        <div
                                            class="font-thin text-gray-600 overflow-hidden whitespace-nowrap overflow-ellipsis ck-content">
                                            <p>{!! $lesson?->lesson_description !!}</p>

                                        </div>

                                        <div class="mb-3 p-3 border rounded-lg shadow-sm bg-white">
                                            <p class="text-sm text-gray-500 mb-3">Save more with multi-lesson packages</p>
                                            <select name="package_slot"
                                                class="no-nice-select w-full border rounded-lg p-2 text-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                                                <option value="--">Select Option</option>
                                                @foreach ($packages_array as $package)
                                                    <option value="{{ $package }}">{{ $package }}</option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <div class="w-100 mt-3">

                                            <a class="lesson-btn text-center" href="{{ route('login') }}">
                                                Purchase
                                            </a>


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
    <script src="{{ asset('assets/js/jquery.min.js') }}"></script>

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
