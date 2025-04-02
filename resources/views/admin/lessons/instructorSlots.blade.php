@extends('layouts.main') @section('title', __('All Slots')) @section('breadcrumb') <li class="breadcrumb-item"><a
        href="{{ route('home') }}">{{ __('Dashboard') }}</a></li>
<li class="breadcrumb-item"><a href="{{ route('lesson.index') }}">{{ __('Lesson') }}</a></li>
<li class="breadcrumb-item">{{ __('All Slots') }}</li>
@endsection
@section('content')
<div class="main-content">
    <section class="section">
        <div class="section-body">
            <div class="m-auto col-lg-6 col-md-8 col-xxl-4">
                <div class="card">
                    <div>
                        <div class="flex justify-between items-center card-header  w-100">
                            <h5>{{ __('All Slots') }}</h5>
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
        var calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'timeGridWeek',
            eventMinHeight: 20,
            eventShortHeight: 45,
            nowIndicator: true,
            slotMinTime: '8:00:00',
            slotMaxTime: '20:00:00',
            events: @json($events),
            eventClick: function(info) {
                const slot_id = info?.event?.extendedProps?.slot_id;
                const isBooked = !!info?.event?.extendedProps?.is_student_assigned;
                const isCompleted = !!info.event?.extendedProps?.is_completed;
                const paymentMethod = info.event?.extendedProps?.payment_method;
                const slot = info.event.extendedProps.slot;
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

                if ((type == 'Instructor') && isBooked && !isCompleted) {
                    const swalWithBootstrapButtons = Swal.mixin({
                        customClass: {
                            confirmButton: "btn btn-success",
                            cancelButton: "btn btn-danger",
                        },
                        buttonsStyling: false,
                    });
                    swalWithBootstrapButtons
                        .fire({
                            title: "Manage Slot",
                            html: `
                    <div style="text-align: left; font-size: 14px;">
                        <span class="mb-1"><strong>Do you wish to complete or unreserve the following slot?</strong><span></br></br>
                       <span><strong>Slot Start Time:</strong> ${formattedTime}</span></br>
                        <span><strong>Lesson:</strong> ${lesson.lesson_name}</span></br>
                        <span><strong>Location:</strong> ${slot.location}</span></br>
                        <span><strong>Student:</strong> ${!!student.isGuest ? `${student.name} (Guest)`: student.name}</span></br>
                        <span><strong>Instructor:</strong> ${instructor.name}</span></br>
                    </div>
                `,
                            showCancelButton: true,
                            confirmButtonText: "Complete",
                            cancelButtonText: "Unreserve",
                            reverseButtons: true,
                            showCloseButton: true
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
                                if (paymentMethod == 'both')
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
                                $.ajax({
                                    url: "{{ route('slot.update') }}",
                                    type: 'POST',
                                    data: {
                                        _token: $('meta[name="csrf-token"]').attr(
                                            'content'),
                                        unbook: 1,
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
                            }
                        });
                }
                if (!isBooked && !isCompleted && (type == 'Admin' || type == 'Instructor')) {
                    const swalWithBootstrapButtons = Swal.mixin({
                        customClass: {
                            confirmButton: "btn btn-success",
                            cancelButton: "btn btn-danger",
                        },
                        buttonsStyling: false,
                    });
                    swalWithBootstrapButtons
                        .fire({
                            title: "Book Slot",
                            text: "Please select from the following payment options before completing slot as complete",
                            html: `
                        <form id="swal-form" class="">
                            <div class="flex justify-start gap-2 items-center mb-2">
                            <input type="checkbox" id="guestBooking" onchange="toggleGuestBooking()" />
                            <label for="guestBooking">Guest</label>
                            </div>
                            <div class="form-group" id="student-form">
                            <label for="guestBooking" class="mb-1">Select Student</label>
                            <select name="student_id" id="student_id" class="form-select w-full">
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
                        `,
                            preConfirm: () => {
                                const isGuest = document.getElementById('guestBooking')
                                    .checked;
                                const student_id = document.getElementById('student_id')
                                    .value;
                                const guestName = document.getElementById('guestName')
                                    ?.value;
                                const guestPhone = document.getElementById('guestPhone')
                                    ?.value;
                                const guestEmail = document.getElementById('guestEmail')
                                    ?.value;
                                const phoneRegex = /^[+]?[0-9]{10,15}$/;

                                if (!student_id && !isGuest || (isGuest && (!guestName || !
                                        guestPhone || !guestEmail)))
                                    Swal.showValidationMessage('All fields are required');

                                if (isGuest && guestPhone && !phoneRegex.test(guestPhone)) {
                                    Swal.showValidationMessage(
                                        'Enter a valid phone number (10-15 digits, optional + prefix)'
                                    );
                                    return false;
                                }

                                return {
                                    isGuest,
                                    student_id,
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
                            console.log(formData);
                            $.ajax({
                                url: "{{ route('slot.admin') }}",
                                type: 'POST',
                                data: {
                                    _token: $('meta[name="csrf-token"]').attr(
                                        'content'),
                                    isGuest: formData.isGuest ?? false,
                                    student_Id: formData.student_id,
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
                if (isBooked && !isCompleted && type == 'Admin') {
                    const swalWithBootstrapButtons = Swal.mixin({
                        customClass: {
                            confirmButton: "btn btn-success",
                            cancelButton: "btn btn-danger",
                        },
                        buttonsStyling: false,
                    });
                    swalWithBootstrapButtons
                        .fire({
                            title: "Unreserve Slot",
                            text: "Are you sure you want to unreserve this slot?",
                            showCancelButton: true,
                            confirmButtonText: "Confirm",
                            reverseButtons: true,
                        })
                        .then((result) => {
                            if (!!result.isConfirmed)
                                $.ajax({
                                    url: "{{ route('slot.update') }}",
                                    type: 'POST',
                                    data: {
                                        _token: $('meta[name="csrf-token"]').attr(
                                            'content'),
                                        unbook: 1,
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
        window.toggleGuestBooking = function() {
            const isGuest = document.getElementById('guestBooking').checked;
            document.getElementById('student-form').style.display = isGuest ? 'none' : 'block';
            document.getElementById('guestFields').style.display = isGuest ? 'block' : 'none';
        };
    });
</script>
<script src="{{ asset('vendor/intl-tel-input/jquery.mask.js') }}"></script>
<script src="{{ asset('vendor/intl-tel-input/intlTelInput-jquery.min.js') }}"></script>
<script src="{{ asset('vendor/intl-tel-input/utils.min.js') }}"></script>
</script>
@endpush
