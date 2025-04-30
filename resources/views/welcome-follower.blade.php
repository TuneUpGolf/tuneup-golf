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
<link rel="stylesheet" href="http://tg.localhost/tuneup_golf/vendor/tailwind.css" />
<link rel="stylesheet" href="https://demo.collegegolfrecruitingportal.com/assets/css/customizer.css">
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
    <div class="hero-sec" style="background-image:url({{ asset('assets/images/landing-page-images/bg-1.png') }})">
        <div class="container ctm-container">
            <div class="d-sm-flex justify-content-between gap-5 align-items-center">
                <div class="master col-sm-7">
                    <h1 class="fw-bold text-white time display-5">
                        Vero accusamus digniss blanditiis
                    </h1>
                    <p class="text-2xl text-white hero-features mt-2 mb-4">
                        Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut
                        labore
                        et dolore magna aliqua.
                    </p>
                    <button class="btn btn-primary border-0 rounded-pill demo">
                        <a href="{{ url('request-demo') }}" style="text-decoration: none" class="text-white">
                            Explore More</a>
                    </button>
                </div>
                <div class="d-flex justify-content-end">
                    <img src="{{ asset('assets/images/landing-page-images/banner-mobile.png') }}"
                        class="w-auto img-fluid" alt="...">
                </div>
            </div>
        </div>
    </div>
