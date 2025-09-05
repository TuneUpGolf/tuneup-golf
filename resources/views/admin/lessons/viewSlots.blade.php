 @extends('layouts.main') @section('title', __('Manage Slots')) @section('breadcrumb') <li class="breadcrumb-item"><a
         href="{{ route('home') }}">{{ __('Dashboard') }}</a></li>
 <li class="breadcrumb-item"><a href="{{ route('lesson.index') }}">{{ __('Lesson') }}</a></li>
 <li class="breadcrumb-item">{{ __('Manage Slots') }}</li>
@endsection

@section('content')

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
 <div class="main-content">
     <section class="section">
         <div class="section-body">
             <div class="m-auto col-lg-3 col-md-8 col-xxl-4">

                 <div class="card card-width-manageSlots">
                     <div class="flex justify-between items-center card-header  w-100 ">
                         @if (Auth::user()->type === 'Student')
                             <h5>{{ __('Book Slot') }}</h5>
                             <p>{{ __('Click on an avaialble slot to book') }}</p>
                         @else
                             <div>
                                 <h5>{{ __('Manage Slots') }}</h5>
                                 <div class="pt-2">
                                     <p class="mb-0 text-muted">Name: <strong>{{ $lesson->lesson_name }}</strong>
                                     </p>
                                     @if ($lesson->is_package_lesson)
                                         <span class="badge bg-primary">Package Lesson</span>
                                     @endif
                                 </div>
                             </div>
                             <button type="button" class="btn btn-primary" data-bs-dismiss="modal">
                                 <a class="text-white" href="{{ route('slot.create', ['lesson_id' => $lesson_id]) }}">
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
             {{-- <div id="calendar"></div> --}}
             <div class="custom-calendar-mobile-view">
                 <div class="row">
                     <div class="col-sm-12">
                         <div class="card">
                             <div class="card-header d-flex justify-content-between align-items-center">
                                 <h5> {{ __('Calendar') }} </h5>
                             </div>
                             <div class="card-body">
                                 <div class="form-group">
                                    <label for="slotDate">Date</label>
                                    <div id="slotDate"></div>
                                 </div>

                                 <div id="slotListContainer" class="row"></div>
                             </div>
                         </div>
                     </div>
                 </div>
             </div>
         </div>
     </section>

     <script>
  flatpickr("#slotDate", {
    inline: true,        // 👈 input ki jagah direct calendar show karega
    dateFormat: "Y-m-d",
    minDate: "today"
  });
