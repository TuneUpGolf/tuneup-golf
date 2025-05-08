@extends('layouts.main') @section('title', __('Admin Bookings')) @section('breadcrumb') 
<li class="breadcrumb-item"><a
   href="{{ route('home') }}">{{ __('Dashboard') }}</a></li>
<li class="breadcrumb-item"><a href="{{ route('lesson.index') }}">{{ __('Lesson') }}</a></li>
<li class="breadcrumb-item">{{ __('Admin Bookings') }}</li>
@endsection
@section('content')
<div class="main-content">
   <section class="section">
      <div class="section-body">
         <div class="card">
            <div class="p-4">
               <div class="row ">
                  <div class="col-sm-6 ctm-justify-center-sm">
                     <form class="w-full">
                        <select name="instructor_id" id="instructor_id" class="form-select w-full"
                           onchange="this.form.submit()">
                           <option value="">All</option>
                           @foreach ($instructors as $instructor)
                           <option value="{{ $instructor->id }}"
                           {{ request()->input('instructor_id') == $instructor->id ? 'selected' : '' }}>
                           {{ ucfirst($instructor->name) }}</option>
                           @endforeach
                        </select>
                     </form>
                  </div>
                  <div class="col-sm-6 flex gap-2 my-2 place-content-end ctm-justify-center-sm">
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
               <div id="calendar"></div>
            </div>
         </div>
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
<link href="https://cdn.jsdelivr.net/npm/@fullcalendar/core/main.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/@fullcalendar/daygrid/main.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/@fullcalendar/timegrid/main.css" rel="stylesheet" />
<link href="{{ asset('assets/css/custom-calendar.css') }}" rel="stylesheet" />
@endpush
@push('javascript')
{{-- <script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js"></script> --}}
<script src="https://cdn.jsdelivr.net/npm/fullcalendar-scheduler@6.1.17/index.global.min.js"></script>
<script>
   document.addEventListener('DOMContentLoaded', function() {
       var calendarEl = document.getElementById('calendar');
       var type = @json($type);
       var students = @json($students);
       var payment_method = @json($payment_method);
       var isMobile = window.innerWidth <= 768;
       var initialCalendarView = 'resourceTimelineWeek';
       var toolbarConf = {
           right: 'today prev,next',
           center: 'title',
           left: 'listWeek,resourceTimeGridDay,resourceTimelineWeek,dayGridMonth',
       };
        
       var initialCalendarView = 'resourceTimelineWeek';
       var initHeight = null;

       if(isMobile) {
            initialCalendarView = 'listWeek';
            initHeight = 'auto';
       }
       
       var calendar = new FullCalendar.Calendar(calendarEl, {
           schedulerLicenseKey: "{{ config('full-calendar.key') }}",
           height:initHeight,
           resourceAreaWidth: '15%',
           nowIndicator: false,
           initialView: initialCalendarView,
           datesAboveResources:true,
           resourceAreaHeaderContent: 'Instructor',
           resources: @json($resources),
           events: @json($events),
           views: {
               resourceTimelineWeek: {
                   slotDuration: { days: 1 },
                   slotLabelFormat: [
                       { weekday: 'short', month: 'short', day: 'numeric' }
                   ],
               }
           },
           firstDay: 1,
           eventContent: function(arg) {
               return {
                   html: `
                   <div style="white-space: normal;">
                       <strong>${arg.event.title}</strong><br/>
                       <small>${arg.event.extendedProps.details || ''}</small><br/>
                       <small>${arg.event.extendedProps.location || ''}</small>
                   </div>
                   `
               };
           },
           eventClick: function(info) {
               const slot_id = info?.event?.extendedProps?.slot_id;
               const isBooked = !!info?.event?.extendedProps?.is_student_assigned;
               const isCompleted = !!info.event?.extendedProps?.is_completed;
               const slot = info.event.extendedProps.slot;
               const availableSeats = info.event.extendedProps.available_seats;
               const student = info.event.extendedProps.student;
               const lesson = info.event.extendedProps.lesson;
               const instructor = info.event.extendedProps.instructor;
               const formattedTime = new Date(slot.date_time.replace(/-/g, "/"))
                   .toLocaleTimeString([], {
                       weekday: 'long', // Full day name
                       year: 'numeric',
                       month: 'long', // Full month name
                       day: 'numeric',
                       hour: '2-digit',
                       minute: '2-digit',
                       hour12: true // 12-hour format with AM/PM
                   });
   
               if (!isCompleted && type == 'Admin') {
                   const swalWithBootstrapButtons = Swal.mixin({
                       customClass: {
                           confirmButton: "btn btn-success",
                           cancelButton: "btn btn-danger",
                       },
                       buttonsStyling: false,
                   });
   
                   if (!!lesson.is_package_lesson) {
                       Swal.fire('Error',
                           'Sorry, admins can\'t book package lesson slots for students.',
                           'error');
                       return; // Stop further execution
                   }
   
   
                   let bookedStudentsHtml = student.length ?
                       `<ul>${student.map(student => 
       `<li>${student?.pivot.friend_name ? student.pivot.friend_name : student.name} ${student.isGuest ? "(Guest)" : ""}</li>`
   ).join('')}</ul>` :
                       "<p class='text-muted'>No students booked yet.</p>";
   
                   let unbookButtonHtml = "";
                   if (isBooked) {
                       unbookButtonHtml =
                           `<button id="unbookBtn" type="button" class="swal2-confirm btn btn-warning">Unbook Students</button>`;
                   }
                   swalWithBootstrapButtons
                       .fire({
                           title: "Book Slot",
                           text: "Please select from the following payment options before completing slot as complete",
                           html: `
                           <form id="swal-form">
                       <div class="flex justify-between gap-2 items-center mb-2">
                           <div>
                           <input type="checkbox" id="guestBooking" onchange="toggleGuestBooking()" />
                           <label for="guestBooking">Guest</label>
                           </div>
                           <div class="mb-2">
                                <p>Available Spots: <strong>${availableSeats}</strong></p>
                           </div>
                       </div>
                       <div class="flex justify-start text-left text-sm">
                       <div>
                           <label><strong>Booked Students:</strong></label><br/>
                            ${bookedStudentsHtml}
                       </div>
                        </div>
                       <div class="form-group" id="student-form">
                           <label class="mb-1">Select Students</label>
                           <select name="student_id[]" id="student_id" class="form-select w-full" multiple>
                               @foreach ($students as $student)
                                   <option value="{{ $student->id }}">
                                       {{ ucfirst($student->name) }}
                                   </option>
                               @endforeach
                           </select>
                       </div>
                       <div id="guestFields" style="display: none;" class="flex flex-col gap-2">
                           <input type="text" id="guestName" class="form-control" placeholder="Guest Name">
                           <input type="text" id="guestPhone" class="form-control" placeholder="Guest Phone Number" pattern="[789][0-9]{9}">
                           <input type="email" id="guestEmail" class="form-control" placeholder="Guest Email Address">
                       </div>
                   </form>
                   <div class="mt-2">
                       ${unbookButtonHtml}
                   </div>
                   `,
                           didRender: () => {
                               document.getElementById("unbookBtn")?.addEventListener(
                                   "click",
                                   function() {
                                       openManageSlotPopup(slot_id, student,
                                           formattedTime, lesson, instructor, slot,
                                           availableSeats);
                                   });
                           },
                           preConfirm: () => {
                               const isGuest = document.getElementById('guestBooking')
                                   .checked;
                               const studentSelect = document.getElementById('student_id');
                               const student_ids = [...studentSelect.selectedOptions].map(
                                   opt => opt.value);
                               const guestName = document.getElementById('guestName')
                                   ?.value;
                               const guestPhone = document.getElementById('guestPhone')
                                   ?.value;
                               const guestEmail = document.getElementById('guestEmail')
                                   ?.value;
                               const phoneRegex = /^[+]?[0-9]{10,15}$/;
   
   
                               if (!student_ids.length && !isGuest || (isGuest && (!
                                       guestName || !
                                       guestPhone || !guestEmail)))
                                   Swal.showValidationMessage('All fields are required');
   
                               if (student_ids.length > availableSeats) {
                                   Swal.showValidationMessage(
                                       `You can only select up to ${availableSeats} students.`
                                   );
                                   return false;
                               }
   
                               if (isGuest && guestPhone && !phoneRegex.test(guestPhone)) {
                                   Swal.showValidationMessage(
                                       'Enter a valid phone number (10-15 digits, optional + prefix)'
                                   );
                                   return false;
                               }
   
                               return {
                                   isGuest,
                                   student_ids,
                                   guestName,
                                   guestPhone,
                                   guestEmail,
                               };
                           },
                           showCancelButton: true,
                           confirmButtonText: "Book",
                           reverseButtons: true,
                       })
                       .then((result) => {
                           const formData = result.value;
                           $.ajax({
                               url: "{{ route('slot.admin') }}",
                               type: 'POST',
                               data: {
                                   _token: $('meta[name="csrf-token"]').attr(
                                       'content'),
                                   isGuest: formData.isGuest ?? false,
                                   student_Ids: formData.student_ids,
                                   guestName: formData.guestName,
                                   guestPhone: formData.guestPhone,
                                   guestEmail: formData.guestEmail,
                                   slot_id: slot_id,
                                   redirect: 1,
                               },
                               success: function(response) {
                                   Swal.fire('Success',
                                       'Form submitted successfully!',
                                       'success');
                                   window.location.reload();
                               },
                               error: function(error) {
                                   Swal.fire('Error',
                                       'There was a problem submitting the form.',
                                       error);
                                   console.log(error);
                               }
   
                           });
   
                       });
               }
               if (isCompleted && type == 'Admin') {
                   let studentsHtml = "<strong>Students Attended:</strong><br/>";
   
                   if (Array.isArray(student) && student.length > 0) {
                       studentsHtml += "<ol style='margin-left: 8px;'>";
                       studentsHtml += student.map((student, index) =>
                           `<li> - ${student.isGuest ? `${student.name} (Guest)` : student.name}</li>`
                       ).join('');
                       studentsHtml += "</ol>";
                   } else {
                       studentsHtml +=
                           "<span style='margin-left: 20px;'>No students attended this slot.</span>";
                   }
   
                   Swal.fire({
                       title: "Completed Slot",
                       html: `
           <div style="text-align: left; font-size: 14px;">
               <span><strong>Slot Start Time:</strong> ${formattedTime}</span><br/>
               <span><strong>Lesson:</strong> ${lesson.lesson_name}</span><br/>
               <span><strong>Instructor:</strong> ${instructor.name}</span><br/>
               <span><strong>Location:</strong> ${slot.location}</span><br/>
               ${studentsHtml}
           </div>
       `,
                       confirmButtonText: "Close",
                       showCloseButton: true,
                   });
               }
   
           },
           headerToolbar: toolbarConf,
           customButtons: {
               customDayButton: {
                   text: 'Day View',
                   click: function() {
                       calendar.changeView('listDay');
                   },
               }
           },
           nowIndicator: true,
       });
   
       calendar.render();
   
       window.toggleGuestBooking = function() {
           const isGuest = document.getElementById('guestBooking').checked;
           document.getElementById('student-form').style.display = isGuest ? 'none' : 'block';
           document.getElementById('guestFields').style.display = isGuest ? 'block' : 'none';
       };
   });
   
   function openManageSlotPopup(slot_id, student, formattedTime, lesson, instructor, slot, availableSeats) {
       const swalWithBootstrapButtons = Swal.mixin({
           customClass: {
               confirmButton: "btn btn-success",
               cancelButton: "btn btn-danger",
           },
           buttonsStyling: false,
       });
   
       let studentsHtml = `
       <label for="unbookStudents"><strong>Select Students to Unbook:</strong></label>
       <select id="unbookStudents" class="form-select w-full" multiple>
   `;
   
       if (Array.isArray(student) && student.length > 0) {
           studentsHtml += student.map(s =>
               `<option value="${s.id}">${s.isGuest ? `${s.name} (Guest)` : s.name}</option>`
           ).join('');
       }
       studentsHtml += `</select>`;
   
       swalWithBootstrapButtons.fire({
           title: "Manage Slot",
           html: `
           <div style="text-align: left; font-size: 14px;">
               <span><strong>Slot Start Time:</strong> ${formattedTime}</span><br/>
               <span><strong>Lesson:</strong> ${lesson.lesson_name}</span><br/>
               <span><strong>Instructor:</strong> ${instructor.name}</span><br/>
               <span><strong>Location:</strong> ${slot.location}</span><br/>
               <span><strong>Available Spots:</strong> ${availableSeats}</span><br/>
               ${studentsHtml}<br/>
           </div>
       `,
           showCancelButton: true,
           confirmButtonText: "Unbook",
           cancelButtonText: "Cancel",
           reverseButtons: true,
           showCloseButton: true,
       }).then((result) => {
           if (result.isConfirmed) {
               const selectedStudents = Array.from(document.getElementById('unbookStudents').selectedOptions)
                   .map(option => option.value);
   
               if (selectedStudents.length === 0) {
                   Swal.showValidationMessage("Please select at least one student to unbook.");
                   return false;
               }
   
               $.ajax({
                   url: "{{ route('slot.update') }}",
                   type: 'POST',
                   data: {
                       _token: $('meta[name="csrf-token"]').attr('content'),
                       unbook: 1,
                       slot_id: slot_id,
                       student_ids: selectedStudents,
                       redirect: 1,
                   },
                   success: function(response) {
                       Swal.fire('Success', 'Students unbooked successfully!', 'success');
                       window.location.reload();
                   },
                   error: function(error) {
                       Swal.fire('Error', 'There was a problem processing the request.');
                       console.log(error);
                   }
               });
           }
       });
   }
</script>
<script src="{{ asset('vendor/intl-tel-input/jquery.mask.js') }}"></script>
<script src="{{ asset('vendor/intl-tel-input/intlTelInput-jquery.min.js') }}"></script>
<script src="{{ asset('vendor/intl-tel-input/utils.min.js') }}"></script>
</script>
@endpush
