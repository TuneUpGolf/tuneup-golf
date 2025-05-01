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
    <div class="hero-sec">
        <img src="{{ asset('assets/images/landing-page-images/image.jpg') }}" alt="hero-banner">
    </div>
</section>
<section class="lession-sec">
    <div class="container ctm-container">
        <h2 class="font-bold text-4xl mb-2">John Doe: Lesson Opportunities</h2>
        <p style="color:#718096;" class="text-xl max-w-2xl">Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed
            do eiusmod tempor incididunt ut labore et dolore magna aliqua.</p>
    </div>
    <div class="container-fluid lessions-slider pt-5">
        <div class="px-3 py-4">
            <div class=" bg-white rounded-lg shadow   flex flex-col">
                <div class="relative text-center p-3 flex gap-3">
                    <img src="https://xn--kbenhavnercafeen-lxb.dk/wp-content/uploads/2025/03/Sourdough_Bread1.jpg"
                        alt="https://demo.collegegolfrecruitingportal.com/storage/5"
                        class="hover:shadow-lg cursor-pointer rounded-lg h-32 w-24 object-cover">
                    <div class="text-left">
                        <a class="font-bold text-dark text-xl"
                            href="https://demo.collegegolfrecruitingportal.com/instructor/profile/get?instructor_id=4">
                            User
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
                    <span class="text-xl font-semibold text-dark">First Lesson</span>
                    <p class="font-thin text-gray-600 overflow-hidden whitespace-nowrap overflow-ellipsis">
                        First Lesson
                    </p>

                    <div class="mt-2 bg-gray-200 gap-2 rounded-lg px-4 py-3 flex">
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
                            <button type="submit" class="lesson-btn py-2">Purchase</button>
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
        <div class="px-3 py-4">
            <div class=" bg-white rounded-lg shadow   flex flex-col">
                <div class="relative text-center p-3 flex gap-3">
                    <img src="https://xn--kbenhavnercafeen-lxb.dk/wp-content/uploads/2025/03/Sourdough_Bread1.jpg"
                        alt="https://demo.collegegolfrecruitingportal.com/storage/5"
                        class="hover:shadow-lg cursor-pointer rounded-lg h-32 w-24 object-cover">
                    <div class="text-left">
                        <a class="font-bold text-dark text-xl"
                            href="https://demo.collegegolfrecruitingportal.com/instructor/profile/get?instructor_id=4">
                            User
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
                    <span class="text-xl font-semibold text-dark">First Lesson</span>
                    <p class="font-thin text-gray-600 overflow-hidden whitespace-nowrap overflow-ellipsis">
                        First Lesson
                    </p>
                    <div class="mt-2 bg-gray-200 gap-2 rounded-lg px-4 py-3 flex">
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
                            <button type="submit" class="lesson-btn py-2">Purchase</button>
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
        <div class="px-3 py-4">
            <div class=" bg-white rounded-lg shadow   flex flex-col">
                <div class="relative text-center p-3 flex gap-3">
                    <img src="https://xn--kbenhavnercafeen-lxb.dk/wp-content/uploads/2025/03/Sourdough_Bread1.jpg"
                        alt="https://demo.collegegolfrecruitingportal.com/storage/5"
                        class="hover:shadow-lg cursor-pointer rounded-lg h-32 w-24 object-cover">
                    <div class="text-left">
                        <a class="font-bold text-dark text-xl"
                            href="https://demo.collegegolfrecruitingportal.com/instructor/profile/get?instructor_id=4">
                            User
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
                    <span class="text-xl font-semibold text-dark">First Lesson</span>
                    <p class="font-thin text-gray-600 overflow-hidden whitespace-nowrap overflow-ellipsis">
                        First Lesson
                    </p>
                    <div class="mt-2 bg-gray-200 gap-2 rounded-lg px-4 py-3 flex">
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
                            <button type="submit" class="lesson-btn py-2">Purchase</button>
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
        <div class="px-3 py-4">
            <div class=" bg-white rounded-lg shadow   flex flex-col">
                <div class="relative text-center p-3 flex gap-3">
                    <img src="https://xn--kbenhavnercafeen-lxb.dk/wp-content/uploads/2025/03/Sourdough_Bread1.jpg"
                        alt="https://demo.collegegolfrecruitingportal.com/storage/5"
                        class="hover:shadow-lg cursor-pointer rounded-lg h-32 w-24 object-cover">
                    <div class="text-left">
                        <a class="font-bold text-dark text-xl"
                            href="https://demo.collegegolfrecruitingportal.com/instructor/profile/get?instructor_id=4">
                            User
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
                    <span class="text-xl font-semibold text-dark">First Lesson</span>
                    <p class="font-thin text-gray-600 overflow-hidden whitespace-nowrap overflow-ellipsis">
                        First Lesson
                    </p>
                    <div class="mt-2 bg-gray-200 gap-2 rounded-lg px-4 py-3 flex">
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
                            <button type="submit" class="lesson-btn py-2">Purchase</button>
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
        <div class="px-3 py-4">
            <div class=" bg-white rounded-lg shadow   flex flex-col">
                <div class="relative text-center p-3 flex gap-3">
                    <img src="https://xn--kbenhavnercafeen-lxb.dk/wp-content/uploads/2025/03/Sourdough_Bread1.jpg"
                        alt="https://demo.collegegolfrecruitingportal.com/storage/5"
                        class="hover:shadow-lg cursor-pointer rounded-lg h-32 w-24 object-cover">
                    <div class="text-left">
                        <a class="font-bold text-dark text-xl"
                            href="https://demo.collegegolfrecruitingportal.com/instructor/profile/get?instructor_id=4">
                            User
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
                    <span class="text-xl font-semibold text-dark">First Lesson</span>
                    <p class="font-thin text-gray-600 overflow-hidden whitespace-nowrap overflow-ellipsis">
                        First Lesson
                    </p>
                    <div class="mt-2 bg-gray-200 gap-2 rounded-lg px-4 py-3 flex">
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
                            <button type="submit" class="lesson-btn py-2">Purchase</button>
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
        <div class="px-3 py-4">
            <div class=" bg-white rounded-lg shadow   flex flex-col">
                <div class="relative text-center p-3 flex gap-3">
                    <img src="https://xn--kbenhavnercafeen-lxb.dk/wp-content/uploads/2025/03/Sourdough_Bread1.jpg"
                        alt="https://demo.collegegolfrecruitingportal.com/storage/5"
                        class="hover:shadow-lg cursor-pointer rounded-lg h-32 w-24 object-cover">
                    <div class="text-left">
                        <a class="font-bold text-dark text-xl"
                            href="https://demo.collegegolfrecruitingportal.com/instructor/profile/get?instructor_id=4">
                            User
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
                    <span class="text-xl font-semibold text-dark">First Lesson</span>
                    <p class="font-thin text-gray-600 overflow-hidden whitespace-nowrap overflow-ellipsis">
                        First Lesson
                    </p>
                    <div class="mt-2 bg-gray-200 gap-2 rounded-lg px-4 py-3 flex">
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
                            <button type="submit" class="lesson-btn py-2">Purchase</button>
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
</section>

