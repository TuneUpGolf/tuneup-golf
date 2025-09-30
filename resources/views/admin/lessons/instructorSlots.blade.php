@extends('layouts.main') @section('title', __('Dashboard')) @section('breadcrumb') <li class="breadcrumb-item"><a
        href="{{ route('home') }}">{{ __('Dashboard') }}</a></li>
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
                        <div class="justify-between items-center card-header  w-100">
                            {{-- <h5>{{ __('All Slots') }}</h5> --}}
                            <form>
                                <select name="lesson_id" id="lesson_id" class="form-select w-full"
                                    onchange="this.form.submit()">
                                    <option value="" disabled selected>Select Lesson</option>
                                    <option value="-1">All</option>
                                    @foreach ($lessons as $lesson)
                                        <option value="{{ $lesson->id }}"
                                            {{ request()->input('lesson_id') == $lesson->id ? 'selected' : '' }}>
                                            {{ ucfirst($lesson->lesson_name) }}</option>
                                    @endforeach
                                </select>
                            </form>
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
        var students = @json($students);
        var payment_method = @json($payment_method);
        var isMobile = window.innerWidth <= 768;
        var initialCalendarView = isMobile ? 'listWeek' : 'timeGridWeek';
        var calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'timeGridWeek',
            eventMinHeight: 20,
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
            headerToolbar: {
                right: 'customDayButton today prev,next', // Add custom button here
                center: 'title',
                left: 'timeGridWeek,listWeek', // Built-in views
            },
            customButtons: {
                customDayButton: {
                    text: 'Day View', // Button label
                    click: function() {
                        calendar.changeView('timeGridDay'); // Change to day view
                    },
                },
            },
        });

        calendar.render();
    });

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
</script>
@endpush
