@php
use Carbon\Carbon;
$user = Auth::user();
if ($user->type == "Admin") {
    $currency_symbol = tenancy()->central(function ($tenant) {
        return Utility::getsettings("currency_symbol");
    });
} else {
    $currency_symbol = Utility::getsettings("currency_symbol");
}
if ($user->type != "Admin") {
    $currency = Utility::getsettings("currency");
} else {
    $currency = tenancy()->central(function ($tenant) {
        return Utility::getsettings("currency");
    });
}
$isChatTab = isset($token) ? true : false;


@endphp
@extends('layouts.main')
@section('title', __('Dashboard'))
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
                <!-- Desktop Tabs -->
                <div class="d-none d-md-flex">
                    <button class="tablinks {{ $tab == 'in-person'? 'active' : '' }}" onclick="window.location.href='home?view=in-person'">In-Person Lessons</button>
                    <button class="tablinks {{ $tab == 'online'? 'active' : '' }}" onclick="window.location.href='home?view=online'">Online Lessons</button>
                    <button class="tablinks {{ $tab == 'my-lessons'? 'active' : '' }}" onclick="window.location.href='home?view=my-lessons'">My Lessons</button>
                    <button class="tablinks {{ $tab == 'posts'? 'active' : '' }}" onclick="window.location.href='home?view=posts'">Tips & Drills</button>
                    @if ($user->type == 'Student')   
                    <button class="tablinks {{ $tab == 'subscriptions' ? 'active' : '' }}" onclick="window.location.href='home?view=subscriptions'">Subscriptions</button>
                    <button class="tablinks {{ $tab == 'chat'? 'active' : '' }}" onclick="window.location.href='home?view=chat'">Chat</button>
                    @endif
                </div>
                <!-- Mobile Accordion Tabs -->
                <div class="d-md-none">
                    <div class="accordion-tab" id="acc-in-person">
                        <div class="accordion-header">In-Person Lessons</div>
                        <div class="accordion-content">
                            <button class="tablinks {{ $tab == 'in-person'? 'active' : '' }}" onclick="window.location.href='home?view=in-person'">Go to In-Person Lessons</button>
                        </div>
                    </div>
                    <div class="accordion-tab" id="acc-online">
                        <div class="accordion-header">Online Lessons</div>
                        <div class="accordion-content">
                            <button class="tablinks {{ $tab == 'online'? 'active' : '' }}" onclick="window.location.href='home?view=online'">Go to Online Lessons</button>
                        </div>
                    </div>
                    <div class="accordion-tab" id="acc-my-lessons">
                        <div class="accordion-header">My Lessons</div>
                        <div class="accordion-content">
                            <button class="tablinks {{ $tab == 'my-lessons'? 'active' : '' }}" onclick="window.location.href='home?view=my-lessons'">Go to My Lessons</button>
                        </div>
                    </div>
                    <div class="accordion-tab" id="acc-posts">
                        <div class="accordion-header">Tips & Drills</div>
                        <div class="accordion-content">
                            <button class="tablinks {{ $tab == 'posts'? 'active' : '' }}" onclick="window.location.href='home?view=posts'">Go to Tips & Drills</button>
                        </div>
                    </div>
                    @if ($user->type == 'Student')   
                    <div class="accordion-tab" id="acc-subscriptions">
                        <div class="accordion-header">Subscriptions</div>
                        <div class="accordion-content">
                            <button class="tablinks {{ $tab == 'subscriptions' ? 'active' : '' }}" onclick="window.location.href='home?view=subscriptions'">Go to Subscriptions</button>
                        </div>
                    </div>
                    <div class="accordion-tab" id="acc-chat">
                        <div class="accordion-header">Chat</div>
                        <div class="accordion-content">
                            <button class="tablinks {{ $tab == 'chat'? 'active' : '' }}" onclick="window.location.href='home?view=chat'">Go to Chat</button>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
            <div class="card tabcontent">
               <div class="flex flex-col w-100">
                  @if($tab == 'my-lessons')
                  {{ $dataTable->table(['width' => '100%']) }}
                  @push('javascript')
                  {{ $dataTable->scripts() }}
                  @include('layouts.includes.datatable_js')
                  @endpush
                  @elseif($tab == 'chat')
                  <div id="Chat">
                     <div class="row">
                        @if($chatEnabled && $instructor)
                              @include('admin.students.chat', ['token' => $token, 'instructor' => $instructor])
                           @else
                              @isset($plans)
                                 @foreach ($plans as $plan)
                                    @if ($plan->active_status == 1 && $plan->is_chat_enabled == 1)
                                    <div class="col-xl-3 col-md-6 py-4">
                                       <div class="card price-card price-1 wow animate__fadeInUp ani-fade m-0 h-100"  data-wow-delay="0.2s">
                                          <div class="rounded-lg shadow popular-wrap h-100">
                                             <div class="px-3 pt-4 ">
                                                <p class="text-2xl font-bold mb-1">
                                                   {{ $plan->name }}
                                                   <p class="text-gray-600"><strong>Instructor: {{ $plan->instructor->name }}</strong></p>
                                                </p>
                                                <div class="flex gap-1 items-center mt-2 ">
                                                   <p class="text-4xl font-bold">{{ $currency_symbol . $plan->price }}/</p>
                                                   <p class="text-2xl text-gray-600">{{ $plan->duration . ' ' . $plan->durationtype }}</p>
                                                </div>
                                             </div>
                                             <div class="border-t border-gray-300"></div>
                                             <div class="px-3 py-4">
                                                @if ($plan->id != 1)
                                                @if ($plan->id == $user->plan_id && !empty($user->plan_expired_date) && Carbon::parse($user->plan_expired_date)->gte(now()))
                                                <a href="javascript:void(0)" data-id="{{ $plan->id }}"
                                                   class="lesson-btn text-center font-bold text-lg mt-auto"
                                                   data-amount="{{ $plan->price }}">{{ __('Expire at') }}
                                                {{ Carbon::parse($user->plan_expired_date)->format('d/m/Y') }}</a>
                                                @else
                                                <a href="{{ route('payment', \Illuminate\Support\Facades\Crypt::encrypt($plan->id)) }}"
                                                   class="lesson-btn text-center font-bold text-lg mt-auto">
                                                   @if($plan->id == $user->plan_id)
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
                           <div class="card price-card price-1 wow animate__fadeInUp ani-fade m-0 h-100"  data-wow-delay="0.2s">
                              <div class="rounded-lg shadow popular-wrap h-100">
                                 <div class="px-3 pt-4 ">
                                    <p class="text-2xl font-bold mb-1">
                                       {{ $plan->name }}
                                       <p class="text-gray-600"><strong>Instructor: {{ $plan->instructor->name }}</strong></p>
                                    </p>
                                    <div class="flex gap-1 items-center mt-2 ">
                                       <p class="text-4xl font-bold">{{ $currency_symbol . $plan->price }}/</p>
                                       <p class="text-2xl text-gray-600">{{ $plan->duration . ' ' . $plan->durationtype }}</p>
                                    </div>
                                 </div>
                                 <div class="border-t border-gray-300"></div>
                                 <div class="px-3 py-4">
                                    @if ($plan->id != 1)
                                    @if ($plan->id == $user->plan_id && !empty($user->plan_expired_date) && Carbon::parse($user->plan_expired_date)->gte(now()))
                                    <a href="javascript:void(0)" data-id="{{ $plan->id }}"
                                       class="lesson-btn text-center font-bold text-lg mt-auto"
                                       data-amount="{{ $plan->price }}">{{ __('Expire at') }}
                                    {{ Carbon::parse($user->plan_expired_date)->format('d/m/Y') }}</a>
                                    @else
                                    <a href="{{ route('payment', \Illuminate\Support\Facades\Crypt::encrypt($plan->id)) }}"
                                       class="lesson-btn text-center font-bold text-lg mt-auto">
                                       @if($plan->id == $user->plan_id)
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
                     </div>
                  </div>
                  @else
                  <livewire:student-dashboard-view />
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
<style>
@media (max-width: 768px) {
    .dashboard-tab {
        display: block;
    }
    .accordion-tab {
        border: 1px solid #eee;
        border-radius: 6px;
        margin-bottom: 8px;
        overflow: hidden;
    }
    .accordion-header {
        background: #f7f7f7;
        padding: 12px 16px;
        cursor: pointer;
        font-weight: bold;
        border-bottom: 1px solid #eee;
    }
    .accordion-content {
        display: none;
        padding: 12px 16px;
        background: #fff;
    }
    .accordion-tab.active .accordion-content {
        display: block;
    }
}
</style>
@endpush
@push('javascript')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jscroll/2.4.1/jquery.jscroll.min.js"></script>

<script>   
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

       // Accordion for mobile
       if ($(window).width() < 768) {
           $('.accordion-tab').removeClass('active');
           $('.accordion-tab').first().addClass('active');
           $('.accordion-header').on('click', function() {
               var parent = $(this).parent();
               if (!parent.hasClass('active')) {
                   $('.accordion-tab').removeClass('active');
                   parent.addClass('active');
               }
           });
       }
   });
</script>
@endpush