<section class="lession-sec subscription-sec">
    <div class="container ctm-container">
        <h2 class="font-bold text-4xl mb-2">Subscription Plans</h2>
        <p style="color:#718096;" class="text-xl max-w-2xl">Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed
            do eiusmod tempor incididunt ut labore et dolore magna aliqua.</p>
        <div class="subscription-slider pt-5">
            <div class="px-3 py-4">
                <div class="bg-white rounded-lg shadow">
                    
                    <div class="relative p-3">  
                        <p class="text-2xl font-semibold mb-2">Chat</p>
                        <p class="text-gray-600">
                            Sed ut perspiciatis unde omnis iste natus error sit voluptatem accusantium.
                        </p>
                        <div class="flex gap-2 items-center my-3">
                            <h2 class="text-6xl font-bold">$19.99</h2>
                            <div>
                                <p>$25.00</p>
                                <p class="text-gray-600">/ Month</p>
                            </div>
                        </div>
                        <button type="submit" class="lesson-btn btn-border font-bold text-lg">Purchase</button>
                    </div>
                    <div class="border-t border-gray-300"></div>
                    <div class="p-3">
                        <p class="font-semibold text-xl">Features</p>
                        <ul class="mt-2">
                            <li class="flex items-center gap-2 mb-2">
                                <i class="text-3xl text-primary ti ti-circle-check"></i>
                                <p>Sed ut perspiciatis unde omnis iste.</p>
                            </li>
                            <li class="flex items-center gap-2 mb-2">
                                <i class="text-3xl text-primary ti ti-circle-check"></i>
                                <p>Sed ut perspiciatis unde omnis iste.</p>
                            </li>
                            <li class="flex items-center gap-2 mb-2">
                                <i class="text-3xl text-primary ti ti-circle-check"></i>
                                <p>Sed ut perspiciatis unde omnis iste.</p>
                            </li>
                            <li class="flex items-center gap-2 mb-2">
                                <i class="text-3xl text-primary ti ti-circle-check"></i>
                                <p>Sed ut perspiciatis unde omnis iste.</p>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="px-3 py-4">
                <div class="bg-white rounded-lg shadow popular-wrap position-relative">
                <div class="rounded-pill px-4 py-2 popular-plan w-auto bg-primary text-white font-bold position-absolute ">POPULAR</div>
                    <div class="relative p-3">
                        
                        <p class="text-2xl font-semibold mb-2">Chat</p>
                        <p class="text-gray-600">
                            Sed ut perspiciatis unde omnis iste natus error sit voluptatem accusantium.
                        </p>
                        <div class="flex gap-2 items-center my-3">
                            <h2 class="text-6xl font-bold">$19.99</h2>
                            <div>
                                <p>$25.00</p>
                                <p class="text-gray-600">/ Month</p>
                            </div>
                        </div>
                        <button type="submit" class="lesson-btn font-bold text-lg">Purchase</button>
                    </div>
                    <div class="border-t border-gray-300"></div>
                    <div class="p-3">
                        <p class="font-semibold text-xl">Features</p>
                        <ul class="mt-2">
                            <li class="flex items-center gap-2 mb-2">
                                <i class="text-3xl text-primary ti ti-circle-check"></i>
                                <p>Sed ut perspiciatis unde omnis iste.</p>
                            </li>
                            <li class="flex items-center gap-2 mb-2">
                                <i class="text-3xl text-primary ti ti-circle-check"></i>
                                <p>Sed ut perspiciatis unde omnis iste.</p>
                            </li>
                            <li class="flex items-center gap-2 mb-2">
                                <i class="text-3xl text-primary ti ti-circle-check"></i>
                                <p>Sed ut perspiciatis unde omnis iste.</p>
                            </li>
                            <li class="flex items-center gap-2 mb-2">
                                <i class="text-3xl text-primary ti ti-circle-check"></i>
                                <p>Sed ut perspiciatis unde omnis iste.</p>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="px-3 py-4">
                <div class="bg-white rounded-lg shadow">
                    <div class="relative p-3">
                        
                        <p class="text-2xl font-semibold mb-2">Chat</p>
                        <p class="text-gray-600">
                            Sed ut perspiciatis unde omnis iste natus error sit voluptatem accusantium.
                        </p>
                        <div class="flex gap-2 items-center my-3">
                            <h2 class="text-6xl font-bold">$19.99</h2>
                            <div>
                                <p>$25.00</p>
                                <p class="text-gray-600">/ Month</p>
                            </div>
                        </div>
                        <button type="submit" class="lesson-btn btn-border font-bold text-lg">Purchase</button>
                    </div>
                    <div class="border-t border-gray-300"></div>
                    <div class="p-3">
                        <p class="font-semibold text-xl">Features</p>
                        <ul class="mt-2">
                            <li class="flex items-center gap-2 mb-2">
                                <i class="text-3xl text-primary ti ti-circle-check"></i>
                                <p>Sed ut perspiciatis unde omnis iste.</p>
                            </li>
                            <li class="flex items-center gap-2 mb-2">
                                <i class="text-3xl text-primary ti ti-circle-check"></i>
                                <p>Sed ut perspiciatis unde omnis iste.</p>
                            </li>
                            <li class="flex items-center gap-2 mb-2">
                                <i class="text-3xl text-primary ti ti-circle-check"></i>
                                <p>Sed ut perspiciatis unde omnis iste.</p>
                            </li>
                            <li class="flex items-center gap-2 mb-2">
                                <i class="text-3xl text-primary ti ti-circle-check"></i>
                                <p>Sed ut perspiciatis unde omnis iste.</p>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="px-3 py-4">
                <div class="bg-white rounded-lg shadow">
                    <div class="relative p-3">
                        
                        <p class="text-2xl font-semibold mb-2">Chat</p>
                        <p class="text-gray-600">
                            Sed ut perspiciatis unde omnis iste natus error sit voluptatem accusantium.
                        </p>
                        <div class="flex gap-2 items-center my-3">
                            <h2 class="text-6xl font-bold">$19.99</h2>
                            <div>
                                <p>$25.00</p>
                                <p class="text-gray-600">/ Month</p>
                            </div>
                        </div>
                        <button type="submit" class="lesson-btn btn-border font-bold text-lg">Purchase</button>
                    </div>
                    <div class="border-t border-gray-300"></div>
                    <div class="p-3">
                        <p class="font-semibold text-xl">Features</p>
                        <ul class="mt-2">
                            <li class="flex items-center gap-2 mb-2">
                                <i class="text-3xl text-primary ti ti-circle-check"></i>
                                <p>Sed ut perspiciatis unde omnis iste.</p>
                            </li>
                            <li class="flex items-center gap-2 mb-2">
                                <i class="text-3xl text-primary ti ti-circle-check"></i>
                                <p>Sed ut perspiciatis unde omnis iste.</p>
                            </li>
                            <li class="flex items-center gap-2 mb-2">
                                <i class="text-3xl text-primary ti ti-circle-check"></i>
                                <p>Sed ut perspiciatis unde omnis iste.</p>
                            </li>
                            <li class="flex items-center gap-2 mb-2">
                                <i class="text-3xl text-primary ti ti-circle-check"></i>
                                <p>Sed ut perspiciatis unde omnis iste.</p>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="px-3 py-4">
                <div class="bg-white rounded-lg shadow">
                    <div class="relative p-3">
                        
                        <p class="text-2xl font-semibold mb-2">Chat</p>
                        <p class="text-gray-600">
                            Sed ut perspiciatis unde omnis iste natus error sit voluptatem accusantium.
                        </p>
                        <div class="flex gap-2 items-center my-3">
                            <h2 class="text-6xl font-bold">$19.99</h2>
                            <div>
                                <p>$25.00</p>
                                <p class="text-gray-600">/ Month</p>
                            </div>
                        </div>
                        <button type="submit" class="lesson-btn btn-border font-bold text-lg">Purchase</button>
                    </div>
                    <div class="border-t border-gray-300"></div>
                    <div class="p-3">
                        <p class="font-semibold text-xl">Features</p>
                        <ul class="mt-2">
                            <li class="flex items-center gap-2 mb-2">
                                <i class="text-3xl text-primary ti ti-circle-check"></i>
                                <p>Sed ut perspiciatis unde omnis iste.</p>
                            </li>
                            <li class="flex items-center gap-2 mb-2">
                                <i class="text-3xl text-primary ti ti-circle-check"></i>
                                <p>Sed ut perspiciatis unde omnis iste.</p>
                            </li>
                            <li class="flex items-center gap-2 mb-2">
                                <i class="text-3xl text-primary ti ti-circle-check"></i>
                                <p>Sed ut perspiciatis unde omnis iste.</p>
                            </li>
                            <li class="flex items-center gap-2 mb-2">
                                <i class="text-3xl text-primary ti ti-circle-check"></i>
                                <p>Sed ut perspiciatis unde omnis iste.</p>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>