</section>
<section class="lession-sec">
    <div class="container ctm-container">
        <h2 class="font-bold text-4xl mb-4">John Doe: Lesson Opportunities</h2>
        <div class="lessions-slider slick-slider">
            <div class="p-3">
                <div class=" bg-white rounded-lg shadow dark:bg-gray-800 dark:border-gray-700 flex flex-col">
                    <div class="relative text-center p-3 flex gap-3">
                        <img src="https://xn--kbenhavnercafeen-lxb.dk/wp-content/uploads/2025/03/Sourdough_Bread1.jpg"
                            alt="https://demo.collegegolfrecruitingportal.com/storage/5"
                            class="hover:shadow-lg cursor-pointer rounded-lg h-32 w-24 object-cover">
                        <div class="text-left">
                            <a class="font-bold text-dark text-xl"
                                href="https://demo.collegegolfrecruitingportal.com/instructor/profile/get?instructor_id=4">
                                Nikhil
                            </a>
                            <div class="text-lg font-bold tracking-tight text-primary">
                                $ 0.00 (USD)
                            </div>
                            <div class="text-sm font-medium text-gray-500 italic">
                                <span class="">(8 Purchased)</span>
                            </div>
                        </div>
                    </div>

                    <div class="px-3 pb-4 mt-1 flex flex-col flex-grow">
                        <div class="flex flex-row justify-between">

                        </div>

                        <span class="text-xl font-semibold text-dark">First Lesson</span>
                        <p class="font-thin text-gray-600 overflow-hidden whitespace-nowrap overflow-ellipsis">
                            First Lesson
                        </p>

                        <div class="mt-2 bg-gray-200 gap-1 rounded-lg px-4 py-3 flex">
                            <div class="text-center w-50">
                                <span class="text-xl font-bold">30</span>
                                <div class="text-sm rtl:space-x-reverse">Number of Lessons</div>

                            </div>
                            <div class="text-center w-50">
                                <span class="text-xl font-bold">30 Days</span>
                                <div class="text-sm rtl:space-x-reverse">Expected Response Time</div>

                            </div>

                        </div>

                        <div class="w-100 mt-3">
                            <form method="POST"
                                action="https://demo.collegegolfrecruitingportal.com/purchase/store?lesson_id=1"
                                accept-charset="UTF-8" enctype="multipart/form-data" class="form-horizontal"
                                data-validate="" novalidate="true"><input name="_token" type="hidden"
                                    value="0DKCSNAoSKqudQ5rlJIX6LimpNfZ5JMl0QoWqaGH">
                                <button type="submit" class="lesson-btn">Purchase</button>
                            </form>

                        </div>
                    </div>
                    <form id="bookingForm" method="POST"
                        action="https://demo.collegegolfrecruitingportal.com/lesson/slot/booking?redirect=1">
                        <input type="hidden" name="_token" value="0DKCSNAoSKqudQ5rlJIX6LimpNfZ5JMl0QoWqaGH"> <input
                            type="hidden" id="slotIdInput" name="slot_id">
                        <input type="hidden" id="friendNamesInput" name="friend_names">

                    </form>
                </div>
            </div>
            <div class="p-3">
                <div class=" bg-white rounded-lg shadow dark:bg-gray-800 dark:border-gray-700 flex flex-col">
                    <div class="relative text-center p-3 flex gap-3">
                        <img src="https://xn--kbenhavnercafeen-lxb.dk/wp-content/uploads/2025/03/Sourdough_Bread1.jpg"
                            alt="https://demo.collegegolfrecruitingportal.com/storage/5"
                            class="hover:shadow-lg cursor-pointer rounded-lg h-32 w-24 object-cover">
                        <div class="text-left">
                            <a class="font-bold text-dark text-xl"
                                href="https://demo.collegegolfrecruitingportal.com/instructor/profile/get?instructor_id=4">
                                Nikhil
                            </a>
                            <div class="text-lg font-bold tracking-tight text-primary">
                                $ 0.00 (USD)
                            </div>
                            <div class="text-sm font-medium text-gray-500 italic">
                                <span class="">(8 Purchased)</span>
                            </div>
                        </div>
                    </div>

                    <div class="px-3 pb-4 mt-1 flex flex-col flex-grow">
                        <div class="flex flex-row justify-between">

                        </div>

                        <span class="text-xl font-semibold text-dark">First Lesson</span>
                        <p class="font-thin text-gray-600 overflow-hidden whitespace-nowrap overflow-ellipsis">
                            First Lesson
                        </p>

                        <div class="mt-2 bg-gray-200 gap-1 rounded-lg px-4 py-3 flex">
                            <div class="text-center w-50">
                                <span class="text-xl font-bold">30</span>
                                <div class="text-sm rtl:space-x-reverse">Number of Lessons</div>

                            </div>
                            <div class="text-center w-50">
                                <span class="text-xl font-bold">30 Days</span>
                                <div class="text-sm rtl:space-x-reverse">Expected Response Time</div>

                            </div>

                        </div>

                        <div class="w-100 mt-3">
                            <form method="POST"
                                action="https://demo.collegegolfrecruitingportal.com/purchase/store?lesson_id=1"
                                accept-charset="UTF-8" enctype="multipart/form-data" class="form-horizontal"
                                data-validate="" novalidate="true"><input name="_token" type="hidden"
                                    value="0DKCSNAoSKqudQ5rlJIX6LimpNfZ5JMl0QoWqaGH">
                                <button type="submit" class="lesson-btn">Purchase</button>
                            </form>

                        </div>
                    </div>
                    <form id="bookingForm" method="POST"
                        action="https://demo.collegegolfrecruitingportal.com/lesson/slot/booking?redirect=1">
                        <input type="hidden" name="_token" value="0DKCSNAoSKqudQ5rlJIX6LimpNfZ5JMl0QoWqaGH"> <input
                            type="hidden" id="slotIdInput" name="slot_id">
                        <input type="hidden" id="friendNamesInput" name="friend_names">

                    </form>
                </div>
            </div>
            <div class="p-3">
                <div class=" bg-white rounded-lg shadow dark:bg-gray-800 dark:border-gray-700 flex flex-col">
                    <div class="relative text-center p-3 flex gap-3">
                        <img src="https://xn--kbenhavnercafeen-lxb.dk/wp-content/uploads/2025/03/Sourdough_Bread1.jpg"
                            alt="https://demo.collegegolfrecruitingportal.com/storage/5"
                            class="hover:shadow-lg cursor-pointer rounded-lg h-32 w-24 object-cover">
                        <div class="text-left">
                            <a class="font-bold text-dark text-xl"
                                href="https://demo.collegegolfrecruitingportal.com/instructor/profile/get?instructor_id=4">
                                Nikhil
                            </a>
                            <div class="text-lg font-bold tracking-tight text-primary">
                                $ 0.00 (USD)
                            </div>
                            <div class="text-sm font-medium text-gray-500 italic">
                                <span class="">(8 Purchased)</span>
                            </div>
                        </div>
                    </div>

                    <div class="px-3 pb-4 mt-1 flex flex-col flex-grow">
                        <div class="flex flex-row justify-between">

                        </div>

                        <span class="text-xl font-semibold text-dark">First Lesson</span>
                        <p class="font-thin text-gray-600 overflow-hidden whitespace-nowrap overflow-ellipsis">
                            First Lesson
                        </p>

                        <div class="mt-2 bg-gray-200 gap-1 rounded-lg px-4 py-3 flex">
                            <div class="text-center w-50">
                                <span class="text-xl font-bold">30</span>
                                <div class="text-sm rtl:space-x-reverse">Number of Lessons</div>

                            </div>
                            <div class="text-center w-50">
                                <span class="text-xl font-bold">30 Days</span>
                                <div class="text-sm rtl:space-x-reverse">Expected Response Time</div>

                            </div>

                        </div>

                        <div class="w-100 mt-3">
                            <form method="POST"
                                action="https://demo.collegegolfrecruitingportal.com/purchase/store?lesson_id=1"
                                accept-charset="UTF-8" enctype="multipart/form-data" class="form-horizontal"
                                data-validate="" novalidate="true"><input name="_token" type="hidden"
                                    value="0DKCSNAoSKqudQ5rlJIX6LimpNfZ5JMl0QoWqaGH">
                                <button type="submit" class="lesson-btn">Purchase</button>
                            </form>

                        </div>
                    </div>
                    <form id="bookingForm" method="POST"
                        action="https://demo.collegegolfrecruitingportal.com/lesson/slot/booking?redirect=1">
                        <input type="hidden" name="_token" value="0DKCSNAoSKqudQ5rlJIX6LimpNfZ5JMl0QoWqaGH"> <input
                            type="hidden" id="slotIdInput" name="slot_id">
                        <input type="hidden" id="friendNamesInput" name="friend_names">

                    </form>
                </div>
            </div>
            <div class="p-3">
                <div class=" bg-white rounded-lg shadow dark:bg-gray-800 dark:border-gray-700 flex flex-col">
                    <div class="relative text-center p-3 flex gap-3">
                        <img src="https://xn--kbenhavnercafeen-lxb.dk/wp-content/uploads/2025/03/Sourdough_Bread1.jpg"
                            alt="https://demo.collegegolfrecruitingportal.com/storage/5"
                            class="hover:shadow-lg cursor-pointer rounded-lg h-32 w-24 object-cover">
                        <div class="text-left">
                            <a class="font-bold text-dark text-xl"
                                href="https://demo.collegegolfrecruitingportal.com/instructor/profile/get?instructor_id=4">
                                Nikhil
                            </a>
                            <div class="text-lg font-bold tracking-tight text-primary">
                                $ 0.00 (USD)
                            </div>
                            <div class="text-sm font-medium text-gray-500 italic">
                                <span class="">(8 Purchased)</span>
                            </div>
                        </div>
                    </div>

                    <div class="px-3 pb-4 mt-1 flex flex-col flex-grow">
                        <div class="flex flex-row justify-between">

                        </div>

                        <span class="text-xl font-semibold text-dark">First Lesson</span>
                        <p class="font-thin text-gray-600 overflow-hidden whitespace-nowrap overflow-ellipsis">
                            First Lesson
                        </p>

                        <div class="mt-2 bg-gray-200 gap-1 rounded-lg px-4 py-3 flex">
                            <div class="text-center w-50">
                                <span class="text-xl font-bold">30</span>
                                <div class="text-sm rtl:space-x-reverse">Number of Lessons</div>

                            </div>
                            <div class="text-center w-50">
                                <span class="text-xl font-bold">30 Days</span>
                                <div class="text-sm rtl:space-x-reverse">Expected Response Time</div>

                            </div>

                        </div>

                        <div class="w-100 mt-3">
                            <form method="POST"
                                action="https://demo.collegegolfrecruitingportal.com/purchase/store?lesson_id=1"
                                accept-charset="UTF-8" enctype="multipart/form-data" class="form-horizontal"
                                data-validate="" novalidate="true"><input name="_token" type="hidden"
                                    value="0DKCSNAoSKqudQ5rlJIX6LimpNfZ5JMl0QoWqaGH">
                                <button type="submit" class="lesson-btn">Purchase</button>
                            </form>

                        </div>
                    </div>
                    <form id="bookingForm" method="POST"
                        action="https://demo.collegegolfrecruitingportal.com/lesson/slot/booking?redirect=1">
                        <input type="hidden" name="_token" value="0DKCSNAoSKqudQ5rlJIX6LimpNfZ5JMl0QoWqaGH"> <input
                            type="hidden" id="slotIdInput" name="slot_id">
                        <input type="hidden" id="friendNamesInput" name="friend_names">

                    </form>
                </div>
            </div>
            <div class="p-3">
                <div class=" bg-white rounded-lg shadow dark:bg-gray-800 dark:border-gray-700 flex flex-col">
                    <div class="relative text-center p-3 flex gap-3">
                        <img src="https://xn--kbenhavnercafeen-lxb.dk/wp-content/uploads/2025/03/Sourdough_Bread1.jpg"
                            alt="https://demo.collegegolfrecruitingportal.com/storage/5"
                            class="hover:shadow-lg cursor-pointer rounded-lg h-32 w-24 object-cover">
                        <div class="text-left">
                            <a class="font-bold text-dark text-xl"
                                href="https://demo.collegegolfrecruitingportal.com/instructor/profile/get?instructor_id=4">
                                Nikhil
                            </a>
                            <div class="text-lg font-bold tracking-tight text-primary">
                                $ 0.00 (USD)
                            </div>
                            <div class="text-sm font-medium text-gray-500 italic">
                                <span class="">(8 Purchased)</span>
                            </div>
                        </div>
                    </div>

                    <div class="px-3 pb-4 mt-1 flex flex-col flex-grow">
                        <div class="flex flex-row justify-between">

                        </div>

                        <span class="text-xl font-semibold text-dark">First Lesson</span>
                        <p class="font-thin text-gray-600 overflow-hidden whitespace-nowrap overflow-ellipsis">
                            First Lesson
                        </p>

                        <div class="mt-2 bg-gray-200 gap-1 rounded-lg px-4 py-3 flex">
                            <div class="text-center w-50">
                                <span class="text-xl font-bold">30</span>
                                <div class="text-sm rtl:space-x-reverse">Number of Lessons</div>

                            </div>
                            <div class="text-center w-50">
                                <span class="text-xl font-bold">30 Days</span>
                                <div class="text-sm rtl:space-x-reverse">Expected Response Time</div>

                            </div>

                        </div>

                        <div class="w-100 mt-3">
                            <form method="POST"
                                action="https://demo.collegegolfrecruitingportal.com/purchase/store?lesson_id=1"
                                accept-charset="UTF-8" enctype="multipart/form-data" class="form-horizontal"
                                data-validate="" novalidate="true"><input name="_token" type="hidden"
                                    value="0DKCSNAoSKqudQ5rlJIX6LimpNfZ5JMl0QoWqaGH">
                                <button type="submit" class="lesson-btn">Purchase</button>
                            </form>

                        </div>
                    </div>
                    <form id="bookingForm" method="POST"
                        action="https://demo.collegegolfrecruitingportal.com/lesson/slot/booking?redirect=1">
                        <input type="hidden" name="_token" value="0DKCSNAoSKqudQ5rlJIX6LimpNfZ5JMl0QoWqaGH"> <input
                            type="hidden" id="slotIdInput" name="slot_id">
                        <input type="hidden" id="friendNamesInput" name="friend_names">

                    </form>
                </div>
            </div>
        </div>
    </div>
