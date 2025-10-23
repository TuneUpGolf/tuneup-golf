@extends('layouts.main') @section('title', __('Dashboard'))
@section('breadcrumb') <li class="breadcrumb-item">
    <a href="{{ route('home') }}">{{ __('Dashboard') }}</a>
</li>
<li class="breadcrumb-item"><a href="{{ route('lesson.index') }}">{{ __('Lesson') }}</a></li>
<li class="breadcrumb-item">{{ __('Dashboard') }}</li>
@endsection
@section('content')
<div class="main-content">
    <section class="section">
        <div class="section-body">
            <div class="m-auto col-lg-6 col-md-8 col-xxl-4">
                <div class="card">
                    <div>
                        <div class="justify-between items-center card-header w-100">
                            <form id="filterForm">
                              
                                <div class="mb-3">
                                    <div class="form-label">
                                        <strong>Select Lessons</strong>
                                    </div>

                                    <div class="lesson-checkboxes-container">
                                        <div class="lesson-checkboxes">
                                            <div class="lesson-list">
                                                @foreach ($lessons as $lesson)
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" 
                                                            name="lesson_ids[]" 
                                                            value="{{ $lesson->id }}" 
                                                            id="lesson_{{ $lesson->id }}"
                                                            {{ in_array($lesson->id, (array) request()->input('lesson_ids', [])) ? 'checked' : '' }}>
                                                        <label class="form-check-label" for="lesson_{{ $lesson->id }}">
                                                            {{ ucfirst($lesson->lesson_name) }}
                                                        </label>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                
                                <div class="mb-3">
                                    <!-- <label class="form-label"><strong>View Filters</strong></label> -->
                                    <div class="d-flex flex-column gap-2">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="viewScheduled" 
                                                name="view_scheduled" value="1" 
                                                {{ request()->input('view_scheduled') ? 'checked' : '' }}>
                                            <label class="form-check-label" for="viewScheduled">
                                                View Scheduled Lessons
                                            </label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="hideAvailable" 
                                                name="hide_available" value="1" 
                                                {{ request()->input('hide_available') ? 'checked' : '' }}>
                                            <label class="form-check-label" for="hideAvailable">
                                                Hide Available Lessons
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                        <div class="flex flex-col justify-center my-2 gap-2">
                            <div>
                                <div class="flex justify-center gap-2 items items-baseline">
                                    <label for="calendar-date" class="font-semibold">Select Date:</label>
                                    <input type="date" id="calendar-date" class="form-control w-auto" />
                                </div>
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
                                <div class="flex gap-1 items-center">
                                    <div class="blocked-key"></div>
                                    <span>Blocked</span>
                                </div>
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

    .fc-event-main-frame {
        flex-direction: column !important;
    }

    .fc-event-time::after {
        content: none !important;
    }

    .fc-event,
    .fc-event:not([href]) {
        padding: 0 !important;
    }

    .fc-event.event-free {
        background-color: #007BFF !important;
        border-color: #007BFF !important;
        color: #fff !important;
    }

    /* Booked slots (yellow) */
    .fc-event.event-booked {
        background-color: #C5B706 !important;
        border-color: #C5B706 !important;
        color: #000 !important;
    }

    /* Completed slots (green) */
    .fc-event.event-completed {
        background-color: #28A745 !important;
        border-color: #28A745 !important;
        color: #fff !important;
    }

    /* Blocked slots (red) */
    .fc-event.event-blocked {
        background-color: #FF3D41 !important;
        border-color: #FF3D41 !important;
        color: #fff !important;
    }

    .lesson-checkboxes {
    border: 1px solid #dee2e6;
    border-radius: 0.375rem;
    padding: 10px;
    max-height: 250px; /* fixed visible height */
    overflow-y: auto;  /* scroll when too many lessons */
    }

    /* Optional — makes scrollbar look cleaner */
    .lesson-checkboxes::-webkit-scrollbar {
        width: 6px;
    }
    .lesson-checkboxes::-webkit-scrollbar-thumb {
        background: #adb5bd;
        border-radius: 4px;
    }
    .lesson-checkboxes::-webkit-scrollbar-thumb:hover {
        background: #868e96;
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
<link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/css/bootstrap-datepicker.min.css" rel="stylesheet" />
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/js/bootstrap-datepicker.min.js"></script>


<script>
    //now need to work on relaoding of page///
    document.addEventListener('DOMContentLoaded', function() {
         // Filter form handling
        const filterForm = document.getElementById('filterForm');
        const lessonSelect = document.getElementById('lesson_id');
        const viewScheduledCheckbox = document.getElementById('viewScheduled');
        const hideAvailableCheckbox = document.getElementById('hideAvailable');
        
        // Remove auto-submit and replace with AJAX calls
        document.querySelectorAll('.lesson-checkboxes input[type="checkbox"]').forEach(checkbox => {
            checkbox.addEventListener('change', function(e) {
                e.preventDefault();
                updateCalendarWithCurrentFilters();
            });
        });

          if (viewScheduledCheckbox) {
            viewScheduledCheckbox.addEventListener('change', function(e) {
                e.preventDefault();
                updateCalendarWithCurrentFilters();
            });
        }

        if (hideAvailableCheckbox) {
            hideAvailableCheckbox.addEventListener('change', function(e) {
                e.preventDefault();
                updateCalendarWithCurrentFilters();
            });
        }

          filterForm.addEventListener('submit', function(e) {
            e.preventDefault();
            updateCalendarWithCurrentFilters();
        });

         // Lesson checkboxes expand/collapse functionality
        const selectLessonsLabel = document.querySelector('.form-label strong');
        const lessonCheckboxesContainer = document.querySelector('.lesson-checkboxes');

        if (selectLessonsLabel && lessonCheckboxesContainer) {
            selectLessonsLabel.style.cursor = 'pointer';
            selectLessonsLabel.addEventListener('click', function() {
                if (lessonCheckboxesContainer.style.maxHeight === '0px' || !lessonCheckboxesContainer.style.maxHeight) {
                    lessonCheckboxesContainer.style.maxHeight = '200px';
                } else {
                    lessonCheckboxesContainer.style.maxHeight = '0px';
                }
            });
            
            // Auto-expand if any lessons are already selected
            const hasSelectedLessons = document.querySelectorAll('.lesson-checkboxes input[type="checkbox"]:checked').length > 0;
            if (hasSelectedLessons) {
                lessonCheckboxesContainer.style.maxHeight = '200px';
            }
        }

        var calendarEl = document.getElementById('calendar');

        var type = @json($type);
        var students = @json($students);
        var payment_method = @json($payment_method);
        var isMobile = window.innerWidth <= 768;
        var initialCalendarView = isMobile ? 'listWeek' : 'timeGridWeek';
        var blockSlots = @json($blockSlots ?? []);

          // Get filter states from request
        var viewScheduled = @json(request()->input('view_scheduled', false));
        var hideAvailable = @json(request()->input('hide_available', false));
         // Filter events based on user selection
        var allEvents = @json($events);
        var filteredEvents = filterEvents(allEvents, viewScheduled, hideAvailable);

        var calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'timeGridWeek',
            selectable: true, //
            eventMinHeight: 20,
            eventShortHeight: 45,
            nowIndicator: true,
            slotMinTime: '00:00:00',
            slotMaxTime: '23:59:00',
            events: [
                ...filteredEvents.map(event => ({
                    ...event,
                    className: getEventClassName(event)
                })),
                ...(viewScheduled ? [] : (blockSlots || [])).map(slot => ({
                    title: "Blocked",
                    start: slot.start_time,
                    end: slot.end_time,
                    color: "#ff3d41",
                    extendedProps: {
                        isBlocked: true,
                        description: slot.description,
                        id: slot.id
                    }
                }))
            ],       

            select: function(info) {
                // info.startStr and info.endStr give ISO time range

                let isBlocked = (blockSlots || []).some(slot => {
                    let blockStart = new Date(slot.start_time);
                    let blockEnd = new Date(slot.end_time);
                    return (
                        (info.start >= blockStart && info.start < blockEnd) ||
                        (info.end > blockStart && info.end <= blockEnd)
                    );
                });

                if (isBlocked) {
                    Swal.fire("Blocked", "This time is blocked and cannot be booked.", "warning");
                    return;
                }

                function formatDate(d) {
                    return d.getFullYear() + "-" +
                        String(d.getMonth() + 1).padStart(2, "0") + "-" +
                        String(d.getDate()).padStart(2, "0") + " " +
                        String(d.getHours()).padStart(2, "0") + ":" +
                        String(d.getMinutes()).padStart(2, "0") + ":" +
                        String(d.getSeconds()).padStart(2, "0");
                }

                const startFormatted = formatDate(info.start);
                const endFormatted = formatDate(info.end);

                // Use startFormatted & endFormatted instead of info.startStr/info.endStr
                const startTime = new Date(info.start).toLocaleTimeString([], {
                    hour: '2-digit',
                    minute: '2-digit'
                });
                const endTime = new Date(info.end).toLocaleTimeString([], {
                    hour: '2-digit',
                    minute: '2-digit'
                });
                const timeRange = `${startTime} - ${endTime}`;

                // First show the options popup
                Swal.fire({
                    title: "Select Action",
                    html: `
                        <div style="text-align:center;">
                            <p><strong>Selected Time:</strong> ${startTime} - ${endTime}</p>
                            <div class="d-flex flex-column gap-2 mt-3">
                                <button id="scheduleLesson" class="btn btn-primary btn-block">Schedule Lesson</button>
                                <button id="setAvailability" class="btn btn-success btn-block">Set Availability</button>
                                <button id="addPersonalEvent" class="btn btn-info btn-block">Add Personal Event</button>
                            </div>
                        </div>
                    `,
                    showConfirmButton: false,
                    showCancelButton: true,
                    cancelButtonText: "Cancel",
                    didOpen: () => {
                        // Add event listeners to the buttons
                        document.getElementById('scheduleLesson').addEventListener('click', function() {
                            Swal.close();
                            scheduleLesson(startFormatted, endFormatted, startTime, endTime);
                        });

                        document.getElementById('setAvailability').addEventListener('click', function() {
                            Swal.close();
                            setAvailability(startFormatted, endFormatted, startTime, endTime);
                        });
                        

                        document.getElementById('addPersonalEvent').addEventListener('click', function() {
                            Swal.close();
                            addPersonalEvent(startFormatted, endFormatted, startTime, endTime, info);
                        });
                    }
                });
            },
            eventDidMount: function(info) {
                const isBlocked = info.event.extendedProps?.isBlocked;
                const titleContainer = info.el.querySelector(".fc-event-time");

                if (!titleContainer) return;
                titleContainer.style.display = "flex";

                titleContainer.style.justifyContent = "space-between";

                // Wrapper for checkbox + delete icon
                const actionContainer = document.createElement("div");
                actionContainer.style.display = "flex";
                actionContainer.style.justifyContent = "end";

                // actionContainer.style.position = 'absolute'
                // actionContainer.style.top = '0px'
                // actionContainer.style.right = '2px'
                actionContainer.style.alignItems = "center";
                actionContainer.style.gap = "6px"; // space between checkbox and delete icon
                actionContainer.style.marginLeft = "8px";

                // ✅ Checkbox only for non-blocked slots
                if (!isBlocked) {
                    const checkbox = document.createElement("input");
                    checkbox.type = "checkbox";
                    // checkbox.style.top = '3px'
                    // checkbox.style.right = '20px'
                    checkbox.className = "slot-select-checkbox";
                    checkbox.dataset.slotId = info.event.extendedProps.slot_id;

                    // prevent checkbox click from triggering eventClick
                    checkbox.addEventListener("click", function(e) {
                        e.stopPropagation();
                    });

                    actionContainer.appendChild(checkbox);
                }

                if (type == 'Instructor' && isBlocked) {

                    // ✅ Delete button (only for instructors)
                    const deleteBtn = document.createElement('span');
                    deleteBtn.className = '';
                    deleteBtn.innerHTML = `<i class="ti ti-trash text-white"></i>`; // ⬅️ unchanged
                    deleteBtn.title = 'Delete';
                    deleteBtn.style.cursor = 'pointer';
                    deleteBtn.style.marginLeft = "4px";
                    // deleteBtn.style.top = '0px'
                    // deleteBtn.style.right = '2px'

                    deleteBtn.addEventListener('click', function(e) {
                        e.stopPropagation();

                        const eventId = isBlocked ? info?.event?.extendedProps?.id : info
                            ?.event?.extendedProps?.slot_id;
                        const deleteUrl = isBlocked ? "{{ route('slot.block.delete') }}" :
                            "{{ route('slot.delete') }}";
                        const slotType = isBlocked ? "blocked slot" : "slot";

                        Swal.fire({
                            title: `Delete ${slotType}?`,
                            text: `Are you sure you want to delete this ${slotType}?`,
                            icon: "warning",
                            showCancelButton: true,
                            confirmButtonText: "Yes, delete it",
                            cancelButtonText: "Cancel",
                            reverseButtons: true
                        }).then((result) => {
                            if (result.isConfirmed && eventId > 0) {
                                info.event.remove();
                                $.ajax({
                                    url: deleteUrl,
                                    type: 'POST',
                                    data: {
                                        _token: $('meta[name="csrf-token"]')
                                            .attr('content'),
                                        id: eventId,
                                    },
                                    success: function(response) {
                                        Swal.fire('Deleted!', response
                                            .message, 'success');
                                    },
                                    error: function(error) {
                                        Swal.fire('Error',
                                            'There was a problem deleting the slot.',
                                            'error');
                                        console.log(error);
                                    }
                                });
                            }
                        });
                    });
                    actionContainer.appendChild(deleteBtn);
                }
                // ✅ Add the group after title
                titleContainer.appendChild(actionContainer);
            },
            eventClick: function(info) {
                if (info.event.extendedProps.isBlocked) {
                    Swal.fire({
                        title: "Blocked Slot",
                        html: `
                                <div style="text-align:left;">
                                    <p><strong>Time:</strong> ${new Date(info.event.start).toLocaleString()} - ${new Date(info.event.end).toLocaleTimeString()}</p>
                                    <p><strong>Description:</strong></p>
                                    <p>${info.event.extendedProps.description || "No description provided."}</p>
                                </div>
                            `,
                        icon: "info",
                        confirmButtonText: "Close"
                    });
                    return; // stop normal eventClick behavior
                }

                const slot_id = info?.event?.extendedProps?.slot_id;
                const isBooked = !!info?.event?.extendedProps?.is_student_assigned;
                const isCompleted = !!info.event?.extendedProps?.is_completed;
                const availableSeats = info.event.extendedProps.available_seats;
                const slot = info.event.extendedProps.slot;
                const paymentMethod = slot.lesson.payment_method;
                const student = info.event.extendedProps.student;
                const lesson = info.event.extendedProps.lesson;
                const instructor = info.event.extendedProps.instructor;
                const isPackageLesson = !!lesson.is_package_lesson;
                const formattedTime = new Date(slot.date_time.replace(/-/g, "/"))
                    .toLocaleTimeString([], {
                        weekday: 'long',
                        year: 'numeric',
                        month: 'long',
                        day: 'numeric',
                        hour: '2-digit',
                        minute: '2-digit',
                        hour12: true
                    });
                //         if (isBooked && type == 'Instructor' && isPackageLesson) {
                //             // Extract list of booked students
                //             let bookedStudentsHtml =
                //                 "<strong style='display: block; text-align: left; margin-bottom: 5px;'>📋 Booked Students:</strong>";
                //             bookedStudentsHtml += "<ol style='text-align: left; padding-left: 20px;'>";

                //             if (student.length > 0) {
                //                 student.forEach((student, index) => {
                //                     let studentName = student.pivot.isFriend ?
                //                         `${student.pivot.friend_name} (Friend: ${student.name})` :
                //                         student.name;

                //                     bookedStudentsHtml += `<li>${index + 1}. ${studentName}</li>`;
                //                 });
                //             } else {
                //                 bookedStudentsHtml += "<li>No students booked yet.</li>";
                //             }

                //             bookedStudentsHtml += "</ol>";

                //             Swal.fire({
                //                 title: "Confirm Slot Completion",
                //                 html: `
                //     <p style="text-align: left;">Are you sure you want to complete this lesson slot?</p>
                //     ${bookedStudentsHtml}
                // `,
                //                 icon: "warning",
                //                 showCancelButton: true,
                //                 confirmButtonText: "Confirm",
                //                 cancelButtonText: "Cancel",
                //             }).then((result) => {
                //                 if (result.isConfirmed) {
                //                     // Send AJAX request to complete slot
                //                     $.ajax({
                //                         url: "{{ route('slot.complete') }}",
                //                         type: "POST",
                //                         data: {
                //                             _token: $('meta[name="csrf-token"]').attr(
                //                                 'content'),
                //                             payment_method: "online",
                //                             slot_id: slot_id,
                //                             redirect: 1,
                //                         },
                //                         success: function(response) {
                //                             Swal.fire("Success!",
                //                                     "Lesson slot has been completed.",
                //                                     "success")
                //                                 .then(() => {
                //                                     showPageLoader();
                //                                     location
                //                                         .reload(); // Reload page after success
                //                                 });
                //                         },
                //                         error: function(xhr) {
                //                             Swal.fire("Error!",
                //                                 "Something went wrong. Please try again.",
                //                                 "error");
                //                         }
                //                     });
                //                 }
                //             });

                //             return;
                //         }


                if (!isCompleted && (type == 'Admin' || type == 'Instructor')) {
                    const swalWithBootstrapButtons = Swal.mixin({
                        customClass: {
                            confirmButton: "btn btn-success",
                            cancelButton: "btn btn-danger",
                        },
                        buttonsStyling: false,
                    });

                    // if (!!lesson.is_package_lesson) {
                    //     Swal.fire('Error',
                    //         'Sorry, Instructors can\'t book package lesson slots for students.',
                    //         'error');
                    //     return; // Stop further execution
                    // }


                    let completeSlotButtonHtml = '';
                    if (isBooked)
                        completeSlotButtonHtml = `
                                <button id="completeSlotBtn" type="button" class="swal2-confirm btn btn-success">Complete Slot</button>
                                                `;
                    let bookedStudentsHtml = student.length ?
                        `<ul>${student.map(student => 
                            `<li>${student?.pivot.friend_name ? student.pivot.friend_name : student.name} ${student.isGuest ? "(Guest)" : ""}</li>`
                        ).join('')}</ul>` :
                        "<p class='text-muted'>No students booked yet.</p>";

                    let unbookButtonHtml = "";
                    let noteHtml = "";
                    if (isBooked) {
                        unbookButtonHtml =
                            `<button id="unbookBtn" type="button" class="swal2-confirm btn btn-warning">Unbook Students</button>`;
                        noteHtml =
                            ``;
                    }

                    swalWithBootstrapButtons.fire({
                        title: "Book Slot",
                        html: `
                            <div style="text-align: left; font-size: 14px; margin-bottom: 10px;">
                                <span><strong>Lesson Date/Time:</strong> ${formattedTime}</span><br/>
                                <span><strong>Lesson:</strong> ${lesson.lesson_name}</span><br/>
                                <span><strong>Instructor:</strong> ${instructor.name}</span><br/>
                                <span><strong>Location:</strong> ${slot.location}</span><br/>
                                <span><strong>Available Spots:</strong> <strong>${availableSeats}</strong></span><br/>
                                <label><strong>Booked Students:</strong></label>
                                ${bookedStudentsHtml}
                            </div>

                                <form id="swal-form">
                                    <div class="form-group" id="student-form">
                                        <div class="flex justify-start">
                                            <label class="mb-1"><strong>Select Students</strong></label>
                                        </div>
                                        <select name="student_id[]" id="student_id" class="form-select w-full" multiple>
                                            @foreach ($students as $student)
                                                <option value="{{ $student->id }}">
                                                    {{ ucfirst($student->name) }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <textarea name="note" id="notes" class="form-control" placeholder="Enter note here" cols="10" rows="5"></textarea>
                                </form>
                                <div class="mb-4">
                                    ${noteHtml}
                                </div>
                                <div class="flex justify-center gap-4">
                                        ${unbookButtonHtml} ${completeSlotButtonHtml}
                                    </div>
                        `,

                        didOpen: () => {
                            const choices = new Choices(document.getElementById(
                                'student_id'), {
                                searchEnabled: true
                            });
                        },
                        showCancelButton: true,
                        confirmButtonText: "Book",
                        cancelButtonText: 'Close',
                        reverseButtons: true,
                        didRender: () => {
                            document.getElementById("unbookBtn")?.addEventListener(
                                "click",
                                function() {
                                    openManageSlotPopup(slot_id, student,
                                        formattedTime, lesson, instructor, slot,
                                        availableSeats);
                                });
                            document.getElementById("completeSlotBtn")
                                ?.addEventListener("click", function() {
                                    completeSlot(slot_id, paymentMethod,
                                        instructor);
                                });
                        },
                        preConfirm: () => {
                            const studentSelect = document.getElementById('student_id');
                            const student_ids = [...studentSelect.selectedOptions].map(
                                opt => opt.value);

                            const notes = document.querySelector("#notes")?.value;

                            if (student_ids.length > availableSeats)
                                Swal.showValidationMessage(
                                    `You can only select up to ${availableSeats} students.`
                                );

                            return {
                                student_ids,
                                notes,
                            };
                        }
                    }).then((result) => {
                        if (result.value) {
                            const formData = result.value;
                            $.ajax({
                                url: "{{ route('slot.admin') }}",
                                type: 'POST',
                                data: {
                                    _token: $('meta[name="csrf-token"]').attr(
                                        'content'),
                                    isGuest: false,
                                    student_Ids: formData.student_ids,
                                    notes: formData.notes,
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
                        }
                    });
                }
            },
            // headerToolbar: {
            //     right: 'customDayButton today prev,next', // Add custom button here
            //     center: 'title',
            //     left: 'timeGridWeek,listWeek', // Built-in views
            // },
            // customButtons: {
            //     customDayButton: {
            //         text: 'Day View', // Button label
            //         click: function() {
            //             calendar.changeView('timeGridDay'); // Change to day view
            //         },
            //     },
            // },
            headerToolbar: {
                right: 'customDayButton today prev,next deleteSelectedBtn',
                center: 'title',
                left: 'timeGridWeek,listWeek',
            },
            customButtons: {
                customDayButton: {
                    text: 'Day View', // Button label
                    click: function() {
                        calendar.changeView('timeGridDay'); // Change to day view
                    },
                },
                deleteSelectedBtn: {
                    text: 'Delete Selected',
                    click: function() {
                        deleteSelectedSlots();
                    }
                }
            },
        });
        calendar.render();


        // 🔹 Date picker: Jump to selected week's date
        const dateInput = document.getElementById("calendar-date");
        dateInput.addEventListener("change", function() {
            const selectedDate = this.value;
            if (selectedDate) {
                calendar.gotoDate(selectedDate); // Go to that date
                calendar.changeView('timeGridWeek'); // Ensure week view
            }
        });

        function getEventClassName(event) {
            if (event.is_completed) {
                return 'event-completed'; // Green for completed
            } else if (event.is_student_assigned) {
                return 'event-booked'; // Yellow for booked but not completed
            } else {
                return 'event-free'; // Blue for available
            }
        }

         // Function to filter events based on user selection
        function filterEvents(events, showScheduled, hideAvailable) {
            return events.filter(event => {
                // const isScheduled = event.is_student_assigned || event.is_completed;
                const isAvailable = !event.is_student_assigned && !event.is_completed;
                
                // If "View Scheduled Lessons" is checked, only show scheduled lessons
                if (showScheduled) {
                    if (!event.is_student_assigned || event.is_completed) {
                        return false;
                    }
                }
                
                // If "Hide Available Lessons" is checked, hide available lessons
                if (hideAvailable && isAvailable) {
                    return false;
                }
                
                return true;
            });
        }

          // NEW: Function to update calendar with current filters WITHOUT page reload
        // function updateCalendarWithCurrentFilters() {
        //     // Get current filter states
        //     const viewScheduled = document.getElementById('viewScheduled')?.checked || false;
        //     const hideAvailable = document.getElementById('hideAvailable')?.checked || false;
            
        //     // Get selected lesson IDs
        //     const selectedLessonIds = Array.from(document.querySelectorAll('.lesson-checkboxes input[type="checkbox"]:checked'))
        //         .map(checkbox => checkbox.value);
            
        //     console.log('Applying filters:', {
        //         viewScheduled,
        //         hideAvailable,
        //         selectedLessonIds
        //     });
            
        //     // Filter events based on current selections
        //       let filteredEvents = [...allEvents];
            
        //     // Apply lesson filter if any lessons are selected
        //     if (selectedLessonIds.length > 0) {
        //         filteredEvents = filteredEvents.filter(event => 
        //             selectedLessonIds.includes(event.lesson.id.toString())
        //         );
        //     }
            
        //     // Apply view filters
        //     filteredEvents = filterEvents(filteredEvents, viewScheduled, hideAvailable);
            
        //     // Remove all existing events except blocked slots
        //     calendar.getEvents().forEach(event => {
        //         if (!event.extendedProps.isBlocked) {
        //             event.remove();
        //         }
        //     });
            
        //     // Add filtered events
        //     filteredEvents.forEach(event => {
        //         calendar.addEvent({
        //             ...event,
        //             className: getEventClassName(event)
        //         });
        //     });
            
        //     // Add blocked slots (if not viewing scheduled)
        //     if (!viewScheduled) {
        //         (blockSlots || []).forEach(slot => {
        //             calendar.addEvent({
        //                 title: "Blocked",
        //                 start: slot.start_time,
        //                 end: slot.end_time,
        //                 color: "#ff3d41",
        //                 extendedProps: {
        //                     isBlocked: true,
        //                     description: slot.description,
        //                     id: slot.id
        //                 }
        //             });
        //         });
        //     }
            
        //     calendar.render();
        // }

        // DEBUGGING VERSION - Let's find where the issue is
function updateCalendarWithCurrentFilters() {
    // Get current filter states
    const viewScheduled = document.getElementById('viewScheduled')?.checked || false;
    const hideAvailable = document.getElementById('hideAvailable')?.checked || false;
    
    // Get selected lesson IDs
    const selectedLessonIds = Array.from(document.querySelectorAll('.lesson-checkboxes input[type="checkbox"]:checked'))
        .map(checkbox => checkbox.value);
    
    console.log('🔍 DEBUG FILTERS:', {
        viewScheduled,
        hideAvailable,
        selectedLessonIds,
        totalBlockSlots: blockSlots?.length || 0,
        blockSlots: blockSlots // Let's see what's in blockSlots
    });
    
    // Start with all events
    let filteredEvents = [...allEvents];
    
    // Apply lesson filter ONLY if any lessons are selected
    if (selectedLessonIds.length > 0) {
        filteredEvents = filteredEvents.filter(event => {
            return event.lesson && selectedLessonIds.includes(event.lesson.id.toString());
        });
    }
    
    // Apply view filters
    filteredEvents = filterEvents(filteredEvents, viewScheduled, hideAvailable);
    
    console.log('📊 After filtering:', {
        filteredEventsCount: filteredEvents.length,
        shouldShowBlocked: !viewScheduled
    });
    
    // Remove all existing events
    calendar.getEvents().forEach(event => {
        event.remove();
    });
    
    // Add filtered events
    filteredEvents.forEach(event => {
        calendar.addEvent({
            ...event,
            className: getEventClassName(event)
        });
    });
    
    // ✅ FIXED: Add blocked slots ONLY if NOT viewing scheduled lessons
    if (!viewScheduled && blockSlots && blockSlots.length > 0) {
        console.log('🟥 Adding blocked slots:', blockSlots.length);
        
        blockSlots.forEach(slot => {
            console.log('🟥 Block Slot:', {
                start: slot.start_time,
                end: slot.end_time,
                description: slot.description
            });
            
            calendar.addEvent({
                id: `blocked-${slot.id}`, // Unique ID for blocked slots
                title: "Blocked",
                start: slot.start_time,
                end: slot.end_time,
                color: "#ff3d41",
                extendedProps: {
                    isBlocked: true,
                    description: slot.description,
                    id: slot.id
                }
            });
        });
    } else {
        console.log('❌ NOT showing blocked slots because:', {
            viewScheduled,
            hasBlockSlots: blockSlots && blockSlots.length > 0
        });
    }
    
    calendar.render();
}
        // Make calendar globally accessible for other functions
        window.calendar = calendar;


         // Function to apply filters and refresh calendar
        function applyFilters() {
            var filteredEvents = filterEvents(allEvents, viewScheduled, hideAvailable);
            
            // Remove all existing events except blocked slots
            calendar.getEvents().forEach(event => {
                if (!event.extendedProps.isBlocked) {
                    event.remove();
                }
            });
            
            // Add filtered events
            filteredEvents.forEach(event => {
                calendar.addEvent({
                    ...event,
                     className: getEventClassName(event)
                });
            });
            
            calendar.render();
        }


        function addPersonalEvent(startFormatted, endFormatted, startTime, endTime, info) {
            Swal.fire({
                title: "Add Personal Event",
                html: `
                        <div style="text-align:left;">
                            <label><strong>Start Time:</strong></label>
                            <input type="text" id="selectedTime" class="form-control mb-3" value="${startTime}" readonly>
                            <label><strong>End Time:</strong></label>
                            <input type="time" id="selectedendTime" class="form-control mb-3" value="">
                            <label><strong>Event Description:</strong></label>
                            <textarea id="reason" class="form-control" placeholder="Enter event description here..." rows="4"></textarea>
                        </div>
                    `,
                showCancelButton: true,
                confirmButtonText: "Save",
                cancelButtonText: "Cancel",
                preConfirm: () => {
                    const reason = document.getElementById('reason').value;
                    const selectedEndTime = document.getElementById('selectedendTime').value;
                    if (!reason) {
                        Swal.showValidationMessage("Please enter a reason before saving.");
                        return false;
                    }
                    let end = endFormatted;
                    if (selectedEndTime) {
                        const endDate = new Date(info.end);
                        const [h, m] = selectedEndTime.split(':');
                        endDate.setHours(h, m, 0, 0);
                        end = formatDate(endDate);
                    }
                    return {
                        reason,
                        start: startFormatted,
                        end: end
                    };
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    // JUST CLOSE THE DIALOG AND SAVE - NO LOADING INDICATOR
                    Swal.close();

                    $.ajax({
                        url: "{{ route('slot.block.reason') }}",
                        type: "POST",
                        data: {
                            _token: $('meta[name="csrf-token"]').attr('content'),
                            reason: result.value.reason,
                            start_time: result.value.start,
                            end_time: result.value.end
                        },
                        success: function(response) {
                            // Add to calendar silently
                            Swal.fire("Success", "Reason saved successfully", 'success');
                            blockSlots.push({
                                id: response.id,
                                start_time: result.value.start,
                                end_time: result.value.end,
                                description: result.value.reason
                            });
                            calendar.addEvent({
                                id: response.id,
                                title: "Blocked",
                                start: result.value.start,
                                end: result.value.end,
                                color: '#ff3d41',
                                extendedProps: {
                                    isBlocked: true,
                                    description: result.value.reason
                                }
                            });

                            // Optional: Show quick success message
                            Swal.fire({
                                toast: true,
                                position: 'top-end',
                                icon: 'success',
                                title: 'Event saved!',
                                showConfirmButton: false,
                                timer: 1000
                            });
                        },
                        error: function(xhr, status, error) {
                            Swal.fire({
                                icon: "error",
                                title: "Error",
                                text: "Save failed. Please try again.",
                                confirmButtonText: "OK"
                            });
                        }
                    });
                }
            });
        }
    });

    // Function to handle Schedule Lesson
    // Function to handle Schedule Lesson
    function scheduleLesson(startFormatted, endFormatted, startTime, endTime) {
        console.log("Opening Schedule Lesson Modal");

        // Show loading first
        // Swal.fire({
        //     title: 'Loading...',
        //     text: 'Opening lesson scheduling',
        //     allowOutsideClick: false,
        //     showConfirmButton: false,
        //     didOpen: () => {
        //         Swal.showLoading();
        //     }
        // });

        // Load the modal content via AJAX
        $.ajax({
            url: "{{ route('lesson.schedule.modal') }}", // You'll need to create this route
            type: "GET",
            data: {
                start_time: startFormatted,
                end_time: endFormatted,
                start_time_display: startTime,
                end_time_display: endTime
            },
            success: function(response) {
                // Close loading and open modal
                Swal.close();

                Swal.fire({
                    title: 'Schedule Lesson',
                    html: response,
                    width: '700px',
                    showCancelButton: true,
                    confirmButtonText: 'Book Lesson',
                    cancelButtonText: 'Cancel',
                    showLoaderOnConfirm: true,
                    allowOutsideClick: false,
                    didOpen: () => {
                        // Initialize any dynamic functionality
                        initializeScheduleLessonModal();
                    },
                    preConfirm: () => {
                        return new Promise((resolve, reject) => {
                            const form = document.getElementById('scheduleLessonForm');
                            if (!form) {
                                reject('Form not found');
                                return;
                            }

                            const formData = new FormData(form);

                            // Add CSRF token
                            formData.append('_token', '{{ csrf_token() }}');

                            // Submit via AJAX
                            $.ajax({
                                url: "{{ route('lesson.schedule') }}", // You'll need to create this route
                                type: "POST",
                                data: formData,
                                processData: false,
                                contentType: false,
                                success: function(response) {
                                    resolve(response);
                                },
                                error: function(xhr) {
                                    let errorMessage = 'Something went wrong while scheduling the lesson!';
                                    if (xhr.responseJSON && xhr.responseJSON.errors) {
                                        errorMessage = Object.values(xhr.responseJSON.errors).join('<br>');
                                    } else if (xhr.responseJSON && xhr.responseJSON.message) {
                                        errorMessage = xhr.responseJSON.message;
                                    } 
                                    reject(errorMessage);
                                }
                            });
                        });
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Success!',
                            text: 'Lesson scheduled successfully!',
                            timer: 2000,
                            showConfirmButton: false
                        }).then(() => {
                            // Refresh the calendar
                            showPageLoader();
                            window.location.reload();
                        });
                    }
                });
            },
            error: function(xhr, status, error) {
                Swal.close();
                console.error('Error loading modal:', error);
                Swal.fire('Error', 'Could not load schedule lesson form', 'error');
            }
        });
    }

    // Function to initialize the schedule lesson modal functionality
    function initializeScheduleLessonModal() {
        // Initialize student multi-select
        const studentSelect = document.getElementById('student_id');
        if (studentSelect) {
            new Choices(studentSelect, {
                removeItemButton: true,
                searchEnabled: true,
                placeholder: true,
                placeholderValue: 'Select students',
                shouldSort: false
            });
        }

        // Initialize lesson dropdown
        const lessonSelect = document.getElementById('lesson_id');
        if (lessonSelect) {
            new Choices(lessonSelect, {
                searchEnabled: true,
                shouldSort: false
            });
        }

        // Handle lesson type change for multi-student functionality
        const lessonTypeSelect = document.getElementById('lesson_type');
        if (lessonTypeSelect) {
            lessonTypeSelect.addEventListener('change', function() {
                toggleMultiStudentField(this.value);
            });
        }

        // Add student functionality
        const addStudentBtn = document.getElementById('add-student');
        if (addStudentBtn) {
            addStudentBtn.addEventListener('click', addStudentField);
        }

        // Remove student functionality
        document.addEventListener('click', function(e) {
            if (e.target.classList.contains('remove-student')) {
                e.target.closest('.student-field').remove();
            }
        });
    }

    // Function to toggle multi-student field based on lesson type
    function toggleMultiStudentField(lessonType) {
        const multiStudentContainer = document.getElementById('multi-student-container');
        const studentFields = document.getElementById('student-fields');

        if (!multiStudentContainer || !studentFields) return;

        // Check if lesson type allows multiple students (you'll need to define this logic)
        const allowsMultipleStudents = checkIfLessonAllowsMultipleStudents(lessonType);

        if (allowsMultipleStudents) {
            multiStudentContainer.style.display = 'block';
            // Ensure at least one student field exists
            if (studentFields.children.length === 0) {
                addStudentField();
            }
        } else {
            multiStudentContainer.style.display = 'none';
            // Clear all student fields
            studentFields.innerHTML = '';
        }
    }

    // Function to check if a lesson type allows multiple students
    function checkIfLessonAllowsMultipleStudents(lessonId) {
        // You'll need to implement this based on your lesson data structure
        // This could be done by storing lesson data in a JavaScript variable
        // or making an AJAX call to check the lesson type
        const multiStudentLessons = []; // Array of lesson IDs that allow multiple students

        return multiStudentLessons.includes(parseInt(lessonId));
    }

    // Function to add a new student field
    function addStudentField() {
        const studentFields = document.getElementById('student-fields');
        if (!studentFields) return;

        const studentCount = studentFields.children.length;
        const newField = document.createElement('div');
        newField.className = 'student-field row g-2 mb-2';
        newField.innerHTML = `
        <div class="col-md-10">
            <label class="form-label">Student</label>
            <select name="student_ids[]" class="form-select student-select" required>
                <option value="">Select Student</option>
                @foreach ($students as $student)
                    <option value="{{ $student->id }}">{{ $student->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2 d-flex align-items-end">
            <button type="button" class="btn btn-danger remove-student" ${studentCount === 0 ? 'disabled' : ''}>
                Remove
            </button>
        </div>
    `;

        studentFields.appendChild(newField);

        // Initialize Choices for the new select
        const newSelect = newField.querySelector('.student-select');
        if (newSelect) {
            new Choices(newSelect, {
                removeItemButton: true,
                searchEnabled: true,
                placeholder: true,
                placeholderValue: 'Select student',
                shouldSort: false
            });
        }

        // Enable remove buttons if there's more than one field
        if (studentFields.children.length > 1) {
            const removeButtons = studentFields.querySelectorAll('.remove-student');
            removeButtons.forEach(btn => btn.disabled = false);
        }
    }


    // Keep this in a global <script> file or inside <script> tags in the parent page
    function initAvailabilityModal() {
        // Datepicker
        $('.date').datepicker({
            startDate: new Date(),
            multidate: true,
            format: 'yyyy-mm-dd'
        });

        // Time range handling
        const container = $('#time-ranges');
        const addBtn = $('#add-range-btn');

        addBtn.on('click', function() {
            const newRange = $(`
            <div class="time-range row g-2 mb-2">
                <div class="col-md-5">
                    <label class="form-label">Start Time</label>
                    <input type="time" name="start_time[]" class="form-control" required>
                </div>
                <div class="col-md-5">
                    <label class="form-label">End Time</label>
                    <input type="time" name="end_time[]" class="form-control" required>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="button" class="btn btn-danger btn-sm remove-range">Remove</button>
                </div>
            </div>
        `);
            container.append(newRange);
        });

        container.on('click', '.remove-range', function() {
            if (container.find('.time-range').length > 1) {
                $(this).closest('.time-range').remove();
            } else {
                alert('You need at least one time slot');
            }
        });
    }

    ///////

    // Function to handle Set Availability in modal
    function setAvailability(startFormatted, endFormatted, startTime, endTime) {
        console.log("Opening Set Availability Modal");

        // Show loading first
        // Swal.fire({
        //     title: 'Loading...',
        //     text: 'Opening availability settings',
        //     allowOutsideClick: false,
        //     showConfirmButton: false,
        //     didOpen: () => {
        //         Swal.showLoading();
        //     }
        // });

        // Load the modal content via AJAX
        $.ajax({
            url: "{{ route('slot.availability.modal') }}",
            type: "GET",
            success: function(response) {
                // Close loading and open modal
                Swal.close();
                // resolve();

                Swal.fire({
                    title: 'Set Availability',
                    html: response,
                    width: '700px',
                    showCancelButton: true,
                    confirmButtonText: 'Save Availability',
                    cancelButtonText: 'Cancel',
                    showLoaderOnConfirm: true,
                    didOpen: () => {
                        // The JavaScript in the modal content will automatically run
                        console.log('Modal opened successfully');
                        initAvailabilityModal();
                    },
                    preConfirm: () => {
                        // Handle form submission
                        return new Promise((resolve, reject) => {
                            const form = document.getElementById('availabilityModalForm');
                            const formData = new FormData(form);

                            // Add CSRF token
                            formData.append('_token', '{{ csrf_token() }}');

                            // Submit via AJAX
                            $.ajax({
                                url: "{{ route('slot.availability') }}",
                                type: "POST",
                                data: formData,
                                processData: false,
                                contentType: false,
                                success: function(response) {
                                    resolve();
                                    // Show success message
                                    Swal.fire({
                                        icon: 'success',
                                        title: 'Success!',
                                        text: 'Availability set successfully!',
                                        timer: 2000,
                                        showConfirmButton: false
                                    });

                                    location.reload();

                                },
                                error: function(xhr) {
                                    let errorMessage = 'Something went wrong while saving!';
                                    if (xhr.responseJSON && xhr.responseJSON.errors) {
                                        errorMessage = Object.values(xhr.responseJSON.errors).join('<br>');
                                    }
                                    reject(errorMessage);
                                }
                            });
                        });
                    }
                });
            },
            error: function(xhr, status, error) {
                Swal.close();
                console.error('Error loading modal:', error);
                Swal.fire('Error', 'Could not load availability form', 'error');
            }
        });
    }

    // Simple loading functions to avoid SweetAlert loading issues
    function showSimpleLoading() {
        // Create a simple overlay instead of using SweetAlert
        const loadingOverlay = document.createElement('div');
        loadingOverlay.id = 'simple-loading-overlay';
        loadingOverlay.style.cssText = `
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 9999;
        `;

        const loadingContent = document.createElement('div');
        loadingContent.style.cssText = `
            background: white;
            padding: 20px;
            border-radius: 8px;
            text-align: center;
        `;
        loadingContent.innerHTML = `
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p class="mt-2">Saving event...</p>
        `;

        loadingOverlay.appendChild(loadingContent);
        document.body.appendChild(loadingOverlay);
    }

    function hideSimpleLoading() {
        const loadingOverlay = document.getElementById('simple-loading-overlay');
        if (loadingOverlay) {
            loadingOverlay.remove();
        }
    }

    // Helper function for date formatting (if not already in global scope)
    function formatDate(d) {
        return d.getFullYear() + "-" +
            String(d.getMonth() + 1).padStart(2, "0") + "-" +
            String(d.getDate()).padStart(2, "0") + " " +
            String(d.getHours()).padStart(2, "0") + ":" +
            String(d.getMinutes()).padStart(2, "0") + ":" +
            String(d.getSeconds()).padStart(2, "0");
    }
    //////////NEW CODE

    function openManageSlotPopup(slot_id, student, formattedTime, lesson, instructor, slot, availableSeats) {
        const notes = document.querySelector("#notes")?.value;
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
                        <span><strong>Lesson Date/Time:</strong> ${formattedTime}</span><br/>
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

                console.log("here in notes", notes);
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
                        notes: notes,
                    },
                    success: function(response) {
                        Swal.fire('Success', 'Students unbooked successfully!', 'success');
                        showPageLoader();
                        window.location.reload();
                    },
                    error: function(error) {
                        Swal.fire('Error', 'There was a problem processing the request.', 'error');
                        console.log(error);
                    }
                });
            }
        });
    }

    function deleteSelectedSlots() {
        const selected = document.querySelectorAll(".slot-select-checkbox:checked");
        if (selected.length === 0) {
            Swal.fire("No Slots Selected", "Please select at least one slot.", "warning");
            return;
        }

        const slotIds = Array.from(selected).map(cb => cb.dataset.slotId);

        Swal.fire({
            title: "Confirm Bulk Delete",
            text: `Are you sure you want to delete ${slotIds.length} selected slots?`,
            icon: "warning",
            showCancelButton: true,
            confirmButtonText: "Yes, delete",
            cancelButtonText: "Cancel"
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: "{{ route('slot.bulkDelete') }}",
                    type: "POST",
                    data: {
                        _token: $('meta[name="csrf-token"]').attr('content'),
                        ids: slotIds
                    },
                    success: function(response) {
                        if (response.error) {
                            Swal.fire("Error", response.error, "error");
                        } else {
                            Swal.fire("Deleted!", response.message, "success");
                            showPageLoader();
                            window.location.reload();
                        }
                    },
                    error: function(xhr) {
                        let errorMessage = "Something went wrong.";

                        // Try to get error message from JSON response
                        if (xhr.responseJSON && xhr.responseJSON.error) {
                            errorMessage = xhr.responseJSON.error;
                        }
                        // Or fallback to raw text from server
                        else if (xhr.responseText) {
                            try {
                                let parsed = JSON.parse(xhr.responseText);
                                errorMessage = parsed.error || xhr.responseText;
                            } catch (e) {
                                errorMessage = xhr.responseText;
                            }
                        }

                        Swal.fire("Error", errorMessage, "error");
                        console.error("Bulk delete failed:", xhr);
                    }
                });


            }
        });
    }


    function completeSlot(slot_id, paymentMethod, instructor) {
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
    }
</script>

<script src="{{ asset('vendor/intl-tel-input/jquery.mask.js') }}"></script>
<script src="{{ asset('vendor/intl-tel-input/intlTelInput-jquery.min.js') }}"></script>
<script src="{{ asset('vendor/intl-tel-input/utils.min.js') }}"></script>
@endpush