<section class="lession-sec feed-sec">
    <div class="container ctm-container">
        <h2 class="font-bold text-4xl mb-2">Feed</h2>
        <p style="color:#718096;" class="text-xl max-w-2xl mb-3">Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed
            do eiusmod tempor incididunt ut labore et dolore magna aliqua.</p>

        <div class="flex flex-wrap gap-5 w-100">
            <div class="max-w-sm w-full">
                <div class="shadow rounded-2 overflow-hidden position-relative">
                    <div class="p-3 position-absolute left-0 top-0 z-10 w-full custom-gradient">
                        <div class="flex justify-between items-center w-full">
                            <div class="flex items-center gap-3">
                                <img class="w-16 h-16 rounded-full"
                                    src="https://upload.wikimedia.org/wikipedia/commons/8/89/Portrait_Placeholder.png"
                                    alt="Profile">
                                <div>
                                    <p class="text-xl text-white font-bold mb-0 leading-tight">
                                        David
                                    </p>
                                    <span class="text-md text-white">
                                        Instructor
                                    </span>
                                </div>
                            </div>

                            <div class="bg-white py-2 px-3 rounded-3xl shadow">
                                <form method="POST"
                                    action="https://demo.collegegolfrecruitingportal.com/purchase/like?post_id=10"
                                    accept-charset="UTF-8" data-validate="" novalidate="true"><input name="_token"
                                        type="hidden" value="SPXmKFzZiPNexBLu4sdqhfLFZub7MjKoldBMJsMM">

                                    <button type="submit" class="text-md font-semibold flex items-center gap-2"><i
                                            class="text-2xl lh-sm ti ti-heart"></i><span> 1 Likes</span></button>
                                </form>
                            </div>

                        </div>
                    </div>

                    <!-- <img class="rounded-md w-full" src="https://demo.collegegolfrecruitingportal.com/storage/5/posts/cYQP1BsIKDFShYyGdNfSoVJNgX5zXibtHYV37ptw.png"
                alt="Post Image" /> -->
                    <img class=" w-full post-thumbnail"
                        src="https://xn--kbenhavnercafeen-lxb.dk/wp-content/uploads/2025/03/Sourdough_Bread1.jpg"
                        alt="Profile">


                    <div class="px-4 py-2">
                        <div class="text-md italic text-gray-500">
                            30 Apr 2025
                        </div>
                        <h1 class="text-xl font-bold truncate">
                            Quam culpa ut nostr
                        </h1>


                        <p class="text-gray-500 text-md mt-1 description font-medium ctm-min-h">
                            <span class="short-text">Lorem ipsum</span>
                        </p>
                    </div>
                </div>
            </div>
            <div class="max-w-sm w-full">
                <div class="shadow rounded-2 overflow-hidden position-relative">
                    <div class="p-3 position-absolute left-0 top-0 z-10 w-full">
                        <div class="flex justify-between items-center w-full">
                            <div class="flex items-center gap-3">
                                <!--                     <img class="w-10 h-10 rounded-full"
                        src="https://demo.collegegolfrecruitingportal.com/storage/5"
                        alt="Profile" />
                 -->
                                <img class="w-16 h-16 rounded-full"
                                    src="https://upload.wikimedia.org/wikipedia/commons/8/89/Portrait_Placeholder.png"
                                    alt="Profile">
                                <div>
                                    <p class="text-xl text-white font-bold mb-0 leading-tight">
                                        David
                                    </p>
                                    <span class="text-md text-white">
                                        Instructor
                                    </span>
                                </div>
                            </div>

                            <div class="bg-white py-2 px-3 rounded-3xl shadow">
                                <form method="POST"
                                    action="https://demo.collegegolfrecruitingportal.com/purchase/like?post_id=9"
                                    accept-charset="UTF-8" data-validate="" novalidate="true"><input name="_token"
                                        type="hidden" value="SPXmKFzZiPNexBLu4sdqhfLFZub7MjKoldBMJsMM">

                                    <button type="submit" class="text-md font-semibold flex items-center gap-2"><i
                                            class="text-2xl lh-sm ti ti-heart"></i><span> 1 Likes</span></button>
                                </form>
                            </div>

                        </div>
                    </div>

                    <div class="relative paid-post-wrap">
                        <img class=" w-full post-thumbnail"
                            src="https://xn--kbenhavnercafeen-lxb.dk/wp-content/uploads/2025/03/Sourdough_Bread1.jpg"
                            alt="Post Image">
                        <div class="absolute inset-0 flex justify-center items-center paid-post flex-col">
                            <div
                                class="ctm-icon-box bg-white rounded-full text-primary w-24 h-24 text-7xl flex items-center justify-content-center text-center border border-5 mb-3">
                                <i class="ti ti-lock-open"></i>
                            </div>

                            <form method="POST"
                                action="https://demo.collegegolfrecruitingportal.com/purchase/post/instructor?post_id=9"
                                accept-charset="UTF-8" data-validate="" novalidate="true"><input name="_token"
                                    type="hidden" value="SPXmKFzZiPNexBLu4sdqhfLFZub7MjKoldBMJsMM">

                                <div
                                    class="bg-orange text-white px-4 py-1 rounded-3xl w-full text-center flex items-center justify-center gap-1">
                                    <i class="ti ti-lock-open text-2xl lh-sm"></i>
                                    <button type="submit" class="btn p-0 pl-1 text-white border-0">Unlock for -
                                        $2.00</button>

                                </div>
                            </form>
                        </div>
                    </div>


                    <div class="px-4 py-2">
                        <div class="text-md italic text-gray-500">
                            24 Apr 2025
                        </div>
                        <h1 class="text-xl font-bold truncate">
                            tenant test
                        </h1>


                        <p class="text-gray-500 text-md mt-1 description font-medium ctm-min-h">
                            <span class="short-text">Contrary to popular belief, Lorem Ipsum is not simply random text.
                                It has roots</span>
                            <span class="hidden full-text">Contrary to popular belief, Lorem Ipsum is not simply random
                                text. It has roots in a piece of classical Latin literature from 45 BC, making it over
                                2000 years old.</span>
                            <a href="javascript:void(0);" class="text-blue-600 toggle-read-more font-semibold underline"
                                onclick="toggleDescription(this)">Read More</a>
                        </p>
                    </div>
                </div>
            </div>
            <div class="max-w-sm w-full">
                <div class="shadow rounded-2 overflow-hidden position-relative">
                    <div class="p-3 position-absolute left-0 top-0 z-10 w-full custom-gradient">
                        <div class="flex justify-between items-center w-full">
                            <div class="flex items-center gap-3">
                                <!--                     <img class="w-10 h-10 rounded-full"
                        src="https://demo.collegegolfrecruitingportal.com/storage/5"
                        alt="Profile" />
                 -->
                                <img class="w-16 h-16 rounded-full"
                                    src="https://upload.wikimedia.org/wikipedia/commons/8/89/Portrait_Placeholder.png"
                                    alt="Profile">
                                <div>
                                    <p class="text-xl text-white font-bold mb-0 leading-tight">
                                        David
                                    </p>
                                    <span class="text-md text-white">
                                        Instructor
                                    </span>
                                </div>
                            </div>

                            <div class="bg-white py-2 px-3 rounded-3xl shadow">
                                <form method="POST"
                                    action="https://demo.collegegolfrecruitingportal.com/purchase/like?post_id=8"
                                    accept-charset="UTF-8" data-validate="" novalidate="true"><input name="_token"
                                        type="hidden" value="SPXmKFzZiPNexBLu4sdqhfLFZub7MjKoldBMJsMM">

                                    <button type="submit" class="text-md font-semibold flex items-center gap-2"><i
                                            class="text-2xl lh-sm ti ti-heart"></i><span> 0 Likes</span></button>
                                </form>
                            </div>

                        </div>
                    </div>

                    <!-- <img class="rounded-md w-full" src="https://demo.collegegolfrecruitingportal.com/storage/5"
                alt="Post Image" /> -->
                    <img class=" w-full post-thumbnail"
                        src="https://xn--kbenhavnercafeen-lxb.dk/wp-content/uploads/2025/03/Sourdough_Bread1.jpg"
                        alt="Profile">


                    <div class="px-4 py-2">
                        <div class="text-md italic text-gray-500">
                            24 Apr 2025
                        </div>
                        <h1 class="text-xl font-bold truncate">
                            tenant test
                        </h1>


                        <p class="text-gray-500 text-md mt-1 description font-medium ctm-min-h">
                            <span class="short-text">Contrary to popular belief, Lorem Ipsum is not simply random text.
                                It has roots</span>
                            <span class="hidden full-text">Contrary to popular belief, Lorem Ipsum is not simply random
                                text. It has roots in a piece of classical Latin literature from 45 BC, making it over
                                2000 years old.</span>
                            <a href="javascript:void(0);" class="text-blue-600 toggle-read-more font-semibold underline"
                                onclick="toggleDescription(this)">Read More</a>
                        </p>
                    </div>
                </div>
            </div>
            <div class="max-w-sm w-full">
                <div class="shadow rounded-2 overflow-hidden position-relative">
                    <div class="p-3 position-absolute left-0 top-0 z-10 w-full custom-gradient">
                        <div class="flex justify-between items-center w-full">
                            <div class="flex items-center gap-3">
                                <!--                     <img class="w-10 h-10 rounded-full"
                        src="https://demo.collegegolfrecruitingportal.com/storage/5"
                        alt="Profile" />
                 -->
                                <img class="w-16 h-16 rounded-full"
                                    src="https://upload.wikimedia.org/wikipedia/commons/8/89/Portrait_Placeholder.png"
                                    alt="Profile">
                                <div>
                                    <p class="text-xl text-white font-bold mb-0 leading-tight">
                                        David
                                    </p>
                                    <span class="text-md text-white">
                                        Instructor
                                    </span>
                                </div>
                            </div>

                            <div class="bg-white py-2 px-3 rounded-3xl shadow">
                                <form method="POST"
                                    action="https://demo.collegegolfrecruitingportal.com/purchase/like?post_id=7"
                                    accept-charset="UTF-8" data-validate="" novalidate="true"><input name="_token"
                                        type="hidden" value="SPXmKFzZiPNexBLu4sdqhfLFZub7MjKoldBMJsMM">

                                    <button type="submit" class="text-md font-semibold flex items-center gap-2"><i
                                            class="text-2xl lh-sm ti ti-heart"></i><span> 0 Likes</span></button>
                                </form>
                            </div>

                        </div>
                    </div>

                    <!-- <img class="rounded-md w-full" src="https://demo.collegegolfrecruitingportal.com/storage/5"
                alt="Post Image" /> -->
                    <img class=" w-full post-thumbnail"
                        src="https://xn--kbenhavnercafeen-lxb.dk/wp-content/uploads/2025/03/Sourdough_Bread1.jpg"
                        alt="Profile">


                    <div class="px-4 py-2">
                        <div class="text-md italic text-gray-500">
                            24 Apr 2025
                        </div>
                        <h1 class="text-xl font-bold truncate">
                            tenant test
                        </h1>


                        <p class="text-gray-500 text-md mt-1 description font-medium ctm-min-h">
                            <span class="short-text">Contrary to popular belief, Lorem Ipsum is not simply random text.
                                It has roots</span>
                            <span class="hidden full-text">Contrary to popular belief, Lorem Ipsum is not simply random
                                text. It has roots in a piece of classical Latin literature from 45 BC, making it over
                                2000 years old.</span>
                            <a href="javascript:void(0);" class="text-blue-600 toggle-read-more font-semibold underline"
                                onclick="toggleDescription(this)">Read More</a>
                        </p>
                    </div>
                </div>
            </div>
            <div class="max-w-sm w-full">
                <div class="shadow rounded-2 overflow-hidden position-relative">
                    <div class="p-3 position-absolute left-0 top-0 z-10 w-full">
                        <div class="flex justify-between items-center w-full">
                            <div class="flex items-center gap-3">
                                <!--                     <img class="w-10 h-10 rounded-full"
                        src="https://demo.collegegolfrecruitingportal.com/storage/5"
                        alt="Profile" />
                 -->
                                <img class="w-16 h-16 rounded-full"
                                    src="https://upload.wikimedia.org/wikipedia/commons/8/89/Portrait_Placeholder.png"
                                    alt="Profile">
                                <div>
                                    <p class="text-xl text-white font-bold mb-0 leading-tight">
                                        David
                                    </p>
                                    <span class="text-md text-white">
                                        Instructor
                                    </span>
                                </div>
                            </div>

                            <div class="bg-white py-2 px-3 rounded-3xl shadow">
                                <form method="POST"
                                    action="https://demo.collegegolfrecruitingportal.com/purchase/like?post_id=6"
                                    accept-charset="UTF-8" data-validate="" novalidate="true"><input name="_token"
                                        type="hidden" value="SPXmKFzZiPNexBLu4sdqhfLFZub7MjKoldBMJsMM">

                                    <button type="submit" class="text-md font-semibold flex items-center gap-2"><i
                                            class="text-2xl lh-sm ti ti-heart"></i><span> 0 Likes</span></button>
                                </form>
                            </div>

                        </div>
                    </div>

                    <div class="relative paid-post-wrap">
                        <img class=" w-full post-thumbnail"
                            src="https://xn--kbenhavnercafeen-lxb.dk/wp-content/uploads/2025/03/Sourdough_Bread1.jpg"
                            alt="Post Image">
                        <div class="absolute inset-0 flex justify-center items-center paid-post flex-col">
                            <div
                                class="ctm-icon-box bg-white rounded-full text-primary w-24 h-24 text-7xl flex items-center justify-content-center text-center border border-5 mb-3">
                                <i class="ti ti-lock-open"></i>
                            </div>

                            <form method="POST"
                                action="https://demo.collegegolfrecruitingportal.com/purchase/post/instructor?post_id=6"
                                accept-charset="UTF-8" data-validate="" novalidate="true"><input name="_token"
                                    type="hidden" value="SPXmKFzZiPNexBLu4sdqhfLFZub7MjKoldBMJsMM">

                                <div
                                    class="bg-orange text-white px-4 py-1 rounded-3xl w-full text-center flex items-center justify-center gap-1">
                                    <i class="ti ti-lock-open text-2xl lh-sm"></i>
                                    <button type="submit" class="btn p-0 pl-1 text-white border-0">Unlock for -
                                        $43.00</button>

                                </div>
                            </form>
                        </div>
                    </div>


                    <div class="px-4 py-2">
                        <div class="text-md italic text-gray-500">
                            24 Apr 2025
                        </div>
                        <h1 class="text-xl font-bold truncate">
                            Test upload 3
                        </h1>


                        <p class="text-gray-500 text-md mt-1 description font-medium ctm-min-h">
                            <span class="short-text">adfadsfasdf</span>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>










<footer class="foot mt-0">
    <div class="text-center container ctm-container footer-one">
        <div class="flex justify-center">
            <img src="{{ asset('assets/images/landing-page-images/logo-1.png') }}" class="img-fluid" alt="" />
        </div>

        <p class="mt-3 fot-golf word-spacing-5">
            Trusted by Industry leading Golf Club, Instructors, Acadmies, and
            Teaching Professionals.
        </p>
    </div>
</footer>
<footer class="foot-two">
    <div class="flex justify-content-sm-between justify-center align-items-center container footer-two">
        <div class="text-white m-0">
            <p class="fot-p">© 2025 Tuneup. All rights reserved.</p>
        </div>
        <div class="icon flex mt-2 sm-mt-0">
            <img src="{{ asset('assets/images/landing-page-images/Facebook.png') }}" alt="" />
            <img src="{{ asset('assets/images/landing-page-images/Twitter.png') }}" alt="" />
            <img src="{{ asset('assets/images/landing-page-images/Instagram.png') }}" alt="" />
            <img src="{{ asset('assets/images/landing-page-images/Github.png') }}" alt="" />
            <img src="{{ asset('assets/images/landing-page-images/LinkedIn.png') }}" alt="" />
        </div>
    </div>
</footer>
@endsection
