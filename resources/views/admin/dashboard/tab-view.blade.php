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
            <div class="tab">
               <button class="tablinks {{ $activeTab == 'in-person' ? 'active' : '' }}" onclick="openCity(event, 'in-person')">In-Person Lessons</button>
               <button class="tablinks {{ $activeTab == 'online'  ? 'active' : ''}}" onclick="openCity(event, 'online')">Online Lessons</button>
               <button class="tablinks {{ $activeTab == 'posts'  ? 'active' : ''}}" onclick="openCity(event, 'posts')">Posts</button>
               <button class="tablinks {{ $activeTab == 'my-lessons' ? 'active' : '' }}" onclick="openCity(event, 'my-lessons')">My Lessons</button>
               </hr>
            </div>
            <div class="card tabcontent">
                <div class="flex flex-col w-100">
                  @if($activeTab == 'my-lessons')
                  {{ $dataTable->table(['width' => '100%']) }}
                  @push('javascript')
                  @include('layouts.includes.datatable_js')
                  {{ $dataTable->scripts() }}
                  @endpush
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
@endpush
@push('javascript')
<script>
   document.getElementById('Lessons').style.display = "block";
   
   function openCity(evt, tabName) {
       window.location.href = `?view=`+tabName;
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