</script>

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
 <style>
     .choices__inner {
         text-align: left;
     }

     .choices__list--dropdown {
         height: 0;
         z-index: 1;
     }

     .choices__list--dropdown.is-active {
         height: auto;
     }

     .swal2-actions {
         z-index: 0 !important;
     }

     .swal2-html-container {
         overflow: visible !important;
     }

     .choices__list--dropdown .choices__item {
         text-align: left;
     }
 </style>
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
         var authId = @json($authId);
         var students = @json($students);
         var isMobile = window.innerWidth <= 768;
         var initialCalendarView = isMobile ? 'listWeek' : 'timeGridWeek';
         var calendar = new FullCalendar.Calendar(calendarEl, {
             initialView: initialCalendarView,
             eventShortHeight: 45,
             nowIndicator: true,
             slotMinTime: '5:00:00',
             slotMaxTime: '20:00:00',
             events: @json($events),
             eventDidMount: function(info) {
                 if (type == 'Instructor') {
                     const deleteBtn = document.createElement('span');
                     deleteBtn.className = 'fc-delete-btn';
                     deleteBtn.innerHTML = `<i class="ti ti-trash text-white"></i>`;
                     deleteBtn.title = 'Delete';
                     deleteBtn.style.marginLeft = '8px';
                     deleteBtn.style.cursor = 'pointer';
                     deleteBtn.style.color = 'red';
                     deleteBtn.addEventListener('click', function(e) {
                         e.stopPropagation();
                         const slot_id = info?.event?.extendedProps?.slot_id;
                         if (confirm(`Do you want to delete this slot?`) && slot_id > 0) {
                             info.event.remove();
                             $.ajax({
                                 url: "{{ route('slot.delete') }}",
                                 type: 'POST',
                                 data: {
                                     _token: $('meta[name="csrf-token"]').attr(
                                         'content'),
                                     id: slot_id,
                                 },
                                 success: function(response) {
                                     Swal.fire('Success', response.message,
                                         'success');
                                 },
                                 error: function(error) {
                                     Swal.fire('Error',
                                         'There was a problem deleting the slot.',
                                         error);
                                     console.log(error);
                                 }
                             });
                         }
                     });

                     const titleContainer = info.el.querySelector('.fc-event-title-container');
                     if (titleContainer) {
                         titleContainer.appendChild(deleteBtn);
                     }
                 }
             },
             eventClick: function(info) {
                 const slot_id = info?.event?.extendedProps?.slot_id;
                 const slot = info.event.extendedProps.slot;
                 const paymentMethod = slot.lesson.payment_method;
                 const isFullyBooked = info?.event?.extendedProps?.isFullyBooked;
                 const availableSeats = info.event.extendedProps.available_seats;
                 const student = info?.event?.extendedProps?.student;
                 const lesson = slot.lesson;
                 const instructor = info.event.extendedProps.instructor;
                 const isBooked = !!info?.event?.extendedProps?.is_student_assigned;
                 const isCompleted = !!info.event?.extendedProps?.is_completed;
                 const isAuthStudentBooked = !!(student.some(obj => obj.id == authId));
                 const isPackageLesson = !!lesson.is_package_lesson;

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

                 if (type == 'Instructor' && !isBooked && !isCompleted) {
                     const swalWithBootstrapButtons = Swal.mixin({
                         customClass: {
                             confirmButton: "btn btn-success",
                             cancelButton: "btn btn-danger",
                         },
                         buttonsStyling: false,
                     });

                     if (!!lesson.is_package_lesson) {
                         Swal.fire('Error',
                             'Instructors can\'t book package lesson slots for students.',
                             'error');
                         return; // Stop further execution
                     }

                     swalWithBootstrapButtons
                         .fire({
                             title: "Book Slot",
                             html: `
                        <form id="swal-form">
                        <div class="flex justify-start mb-1">
                        <span>Available Spots: <strong>${availableSeats}</strong></span>
                        </div>
                        <div class="form-group" id="student-form">
                             <div class="flex justify-start">
                            <label class="mb-1">Select Students</label>
                            </div>
                            <select name="student_id[]" id="student_id" class="form-select w-full" multiple>
                                @foreach ($students as $student)
                                    <option value="{{ $student->id }}">
                                        {{ ucfirst($student->name) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        </form>`,

                             didOpen: () => {
                                 const choices = new Choices(document.getElementById(
                                     'student_id'), {
                                     searchEnabled: true
                                 });
                             },
                             preConfirm: () => {
                                 const studentSelect = document.getElementById('student_id');
                                 const student_ids = [...studentSelect.selectedOptions].map(
                                     opt => opt.value);

                                 if (student_ids.length > availableSeats)
                                     Swal.showValidationMessage(
                                         `You can only select up to ${availableSeats} students.`
                                     );

                                 return {
                                     student_ids,
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
                                     isGuest: false,
                                     student_Ids: formData.student_ids,
                                     slot_id: slot_id,
                                     redirect: 1,
                                 },
                                 success: function(response) {
                                     Swal.fire('Success',
                                         'Form submitted successfully!',
                                         'success');
                                     showPageLoader();
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
                 if (type == 'Instructor' && isBooked && !isCompleted) {
                     const swalWithBootstrapButtons = Swal.mixin({
                         customClass: {
                             confirmButton: "btn btn-success",
                             cancelButton: "btn btn-danger",
                         },
                         buttonsStyling: false,
                     });

                     if (isPackageLesson) {
                         // Extract list of booked students
                         let bookedStudentsHtml =
                             "<strong style='display: block; text-align: left; margin-bottom: 5px;'>📋 Booked Students:</strong>";
                         bookedStudentsHtml += "<ol style='text-align: left; padding-left: 20px;'>";

                         if (slot.student.length > 0) {
                             slot.student.forEach((student, index) => {
                                 let studentName = student.pivot.isFriend ?
                                     `${student.pivot.friend_name} (Friend: ${student.name})` :
                                     student.name;

                                 bookedStudentsHtml +=
                                     `<li>${index + 1}. ${studentName}</li>`;
                             });
                         } else {
                             bookedStudentsHtml += "<li>No students booked yet.</li>";
                         }

                         bookedStudentsHtml += "</ol>";

                         Swal.fire({
                             title: "Confirm Slot Completion",
                             html: `
            <p style="text-align: left;">Are you sure you want to complete this lesson slot?</p>
            ${bookedStudentsHtml}
        `,
                             icon: "warning",
                             showCancelButton: true,
                             confirmButtonText: "Confirm",
                             cancelButtonText: "Cancel",
                         }).then((result) => {
                             if (result.isConfirmed) {
                                 // Send AJAX request to complete slot
                                 $.ajax({
                                     url: "{{ route('slot.complete') }}",
                                     type: "POST",
                                     data: {
                                         _token: $('meta[name="csrf-token"]').attr(
                                             'content'),
                                         payment_method: "online",
                                         slot_id: slot_id,
                                         redirect: 1,
                                     },
                                     success: function(response) {
                                         Swal.fire("Success!",
                                                 "Lesson slot has been completed.",
                                                 "success")
                                             .then(() => {
                                                 showPageLoader();
                                                 location
                                                     .reload(); // Reload page after success
                                             });
                                     },
                                     error: function(xhr) {
                                         Swal.fire("Error!",
                                             "Something went wrong. Please try again.",
                                             "error");
                                     }
                                 });
                             }
                         });

                         return;
                     }


                     let studentsHtml = `
                     <label for="unbookStudents"><strong>Select Students to Unbook:</strong></label>
                     <select id="unbookStudents" class="form-select w-full" multiple>
                    `;

                     if (Array.isArray(student) && student.length > 0) {
                         studentsHtml += student.map(student =>
                             `<option value="${student.id}">${student.isGuest ? `${student.name} (Guest)` : student.name}</option>`
                         ).join('');
                     }
                     studentsHtml += `</select>`;
                     swalWithBootstrapButtons
                         .fire({
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
                             confirmButtonText: "Complete",
                             cancelButtonText: "Unreserve",
                             reverseButtons: true,
                             showCloseButton: true,
                         })
                         .then((result) => {

                             if (!!result.isConfirmed) {

                                 const swalWithBootstrapButtons = Swal.mixin({
                                     customClass: {
                                         confirmButton: "btn btn-success",
                                         cancelButton: "btn btn-primary",
                                     },
                                     buttonsStyling: false,
                                 });
                                 if (paymentMethod == 'both') {
                                     console.log(instructor.is_stripe_connected);
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
                                                 if (instructor.is_stripe_connected) {
                                                     $("#slot_id").val(slot_id);
                                                     $("#payment_method").val('online');
                                                     $("#form").submit()
                                                 } else {
                                                     Swal.fire({
                                                         title: "Stripe Setup Required",
                                                         text: "Please set up Stripe integration to proceed.",
                                                         icon: "warning",
                                                         confirmButtonText: "OK"
                                                     });
                                                 }
                                             } else {
                                                 $("#slot_id").val(slot_id);
                                                 $("#payment_method").val('cash');
                                                 $("#form").submit()
                                             }
                                         });
                                 }
                                 if (paymentMethod == 'online') {
                                     $("#slot_id").val(slot_id);
                                     $("#payment_method").val('online');
                                     $("#form").submit()
                                 }
                                 if (paymentMethod == 'cash') {
                                     $("#slot_id").val(slot_id);
                                     $("#payment_method").val('cash');
                                     $("#form").submit()
                                 }
                             } else if (result.dismiss == 'cancel') {
                                 const selectedStudents = Array.from(document
                                         .getElementById(
                                             'unbookStudents').selectedOptions)
                                     .map(option => option.value);

                                 if (selectedStudents.length === 0) {
                                     Swal.showValidationMessage(
                                         "Please select at least one student to unbook."
                                     );
                                 }
                                 $.ajax({
                                     url: "{{ route('slot.update') }}",
                                     type: 'POST',
                                     data: {
                                         _token: $('meta[name="csrf-token"]').attr(
                                             'content'),
                                         unbook: 1,
                                         slot_id: slot_id,
                                         student_ids: selectedStudents,
                                         redirect: 1,
                                     },
                                     success: function(response) {
                                         Swal.fire('Success',
                                             'Form submitted successfully!',
                                             'success');
                                         showPageLoader();
                                         window.location.reload();
                                     },
                                     error: function(error) {
                                         Swal.fire('Error',
                                             'There was a problem submitting the form.',
                                             error);
                                         console.log(error);
                                     }

                                 });
                             }
                         });
                 }
                 if (type == 'Student' && !isAuthStudentBooked && !isFullyBooked && !isCompleted) {
                     let studentsHtml = `
        <label for="studentFriends"><strong>Book for Friends (Optional):</strong></label>
        <input type="text" id="studentFriends" class="form-control" placeholder="Enter friend names, separated by commas">
    `;
                     Swal.fire({
                         title: "Slot Details",
                         html: `
                    <div style="text-align: left; font-size: 14px;">
                    <span><strong>Slot Start Time:</strong> ${formattedTime}</span><br/>
                    <span><strong>Location:</strong> ${slot.location}</span><br/>
                    <span><strong>Lesson:</strong> ${lesson.lesson_name}</span><br/>
                    <span><strong>Instructor:</strong> ${instructor.name}</span><br/>
                    <span><strong>Available Spots:</strong> ${availableSeats}</span><br/>
                         ${studentsHtml}
                    </div>
                         `,
                         showCancelButton: true,
                         confirmButtonText: "Book Slot",
                         cancelButtonText: "Cancel",
                         preConfirm: () => {
                             const friendNames = document.getElementById(
                                 'studentFriends')?.value.trim();
                             const friendNamesArray = friendNames ? friendNames
                                 .split(',').map(name => name.trim()) : [];
                             return {
                                 friendNames: friendNamesArray
                             };
                         }
                     }).then((result) => {
                         if (result.isConfirmed) {
                             $.ajax({
                                 url: "{{ route('slot.book') }}",
                                 type: "POST",
                                 data: {
                                     _token: $('meta[name="csrf-token"]').attr(
                                         'content'),
                                     slot_id: slot_id,
                                     friend_names: result.value.friendNames || "",
                                     redirect: 0,
                                 },
                                 success: function(response) {
                                     if (response.payment_url) {
                                         window.location.href = response
                                             .payment_url;
                                     } else {
                                         Swal.fire({
                                             icon: 'success',
                                             title: 'Booking Successful',
                                             text: response.message ||
                                                 'You have successfully booked the slot.',
                                         }).then(() => {
                                             showPageLoader();
                                             location
                                                 .reload(); // Reload page to update UI
                                         });
                                     }
                                 },
                                 error: function(xhr) {
                                     Swal.fire({
                                         icon: 'error',
                                         title: 'Booking Failed',
                                         text: xhr.responseJSON
                                             ?.message ||
                                             'An error occurred while booking the slot.',
                                     });
                                 }
                             });
                         }
                     });
                 }
                 if (type == 'Student' && isAuthStudentBooked) {
                     Swal.fire({
                         title: "Unbook Slot",
                         text: "Are you sure you want to unbook this slot?",
                         icon: "warning",
                         showCancelButton: true,
                         confirmButtonText: "Yes, unbook",
                         cancelButtonText: "Cancel",
                         reverseButtons: true,
                         html: `
                            <textarea name="note" id="notes" class="form-control" placeholder="Enter note here" cols="10" rows="5"></textarea>
                         `,
                     }).then((result) => {
                         const notes = document.querySelector("#notes").value;
                         if (result.isConfirmed) {
                             $.ajax({
                                 url: "{{ route('slot.update') }}",
                                 type: "POST",
                                 data: {
                                     _token: $('meta[name="csrf-token"]').attr(
                                         'content'),
                                     unbook: 1,
                                     slot_id: slot_id,
                                     student_ids: [authId],
                                     redirect: 1,
                                     notes: notes,
                                 },
                                 success: function(response) {
                                     Swal.fire("Success",
                                         "Slot unbooked successfully!",
                                         "success");
                                     showPageLoader();
                                     window.location.reload();
                                 },
                                 error: function(error) {
                                     Swal.fire("Error",
                                         "Failed to unbook the slot.",
                                         "error");
                                     console.log(error);
                                 }
                             });
                         }
                     });
                 }
             },
         });

         calendar.render();

     });

     // custom view of calendar
     var type = @json($type);
     var authId = @json($authId);
     var students = @json($students);
     var eventsData = @json($events); // same data FullCalendar uses
     const allEvents = @json($events); // same events used in FullCalendar
     // var isMobile = window.innerWidth <= 600;

     // normalize event object (supports both direct keys and extendedProps) ----
     function normalizeEventObject(raw) {
         const p = raw?.extendedProps || raw || {};
         const slot = p.slot || raw.slot || {};
         const lesson = slot.lesson || p.lesson || raw.lesson || {};
         const instructor = p.instructor || raw.instructor || slot.instructor || {};
         const student = p.student || raw.student || slot.student || [];
         const availableSeats = (p.available_seats ?? raw.available_seats ?? 0);
         const isBooked = !!(p.is_student_assigned ?? raw.is_student_assigned ?? false);
         const isCompleted = !!(p.is_completed ?? raw.is_completed ?? false);
         const isFullyBooked = !!(p.isFullyBooked ?? raw.isFullyBooked ?? false);
         const slot_id = (p.slot_id ?? raw.slot_id ?? slot.id ?? raw.id);
         const paymentMethod = lesson.payment_method;
         const isPackageLesson = !!lesson.is_package_lesson;
         const isAuthStudentBooked = Array.isArray(student) && student.some(o => o.id == authId);

         // Use the slot datetime if available, fallback to start
         const dtStr = slot.date_time || p.date_time || raw.start || '';
         const formattedTime = dtStr ?
             new Date(dtStr.replace(/-/g, "/")).toLocaleTimeString([], {
                 weekday: 'long',
                 year: 'numeric',
                 month: 'long',
                 day: 'numeric',
                 hour: '2-digit',
                 minute: '2-digit',
                 hour12: true
             }) :
             '';

         return {
             type,
             authId,
             students,
             slot_id,
             slot,
             paymentMethod,
             isFullyBooked,
             availableSeats,
             student,
             lesson,
             instructor,
             isBooked,
             isCompleted,
             isAuthStudentBooked,
             isPackageLesson,
             formattedTime
         };
     }

     // all event logic for mobile screen here ----
     function handleSlotAction(payload) {
         const {
             type,
             authId,
             slot_id,
             slot,
             paymentMethod,
             isFullyBooked,
             availableSeats,
             student,
             lesson,
             instructor,
             isBooked,
             isCompleted,
             isAuthStudentBooked,
             isPackageLesson,
             formattedTime
         } = payload;

         // ========== INSTRUCTOR: Delete (handled by a separate button elsewhere) ==========
         if (type == 'Instructor') {

         }

         // ========== INSTRUCTOR: Book (not booked + not completed) ==========
         if (type == 'Instructor' && !isBooked && !isCompleted) {
             const swalWithBootstrapButtons = Swal.mixin({
                 customClass: {
                     confirmButton: "btn btn-success",
                     cancelButton: "btn btn-danger"
                 },
                 buttonsStyling: false,
             });

             if (isPackageLesson) {
                 Swal.fire('Error', 'Instructors can\'t book package lesson slots for students.', 'error');
                 return;
             }

             swalWithBootstrapButtons.fire({
                     title: "Book Slot",
                     html: `
                    <form id="swal-form">
                      <div class="flex justify-start mb-1">
                        <span>Available Spots: <strong>${availableSeats}</strong></span>
                      </div>
                      <div class="form-group" id="student-form">
                        <div class="flex justify-start">
                          <label class="mb-1">Select Students</label>
                        </div>
                        <select name="student_id[]" id="student_id" class="form-select w-full" multiple>
                          @foreach ($students as $student)
                            <option value="{{ $student->id }}">{{ ucfirst($student->name) }}</option>
                          @endforeach
                        </select>
                      </div>
                    </form>
                `,
                     didOpen: () => {
                         const choices = new Choices(document.getElementById('student_id'), {
                             searchEnabled: true
                         });
                     },
                     preConfirm: () => {
                         const el = document.getElementById('student_id');
                         const ids = [...el.selectedOptions].map(opt => opt.value);
                         if (ids.length > availableSeats) {
                             Swal.showValidationMessage(`You can only select up to ${availableSeats} students.`);
                         }
                         return {
                             student_ids: ids
                         };
                     },
                     showCancelButton: true,
                     confirmButtonText: "Book",
                     reverseButtons: true,
                 })
                 .then((result) => {
                     const formData = result.value;
                     if (!formData) return;

                     $.ajax({
                         url: "{{ route('slot.admin') }}",
                         type: 'POST',
                         data: {
                             _token: $('meta[name="csrf-token"]').attr('content'),
                             isGuest: false,
                             student_Ids: formData.student_ids,
                             slot_id: slot_id,
                             redirect: 1,
                         },
                         success: function() {
                             Swal.fire('Success', 'Form submitted successfully!', 'success');
                             showPageLoader();
                             window.location.reload();
                         },
                         error: function(error) {
                             Swal.fire('Error', 'There was a problem submitting the form.', 'error');
                         }
                     });
                 });

             return; // stop here
         }

         // ========== INSTRUCTOR: Manage (booked + not completed) ==========
         if (type == 'Instructor' && isBooked && !isCompleted) {
             const swalWithBootstrapButtons = Swal.mixin({
                 customClass: {
                     confirmButton: "btn btn-success",
                     cancelButton: "btn btn-danger"
                 },
                 buttonsStyling: false,
             });

             if (isPackageLesson) {
                 // show booked students list & confirm completion
                 let bookedStudentsHtml =
                     "<strong style='display: block; text-align: left; margin-bottom: 5px;'>📋 Booked Students:</strong><ol style='text-align: left; padding-left: 20px;'>";
                 if ((slot.student || []).length > 0) {
                     slot.student.forEach((s, index) => {
                         const name = s.pivot?.isFriend ? `${s.pivot.friend_name} (Friend: ${s.name})` : s.name;
                         bookedStudentsHtml += `<li>${index + 1}. ${name}</li>`;
                     });
                 } else {
                     bookedStudentsHtml += "<li>No students booked yet.</li>";
                 }
                 bookedStudentsHtml += "</ol>";

                 Swal.fire({
                     title: "Confirm Slot Completion",
                     html: `<p style="text-align: left;">Are you sure you want to complete this lesson slot?</p>${bookedStudentsHtml}`,
                     icon: "warning",
                     showCancelButton: true,
                     confirmButtonText: "Confirm",
                     cancelButtonText: "Cancel",
                 }).then((result) => {
                     if (result.isConfirmed) {
                         $.ajax({
                             url: "{{ route('slot.complete') }}",
                             type: "POST",
                             data: {
                                 _token: $('meta[name="csrf-token"]').attr('content'),
                                 payment_method: "online",
                                 slot_id: slot_id,
                                 redirect: 1,
                             },
                             success: function() {
                                 Swal.fire("Success!", "Lesson slot has been completed.", "success")
                                     .then(() => {
                                         showPageLoader();
                                         location.reload();
                                     });
                             },
                             error: function() {
                                 Swal.fire("Error!", "Something went wrong. Please try again.",
                                     "error");
                             }
                         });
                     }
                 });
                 return;
             }

             // Non-package lesson flow
             let studentsHtml = `
              <label for="unbookStudents"><strong>Select Students to Unbook:</strong></label>
              <select id="unbookStudents" class="form-select w-full" multiple>
            `;
             if (Array.isArray(student) && student.length > 0) {
                 studentsHtml += student.map(s =>
                     `<option value="${s.id}">${s.isGuest ? `${s.name} (Guest)` : s.name}</option>`).join('');
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
                     confirmButtonText: "Complete",
                     cancelButtonText: "Unreserve",
                     reverseButtons: true,
                     showCloseButton: true,
                 })
                 .then((result) => {
                     if (!!result.isConfirmed) {
                         const sw = Swal.mixin({
                             customClass: {
                                 confirmButton: "btn btn-success",
                                 cancelButton: "btn btn-primary"
                             },
                             buttonsStyling: false,
                         });

                         if (paymentMethod == 'both') {
                             sw.fire({
                                 title: "Choose Payment Method",
                                 text: "Please select from the following payment options before completing slot as complete",
                                 icon: "warning",
                                 showCancelButton: true,
                                 confirmButtonText: "Online",
                                 cancelButtonText: "Cash",
                                 reverseButtons: true,
                             }).then((r) => {
                                 if (r.isConfirmed) {
                                     if (instructor.is_stripe_connected) {
                                         $("#slot_id").val(slot_id);
                                         $("#payment_method").val('online');
                                         $("#form").submit();
                                     } else {
                                         Swal.fire({
                                             title: "Stripe Setup Required",
                                             text: "Please set up Stripe integration to proceed.",
                                             icon: "warning",
                                             confirmButtonText: "OK"
                                         });
                                     }
                                 } else {
                                     $("#slot_id").val(slot_id);
                                     $("#payment_method").val('cash');
                                     $("#form").submit();
                                 }
                             });
                         }
                         if (paymentMethod == 'online') {
                             $("#slot_id").val(slot_id);
                             $("#payment_method").val('online');
                             $("#form").submit();
                         }
                         if (paymentMethod == 'cash') {
                             $("#slot_id").val(slot_id);
                             $("#payment_method").val('cash');
                             $("#form").submit();
                         }

                     } else if (result.dismiss == 'cancel') {
                         const selectedStudents = Array.from(document.getElementById('unbookStudents')
                             .selectedOptions).map(o => o.value);
                         if (selectedStudents.length === 0) {
                             Swal.showValidationMessage("Please select at least one student to unbook.");
                             return;
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
                             success: function() {
                                 Swal.fire('Success', 'Form submitted successfully!', 'success');
                                 showPageLoader();
                                 window.location.reload();
                             },
                             error: function(error) {
                                 Swal.fire('Error', 'There was a problem submitting the form.', 'error');
                             }
                         });
                     }
                 });

             return; // stop here
         }

         // ========== STUDENT: Book ==========
         if (type == 'Student' && !isAuthStudentBooked && !isFullyBooked && !isCompleted) {
             let friendsHtml = `
              <label for="studentFriends"><strong>Book for Friends (Optional):</strong></label>
              <input type="text" id="studentFriends" class="form-control" placeholder="Enter friend names, separated by commas">
              <textarea name="notes" id="notes" class="form-control mt-2" placeholder="Enter note here" cols="10" rows="5"></textarea>
            `;
             Swal.fire({
                 title: "Slot Details",
                 html: `
                  <div style="text-align: left; font-size: 14px;">
                    <span><strong>Slot Start Time:</strong> ${formattedTime}</span><br/>
                    <span><strong>Location:</strong> ${slot.location}</span><br/>
                    <span><strong>Lesson:</strong> ${lesson.lesson_name}</span><br/>
                    <span><strong>Instructor:</strong> ${instructor.name}</span><br/>
                    <span><strong>Available Spots:</strong> ${availableSeats}</span><br/>
                    
                    ${friendsHtml}
                  </div>
                `,
                 showCancelButton: true,
                 confirmButtonText: "Book Slot",
                 cancelButtonText: "Cancel",
                 preConfirm: () => {
                     const v = document.getElementById('studentFriends')?.value.trim();
                     const arr = v ? v.split(',').map(x => x.trim()).filter(Boolean) : [];

                     const notes = document.getElementById('notes')?.value.trim() || "";

                     return {
                         friendNames: arr,
                         note: notes
                     };
                 }
             }).then((r) => {
                 if (!r.isConfirmed) return;
                 $.ajax({
                     url: "{{ route('slot.book') }}",
                     type: "POST",
                     data: {
                         _token: $('meta[name="csrf-token"]').attr('content'),
                         slot_id: slot_id,
                         friend_names: r.value.friendNames || "",
                         note: r.value.note || "",
                         redirect: 0,
                     },
                     success: function(response) {
                         if (response.payment_url) {
                             window.location.href = response.payment_url;
                         } else {
                             Swal.fire({
                                     icon: 'success',
                                     title: 'Booking Successful',
                                     text: response.message ||
                                         'You have successfully booked the slot.'
                                 })
                                 .then(() => {
                                     showPageLoader();
                                     location.reload();
                                 });
                         }
                     },
                     error: function(xhr) {
                         Swal.fire({
                             icon: 'error',
                             title: 'Booking Failed',
                             text: xhr.responseJSON?.message ||
                                 'An error occurred while booking the slot.'
                         });
                     }
                 });
             });
             return;
         }

         // ========== STUDENT: Unbook ==========  
         if (type == 'Student' && isAuthStudentBooked) {
             Swal.fire({
                 title: "Unbook Slot",
                 text: "Are you sure you want to unbook this slot?",
                 icon: "warning",
                 showCancelButton: true,
                 confirmButtonText: "Yes, unbook",
                 cancelButtonText: "Cancel",
                 reverseButtons: true,
                 html: `<textarea name="note" id="notes" class="form-control" placeholder="Enter note here" cols="10" rows="5"></textarea>`,
             }).then((result) => {
                 const notes = document.querySelector("#notes")?.value || '';
                 if (!result.isConfirmed) return;
                 $.ajax({
                     url: "{{ route('slot.update') }}",
                     type: "POST",
                     data: {
                         _token: $('meta[name="csrf-token"]').attr('content'),
                         unbook: 1,
                         slot_id: slot_id,
                         student_ids: [authId],
                         redirect: 1,
                         notes: notes,
                     },
                     success: function() {
                         Swal.fire("Success", "Slot unbooked successfully!", "success");
                         showPageLoader();
                         window.location.reload();
                     },
                     error: function(error) {
                         Swal.fire("Error", "Failed to unbook the slot.", "error");
                     }
                 });
             });
             return;
         }
     }

     // format time on mobile screen
     function formatTime(date) {
        console.log('f1',date)
        let hours = date.getHours();
        console.log('f2',hours)
        const minutes = date.getMinutes().toString().padStart(2, "0");
        console.log('f3',minutes)
        const ampm = hours >= 12 ? "pm" : "am";
        console.log('f4',ampm)
        hours = hours % 12;
        console.log('f5',hours)
        hours = hours ? hours : 12; // 0 => 12
        console.log('f6',hours)

        console.log('f7',hours, minutes, ampm)

         return `${hours}:${minutes}${ampm}`;
     }

     // rendering only for mobile view
     function renderMobileSlots(selectedDate) {
         const container = $("#slotListContainer");
         container.empty();

         const slotsForDate = eventsData.filter(ev => {
             const startStr = (ev.slot?.date_time || ev.start || '').toString();
             if (!startStr) return false;
             const iso = new Date(startStr.replace(/-/g, "/")).toISOString().split("T")[0];
             return iso === selectedDate;
         });

         if (slotsForDate.length === 0) {
             container.html("<p>No slots available for this date.</p>");
             return;
         }

         slotsForDate.forEach(ev => {
             const payload = normalizeEventObject(ev);

             const lessonText =
                 `${payload.lesson?.lesson_name || 'Lesson'} (${payload.lesson?.booked || 0}/${payload.lesson?.capacity || 1})`;
             const start = new Date(payload.slot.date_time); // e.g. "2025-09-02 13:00:00"

             console.log('1', start)
             const durationInMinutes = payload.slot.lesson.lesson_duration * 60; // 0.5 -> 30 mins
             console.log('2', durationInMinutes)
             const end = new Date(start.getTime() + durationInMinutes * 60000);
             console.log('3', end)
             const formattedTimeRange = `${formatTime(start)} - ${formatTime(end)}`;
             console.log('4', formattedTimeRange)
             
             const myClass = ev.className.split(' ')[0] || '';

             const card = $(`
            <div class="col-md-6 col-sm-12 col-lg-4">
                <div class="card mb-2 p-2 border rounded shadow-sm d-flex justify-content-between align-items-center flex-row ${ myClass }">
                  <div class="slot-action">
                    <strong>${formattedTimeRange}</strong> ${lessonText}
                  </div>
                  <div class="gap-2">
                    ${type == 'Instructor'
                      ? `<button class="btn btn-sm btn-danger del-btn" title="Delete"><i class="ti ti-trash"></i></button>`
                      : ``}
                  </div>                                                                                                                                                                                                                                                                   
                </div>
            </div>
            `);

             // Delete (mobile)
             card.find('.del-btn').on('click', function(e) {
                 e.stopPropagation();
                 if (!payload.slot_id) return;
                 if (confirm(`Do you want to delete this slot?`)) {
                     const selfCard = $(this).closest('.card');
                     $.ajax({
                         url: "{{ route('slot.delete') }}",
                         type: 'POST',
                         data: {
                             _token: $('meta[name="csrf-token"]').attr('content'),
                             id: payload.slot_id
                         },
                         success: function(response) {
                             Swal.fire('Success', response.message, 'success');
                             selfCard.remove();
                         },
                         error: function(error) {
                             Swal.fire('Error', 'There was a problem deleting the slot.',
                                 'error');
                         }
                     });
                 }
             });

             // Action (mobile) -> reuse same logic
             card.find('.slot-action').on('click', function() {
                 handleSlotAction(payload);
             });

             container.append(card);
         });
     }


     //custom calendar view
     $('#slotDate').change(function() {

         const selectedDate = $(this).val();
         renderMobileSlots(selectedDate);

     })

     // today's date on mobile and render:
     // if (isMobile) {
     const today = new Date().toISOString().split('T')[0];
     $('#slotDate').val(today).trigger('change');
     // }
 </script>
 <script src="{{ asset('vendor/intl-tel-input/jquery.mask.js') }}"></script>
 <script src="{{ asset('vendor/intl-tel-input/intlTelInput-jquery.min.js') }}"></script>
 <script src="{{ asset('vendor/intl-tel-input/utils.min.js') }}"></script>
 </script>
@endpush