</section>














<footer class="foot mt-0">
    <div class="d-flex justify-content-between container footer-one">
        <div class="d-flex flex-column gap-4">
            <div>

                <img src="{{ asset('assets/images/landing-page-images/logo (5).png') }}" alt="" />
            </div>
            <p class="text-white fot-golf word-spacing-5">
                Trusted by Industry leading Golf Club, Instructors, Acadmies, and
                Teaching Professionals.
            </p>
        </div>
        <div class="d-flex flex-column gap-4 google">
            <div>
                <img src="{{ asset('assets/images/landing-page-images/Google Play.png') }}" alt="" />
            </div>
            <div>
                <img src="{{ asset('assets/images/landing-page-images/App Store.png') }}" alt="" />
            </div>
        </div>
    </div>
</footer>
<footer class="foot-two">
    <div class="d-flex justify-content-between align-items-center container footer-two">
        <div class="text-white m-0">
            <p class="fot-p">© 2025 Tuneup. All rights reserved.</p>
        </div>
        <div class="icon">
            <img src="{{ asset('assets/images/landing-page-images/Facebook.png') }}" alt="" />
            <img src="{{ asset('assets/images/landing-page-images/Twitter.png') }}" alt="" />
            <img src="{{ asset('assets/images/landing-page-images/Instagram.png') }}" alt="" />
            <img src="{{ asset('assets/images/landing-page-images/Github.png') }}" alt="" />
            <img src="{{ asset('assets/images/landing-page-images/LinkedIn.png') }}" alt="" />
        </div>
    </div>
</footer>
@endsection

<script>

</script>