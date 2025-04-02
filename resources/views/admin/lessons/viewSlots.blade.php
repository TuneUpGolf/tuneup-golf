 @extends('layouts.main') @section('title', __('Manage Slots')) @section('breadcrumb') <li class="breadcrumb-item"><a
         href="{{ route('home') }}">{{ __('Dashboard') }}</a></li>
 <li class="breadcrumb-item"><a href="{{ route('lesson.index') }}">{{ __('Lesson') }}</a></li>
 <li class="breadcrumb-item">{{ __('Manage Slots') }}</li>
@endsection
@section('content')
 <div class="main-content">
     <section class="section">
         <div class="section-body">
             <div class="m-auto col-lg-6 col-md-8 col-xxl-4">
                 <div class="card">
                     <div>
                         <div class="flex justify-between items-center card-header  w-100">
                             @if (Auth::user()->type === 'Student')
                                 <h5>{{ __('Book Slot') }}</h5>
                                 <p>{{ __('Click on an avaialble slot (green) to book') }}</p>
                             @else
                                 <h5>{{ __('Manage Slots') }}</h5>
                                 <button type="button" class="btn btn-primary" data-bs-dismiss="modal">
                                     <a class="text-white"
                                         href="{{ route('slot.create', ['lesson_id' => $lesson_id]) }}">
                                         {{ __('Add New Slot') }}
                                     </a>
                                 </button>
                             @endif
                         </div>
                         <div class="flex justify-center my-2 gap-2">
                             <div class="flex gap-1 items-center">
                                 <div class="completed-key"></div>
                                 <span>Completed</span>
                             </div>
                             <div class="flex gap-1 items-center">
                                 <div class="booked-key"></div>
                                 <span>Booked</span>
                             </div>
                             <div class="flex gap-1 items-center">
                                 <div class="avaialable-key"></div>
                                 <span>Available</span>
                             </div>
                         </div>
                     </div>
                 </div>
             </div>
             <div id="calendar"></div>
         </div>
     </section>
     {{ Form::open([
         'route' => ['slot.complete', ['redirect' => 1]],
         'method' => 'POST',
         'data-validate',
         'id' => 'form',
     ]) }}
     <input type="hidden" id="slot_id" name="slot_id" value="" />
     <input type="hidden" id="payment_method" name="payment_method" value="" />
     {{ Form::close() }}
     {{ Form::open([
         'route' => ['slot.book', ['redirect' => 1]],
         'method' => 'POST',
         'data-validate',
         'id' => 'form-book',
     ]) }}
     <input type="hidden" id="slot" name="slot_id" value="" />
     {{ Form::close() }}
 </div>
 @stack('scripts')
@endsection
@push('css')
 <script src="{{ asset('assets/js/plugins/choices.min.js') }}"></script>
 <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.12/css/intlTelInput.min.css">
@endpush
@push('javascript')
 <script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js"></script>
 <link href="https://cdn.jsdelivr.net/npm/@fullcalendar/core/main.css" rel="stylesheet" />
 <link href="https://cdn.jsdelivr.net/npm/@fullcalendar/daygrid/main.css" rel="stylesheet" />
 <link href="https://cdn.jsdelivr.net/npm/@fullcalendar/timegrid/main.css" rel="stylesheet" />
 <script>
     document.addEventListener('DOMContentLoaded', function() {
         var calendarEl = document.getElementById('calendar');
         var type = @json($type);
         var payment_method = @json($payment_method);
         var calendar = new FullCalendar.Calendar(calendarEl, {
             initialView: 'timeGridWeek',
             eventShortHeight: 45,
             nowIndicator: true,
             slotMinTime: '8:00:00',
             slotMaxTime: '20:00:00',
             events: @json($events),
             eventClick: function(info) {
                 const slot_id = info?.event?.extendedProps?.slot_id;
                 const isBooked = !!info?.event?.extendedProps?.is_student_assigned;
                 const isCompleted = !!info.event?.extendedProps?.is_completed;
                 if (type == 'Instructor' && isBooked && !isCompleted) {
                     const swalWithBootstrapButtons = Swal.mixin({
                         customClass: {
                             confirmButton: "btn btn-success",
                             cancelButton: "btn btn-primary",
                         },
                         buttonsStyling: false,
                     });
                     if (payment_method == 'both')
                         swalWithBootstrapButtons
                         .fire({
                             title: "Choose Payment Method",
                             text: "Please select from the following payment options before completing slot as complete",
                             icon: "warning",
                             showCancelButton: true,
                             confirmButtonText: "Online",
                             cancelButtonText: "Cash",
                             reverseButtons: true,
                         })
                         .then((result) => {
                             if (result.isConfirmed) {
                                 $("#slot_id").val(slot_id);
                                 $("#payment_method").val('online');
                                 $("#form").submit()
                             } else {
                                 $("#slot_id").val(slot_id);
                                 $("#payment_method").val('cash');
                                 $("#form").submit()
                             }
                         });
                     if (payment_method == 'online') {
                         $("#slot_id").val(slot_id);
                         $("#payment_method").val('online');
                         $("#form").submit()
                     }
                     if (payment_method == 'cash') {
                         $("#slot_id").val(slot_id);
                         $("#payment_method").val('cash');
                         $("#form").submit()
                     }

                 }
                 if (type == 'Student' && !isBooked && !isCompleted) {
                     $("#slot").val(slot_id);
                     $("#form-book").submit()
                 }

             },

         });

         calendar.render();

     });
 </script>
 <script src="{{ asset('vendor/intl-tel-input/jquery.mask.js') }}"></script>
 <script src="{{ asset('vendor/intl-tel-input/intlTelInput-jquery.min.js') }}"></script>
 <script src="{{ asset('vendor/intl-tel-input/utils.min.js') }}"></script>
 </script>
@endpush
