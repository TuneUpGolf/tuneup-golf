<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>

    <!-- ✅ Required dependencies -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/css/bootstrap-datepicker.min.css" rel="stylesheet" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/js/bootstrap-datepicker.min.js"></script>

    <style>
        .modal-content {
            background-color: #fff;
            border-radius: 12px;
        }

        .modal-header h5 {
            font-weight: 600;
        }

        .form-label {
            font-size: 0.95rem;
        }

        .form-check-label span {
            font-size: 0.85rem;
            color: #6c757d;
        }

        .btn-primary {
            background-color: #0d6efd;
            border: none;
        }

        .btn-secondary {
            background-color: #6c757d;
            border: none;
        }

        .btn:focus {
            box-shadow: none !important;
        }
    </style>
</head>

<body>
    <div class="modal-content border-0 shadow-sm rounded" style="max-width: 600px; margin: auto;">
        <div class="modal-header border-bottom-0 pt-4 pb-2 px-4">
            <h5 class="modal-title fw-semibold">{{ __('Set Availability') }}</h5>
            <!-- <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button> -->
        </div>

        <div class="modal-body px-4">
            {!! Form::open([
            'route' => ['slot.availability', ['redirect' => 1]],
            'method' => 'POST',
            'data-validate',
            'files' => true,
            'enctype' => 'multipart/form-data',
            'id' => 'availabilityModalForm'
            ]) !!}

            {{-- ✅ Select Dates --}}
            <!-- <div class="mb-3">
                {{ Form::label('start_date', 'Select Dates', ['class' => 'form-label fw-semibold']) }}
                {{ Form::text('start_date', null, [
                    'class' => 'form-control date',
                    'id' => 'start_date',
                    'required',
                    'autocomplete' => 'off',
                    'placeholder' => 'YYYY-MM-DD',
                ]) }}
            </div> -->

            <div class="mb-3">
                <label for="lesson_date" class="form-label fw-semibold">Select Date</label>
                <input
                    type="date"
                    id="start_date"
                    name="start_date"
                    class="form-control"
                    value="{{ request('start_date') }}"
                    required>
            </div>

            {{-- Time Range --}}
            <div id="time-ranges">
                <div class="row g-2 mb-2 time-range">
                    <div class="col-md-6">
                        {{ Form::label('start_time[]', 'Start Time', ['class' => 'form-label fw-semibold']) }}
                        {{ Form::input('time', 'start_time[]', null, ['class' => 'form-control', 'required']) }}
                    </div>
                    <div class="col-md-6">
                        {{ Form::label('end_time[]', 'End Time', ['class' => 'form-label fw-semibold']) }}
                        {{ Form::input('time', 'end_time[]', null, ['class' => 'form-control', 'required']) }}
                    </div>
                </div>
            </div>

            <div class="mb-3">
                <!-- <button type="button" id="add-range-btn"  class="btn btn-primary">+ Add</button> -->
            </div>

            {{-- Location --}}
            <div class="mb-3">
                {{ Form::label('location', 'Location', ['class' => 'form-label fw-semibold']) }}
                {{ Form::text('location', null, [
                    'class' => 'form-control',
                    'placeholder' => 'Enter Location',
                    'required',
                ]) }}
            </div>

            {{-- Applies to --}}
            <div class="form-group">
                @if (!empty($lesson))
                {{ Form::label('package_lesson', __('This Availability Applies To'), ['class' => 'form-label']) }}
                <br>
                @foreach ($lesson as $le)
                <input type="checkbox" name="lesson_id[]" class="form-check-input" value="{{ $le['id'] }}">
                {{ $le['lesson_name'] }}
                <span style="color:gray">
                    @if (isset($le['lesson_duration']))
                    {{ __('Lesson Duration : ' . $le['lesson_duration'] . 'hour(s)') }}
                    @endif
                </span>
                <br>
                @endforeach
                @else
                {{ Form::label('package_lesson', __('No Lesson available'), ['class' => 'form-label']) }}
                @endif
            </div>



            {!! Form::close() !!}
        </div>
    </div>
<script>
$(document).ready(function() {

    // ✅ Initialize Datepicker
    $('.date').datepicker({
        startDate: new Date(),
        multidate: true,
        format: 'yyyy-mm-dd'
    });

    // ✅ Time range management
    const container = $('#time-ranges');
    const addBtn = $('#add-range-btn');

    // Add new range
    addBtn.on('click', function() {
        console.log('Add button clicked');

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

    // Remove a time range
    container.on('click', '.remove-range', function() {
        if (container.find('.time-range').length > 1) {
            $(this).closest('.time-range').remove();
        } else {
            alert('You need at least one time slot');
        }
    });

});
</script>




</body>

</html>