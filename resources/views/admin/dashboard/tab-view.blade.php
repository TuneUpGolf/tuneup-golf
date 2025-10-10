@php
    use Carbon\Carbon;
    $user = Auth::user();
    if ($user->type == 'Admin') {
        $currency_symbol = tenancy()->central(function ($tenant) {
            return Utility::getsettings('currency_symbol');
        });
    } else {
        $currency_symbol = Utility::getsettings('currency_symbol');
    }
    if ($user->type != 'Admin') {
        $currency = Utility::getsettings('currency');
    } else {
        $currency = tenancy()->central(function ($tenant) {
            return Utility::getsettings('currency');
        });
    }
    $isChatTab = isset($token) ? true : false;

@endphp
@extends('layouts.main')
@section('title', __('Dashboard'))
<style>
    .title {
        display: none;
    }

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
</style>
{{--  @section('instructor')  --}}

{{--  @endsection  --}}


@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('home') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item">{{ __('Dashboard') }}</li>
@endsection
@section('content')
    <div class="row">
        <div class="col-xl-12">
            <div class="flex flex-col">
                {{--
         <div class="profile-backdrop">
            <div class="profile-info-container flex">
               <img alt="Anand RaiBV""
               src="http://bvacademy.tuneup.localhost/tuneup_golf/storage/73/dp/qqP2VAIICoIDXGPM0koxNdPXbZRYIpmJQkTJZu0u.png""
               class="rounded-full align-middle border-1 profile-image">
               <div class="flex flex-col">
                  <span class="font-medium text-3xl mb-2">Anand RaiBV</span>
                  <div class="flex justify-center items-center divide-x divide-solid w-100 gap-2 text-gray-600">
                     <div class="text-sm leading-normal text-gray-600 uppercase">
                        <i class="fas fa-map-marker-alt"></i>
                        India
                     </div>
                     <div class="text-sm leading-normal text-gray-600 uppercase">
                        <i class="fas fa-user"></i>
                        Instructor
                     </div>
                  </div>
               </div>
            </div>
         </div>
         --}}
                <div class="card min-h-screen">
                    {{--
            <div class="flex justify-between mt-4">
               <div class="flex justify-center items-center divide-x divide-solid stats-container">
                  <div class="flex flex-col justify-center items-center w-100">
                     <span>2</span>
                     <span class="text-sm text-gray-500 dark:text-gray-400">Followers</span>
                  </div>
                  <div class="flex flex-col justify-center items-center w-100">
                     <span>0</span>
                     <span class="text-sm text-gray-500 dark:text-gray-400">Subscribers</span>
                  </div>
                  <div class="flex flex-col justify-center items-center w-100">
                     <span>1</span>
                     <span class="text-sm text-gray-500 dark:text-gray-400">Posts</span>
                  </div>
                  <div class="flex flex-col justify-center items-center w-100">
                     <span>9</span>
                     <span class="text-sm text-gray-500 dark:text-gray-400">Lessons</span>
                  </div>
               </div>
               <div class="mr-4">
                  <form method="POST" action="http://bvacademy.tuneup.localhost/tuneup_golf/follow/instructor?instructor_id=2&amp;follow=follow" accept-charset="UTF-8" data-validate><input name="_token" type="hidden" value="oChi5851aB97MTK56QymyxR202ph9oHgG1zfkzSD">
                     <button type="submit" class="follow-profile-btn">Follow</button>
                  </form>
               </div>
            </div>
            --}}
                    <div class="tab dashboard-tab">
                        {{--  <button class="tablinks {{ $tab == 'in-person' ? 'active' : '' }}"
                            onclick="window.location.href='home?view=in-person'">In-Person Lessons</button>
                        <button class="tablinks {{ $tab == 'online' ? 'active' : '' }}"
                            onclick="window.location.href='home?view=online'">Online Lessons</button>  --}}
                        <button class="tablinks {{ $tab == 'in-person' ? 'active' : '' }}"
                            onclick="switchTab('in-person')">In-Person Lessons</button>
                        <button class="tablinks {{ $tab == 'online' ? 'active' : '' }}" onclick="switchTab('online')">Online
                            Lessons</button>
                        <button class="tablinks {{ $tab == 'my-lessons' ? 'active' : '' }}"
                            onclick="window.location.href='home?view=my-lessons'">My Lessons</button>
                        <button class="tablinks {{ $tab == 'posts' ? 'active' : '' }}"
                            onclick="window.location.href='home?view=posts'">Tips & Drills</button>
                        @if ($user->type == 'Student')
                            <button class="tablinks {{ $tab == 'subscriptions' ? 'active' : '' }}"
                                onclick="window.location.href='home?view=subscriptions'">Subscriptions</button>
                            <button class="tablinks {{ $tab == 'chat' ? 'active' : '' }}"
                                onclick="window.location.href='home?view=chat'">Chat</button>
                        @endif
                        </hr>
                    </div>
                    <div class="card tabcontent">
                        <div class="flex flex-col w-100">
                            @if ($tab == 'my-lessons')
                                {{ $dataTable->table(['width' => '100%']) }}
                                @push('javascript')
                                    {{ $dataTable->scripts() }}
                                    @include('layouts.includes.datatable_js')
                                @endpush
                            @elseif($tab == 'chat')
                                <div id="Chat">
                                    <div class="row">
                                        @if ($chatEnabled && $instructor)
                                            @include('admin.students.chat', [
                                                'token' => $token,
                                                'instructor' => $instructor,
                                            ])
                                        @else
                                            @isset($plans)
                                                @foreach ($plans as $plan)
                                                    @if ($plan->active_status == 1 && $plan->is_chat_enabled == 1)
                                                        <div class="col-xl-3 col-md-6 py-4">
                                                            <div class="card price-card price-1 wow animate__fadeInUp ani-fade m-0 h-100"
                                                                data-wow-delay="0.2s">
                                                                <div class="rounded-lg shadow popular-wrap h-100">
                                                                    <div class="px-3 pt-4 ">
                                                                        <p class="text-2xl font-bold mb-1">
                                                                            {{ $plan->name }}
                                                                        <p class="text-gray-600"><strong>Instructor:
                                                                                {{ $plan->instructor->name }}</strong></p>
                                                                        </p>
                                                                        <div class="flex gap-1 items-center mt-2 ">
                                                                            <p class="text-4xl font-bold">
                                                                                {{ $currency_symbol . $plan->price }}/</p>
                                                                            <p class="text-2xl text-gray-600">
                                                                                {{ $plan->duration . ' ' . $plan->durationtype }}
                                                                            </p>
                                                                        </div>
                                                                    </div>
                                                                    <div class="border-t border-gray-300"></div>
                                                                    <div class="px-3 py-4">
                                                                        @if ($plan->id != 1)
                                                                            @if (
                                                                                $plan->id == $user->plan_id &&
                                                                                    !empty($user->plan_expired_date) &&
                                                                                    Carbon::parse($user->plan_expired_date)->gte(now()))
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
                                            @endisset
                                        @endif
                                    </div>
                                </div>
                            @elseif($tab == 'subscriptions')
                                <div id="Subscriptions" class="tabcontent block">
                                    <div class="row">
                                        @foreach ($plans as $plan)
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
                                                                <div class="flex gap-1 items-center mt-2 ">
                                                                    <p class="text-4xl font-bold">
                                                                        {{ $currency_symbol . $plan->price }}/</p>
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
                                                                    {{-- 
                                                                    @if ($user->plan_id != null)
                                                                        @if ($plan->id == $user->plan_id)
                                                                            @if (!empty($user->plan_expired_date) && Carbon::parse($user->plan_expired_date)->gte(now()))
                                                                                <a href="javascript:void(0)"
                                                                                    data-id="{{ $plan->id }}"
                                                                                    class="lesson-btn text-center font-bold text-lg mt-auto"
                                                                                    data-amount="{{ $plan->price }}">{{ __('Expire at') }}
                                                                                    {{ Carbon::parse($user->plan_expired_date)->format('d/m/Y') }}</a>
                                                                                <a href="javascript:void(0)"
                                                                                    data-id="{{ $plan->id }}"
                                                                                    class="lesson-btn text-center font-bold text-lg mt-2 cancel-btn"
                                                                                    data-amount="{{ $plan->price }}">Cancel
                                                                                    Plan</a>
                                                                            @else
                                                                                <a href="{{ route('payment', \Illuminate\Support\Facades\Crypt::encrypt($plan->id)) }}"
                                                                                    class="lesson-btn text-center font-bold text-lg mt-auto">
                                                                                    @if ($plan->id == $user->plan_id)
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
                                                                            @if ($plan->id == $user->plan_id)
                                                                                {{ __('Renew') }}
                                                                            @else
                                                                                {{ __('Buy Plan') }}
                                                                            @endif
                                                                        </a>
                                                                    @endif --}}

                                                                    @php
                                                                        $hasPlan = !is_null($user->plan_id);
                                                                        $isCurrentPlan =
                                                                            $hasPlan && $plan->id == $user->plan_id;
                                                                        $isActive =
                                                                            $isCurrentPlan &&
                                                                            !empty($user->plan_expired_date) &&
                                                                            Carbon::parse(
                                                                                $user->plan_expired_date,
                                                                            )->gte(now());
                                                                    @endphp

                                                                    @if ($isCurrentPlan)
                                                                        @if ($isActive)
                                                                            <a href="javascript:void(0)"
                                                                                data-id="{{ $plan->id }}"
                                                                                class="lesson-btn text-center font-bold text-lg mt-auto"
                                                                                data-amount="{{ $plan->price }}">
                                                                                {{ __('Expire at') }}
                                                                                {{ Carbon::parse($user->plan_expired_date)->format('d/m/Y') }}
                                                                            </a>
                                                                                <a href="{{ route('plans.cancel', \Illuminate\Support\Facades\Crypt::encrypt($plan->id)) }}"
                                                                              
                                                                                class="lesson-btn text-center font-bold text-lg mt-2 cancel-btn">
                                                                                {{ __('Cancel Plan') }}
                                                                            </a>
                                                                        @else
                                                                            <a href="{{ route('payment', Crypt::encrypt($plan->id)) }}"
                                                                                class="lesson-btn text-center font-bold text-lg mt-auto">
                                                                                {{ __('Renew') }}
                                                                            </a>
                                                                        @endif
                                                                    @elseif ($hasPlan)
                                                                        <button disabled
                                                                            class="lesson-btn text-center font-bold text-lg mt-auto">
                                                                            {{ __('Buy Plan') }}
                                                                        </button>
                                                                    @else
                                                                        <a href="{{ route('payment', Crypt::encrypt($plan->id)) }}"
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
                                </div>
                            @else
                                @if ($tab == 'in-person')
                                    {{-- @dd($inPerson_instructors) --}}
                                    @if (isset($inPerson_instructors) && $inPerson_instructors->count())
                                        <div class="col-md-2 mt-2">
                                            <select name="instructor" id="instructor" class="form-control"
                                                onchange="updateInstructorUrl(this.value)">
                                                <option value="">All Instructors</option>
                                                @foreach ($inPerson_instructors as $instructor)
                                                    <option value="{{ $instructor->id }}"
                                                        {{ request('instructor_id') == $instructor->id ? 'selected' : '' }}>
                                                        {{ $instructor->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    @endif
                                @elseif($tab == 'online')
                                    @if (isset($online_instructors) && $online_instructors->count())
                                        <div class="col-md-2 mt-2">
                                            <select name="instructor" id="instructor" class="form-control"
                                                onchange="updateInstructorUrl(this.value)">
                                                <option value="">All Instructors</option>
                                                @foreach ($online_instructors as $instructor)
                                                    <option value="{{ $instructor->id }}"
                                                        {{ request('instructor_id') == $instructor->id ? 'selected' : '' }}>
                                                        {{ $instructor->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    @endif
                                @else
                                    <div class="col-md-2 mt-2">
                                        <select name="instructor" id="instructor" class="form-control"
                                            onchange="updateInstructorUrl(this.value)">
                                            <option value="">All Instructors</option>
                                            @foreach ($album_instructors as $instructor)
                                                <option value="{{ $instructor->id }}"
                                                    {{ request('instructor_id') == $instructor->id ? 'selected' : '' }}>
                                                    {{ $instructor->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="dataTable-top row">

                                        <div class="col-xl-7 col-lg-3 col-sm-6 d-none d-sm-block"></div>
                                        <div class="tb-search col-md-5 col-sm-6 col-lg-6 col-xl-5 col-sm-12 d-flex">
                                            <select id="album-category" class="form-select"
                                                style="margin-left:auto; max-width: 12.5rem;">
                                                <option value="" disabled>
                                                    - Select Category -
                                                </option>
                                                <option value=""
                                                    {{ request()->query('category') === ' ' ? 'selected' : '' }}>
                                                    View Individual Tips/Drills
                                                </option>
                                                <option value="all_category"
                                                    {{ request()->query('category') === 'all_category' ? 'selected' : '' }}>
                                                    View Categories
                                                </option>
                                                {{-- @foreach ($album_categories as $category)
                                                    <option value="{{ $category->id }}"
                                                        {{ request()->query('category') == $category->id ? 'selected' : '' }}>
                                                        {{ $category->title }}
                                                    </option>
                                                @endforeach --}}
                                            </select>
                                        </div>
                                    </div>
                                @endif

                                <livewire:student-dashboard-view :instructor_id="$instructor_id" />
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@push('css')
    @include('layouts.includes.datatable_css')
    <link rel="stylesheet"
        href="https://demos.creative-tim.com/notus-js/assets/vendor/@fortawesome/fontawesome-free/css/all.min.css">
@endpush
@push('javascript')
    <script>
        document.getElementById('album-category').addEventListener('change', function() {
            let categoryId = this.value;

            // Only redirect if current view is 'posts' (or not in-person/online)
            const url = new URL(window.location.href);
            const currentView = url.searchParams.get('view') || 'in-person';

            if (currentView === 'posts') {
                url.searchParams.set('category', categoryId);
                url.searchParams.delete('category_album');
                window.location.href = url.toString();
            }
        });


        function switchTab(view) {
            const url = new URL(window.location.href);
            url.searchParams.set('view', view);

            const instructorId = document.getElementById('instructor')?.value;
            if (instructorId) {
                url.searchParams.set('instructor_id', instructorId);
            } else {
                url.searchParams.delete('instructor_id');
            }

            // Remove category when switching to lessons tabs
            if (view === 'in-person' || view === 'online' || view === 'my-lessons' || view === 'chat') {
                url.searchParams.delete('category');
            }

            window.location.href = url.toString();
        }


        function updateInstructorUrl(instructorId) {
            const url = new URL(window.location.href);
            if (instructorId) {
                url.searchParams.set('instructor_id', instructorId);
            } else {
                url.searchParams.delete('instructor_id');
            }
            window.location.href = url.toString();
        }
        $('ul.pagination').hide();
        $(function() {
            $('.infinity').jscroll({
                autoTrigger: true,
                debug: false,
                loadingHtml: '<img class="center-block" src="/images/loading.gif" alt="Loading..." />',
                padding: 0,
                nextSelector: '.pagination li.active + li a',
                contentSelector: '.infinity',
                callback: function() {
                    $('ul.pagination').remove();
                }
            });
        });
    </script>
@endpush
