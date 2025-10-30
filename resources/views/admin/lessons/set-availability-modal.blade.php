<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Set Availability</title>

    <!-- ✅ Dependencies -->
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
        
        .start-on-hour-help {
            font-size: 0.8rem;
            color: #6c757d;
            margin-top: -5px;
            margin-bottom: 15px;
        }
    </style>
</head>
<body>
    <div class="modal-content border-0 shadow-sm rounded" style="max-width: 600px; margin: auto;">
        <div class="modal-header border-bottom-0 pt-4 pb-2 px-4">
            <!-- <h5 class="modal-title fw-semibold">Set Availability</h5> -->
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

            {{-- ✅ Select Multiple Dates --}}
            <div class="mb-3">
                {{ Form::label('start_date', 'Select Dates', ['class' => 'form-label fw-semibold']) }}
                {{ Form::text('start_date', null, [
                    'class' => 'form-control date',
                    'id' => 'start_date',
                    'required',
                    'autocomplete' => 'off',
                    'placeholder' => 'Select one or more dates',
                ]) }}
            </div>

            
            {{-- ✅ Time Ranges --}}
            <div id="time-ranges">
                <div class="time-range row g-2 mb-2">
                    <div class="col-md-5">
                        {{ Form::label('start_time[]', 'Start Time', ['class' => 'form-label fw-semibold']) }}
                        {{ Form::input('time', 'start_time[]', null, ['class' => 'form-control', 'required']) }}
                    </div>
                    <div class="col-md-5">
                        {{ Form::label('end_time[]', 'End Time', ['class' => 'form-label fw-semibold']) }}
                        {{ Form::input('time', 'end_time[]', null, ['class' => 'form-control', 'required']) }}
                    </div>
                </div>
            </div>

            <div class="mb-3">
                <button type="button" id="add-range-btn" class="btn btn-primary btn-sm">+ Add Time Slot</button>
            </div>

            {{-- ✅ Start on the Hour Option --}}
            <div class="mb-3">
                <div class="form-check">
                    {{ Form::checkbox('start_on_hour', '1', false, [
                        'class' => 'form-check-input',
                        'id' => 'start_on_hour'
                    ]) }}
                    {{ Form::label('start_on_hour', 'Start lessons on the hour', [
                        'class' => 'form-check-label fw-semibold'
                    ]) }}
                </div>
                <div class="start-on-hour-help">
                    When enabled, lessons will start at exact hours (8:00, 9:00, 10:00, etc.) instead of following exact availability start times.
                </div>
            </div>

            {{-- ✅ Location --}}
            <div class="mb-3">
                {{ Form::label('location', 'Location', ['class' => 'form-label fw-semibold']) }}
                {{ Form::text('location', null, [
                    'class' => 'form-control',
                    'placeholder' => 'Enter Location',
                    'required',
                ]) }}
            </div>

            {{-- ✅ Lessons --}}
            <div class="form-group">
                @if (!empty($lesson))
                    {{ Form::label('package_lesson', 'This Availability Applies To', ['class' => 'form-label fw-semibold']) }}
                    <div class="lesson-checkboxes" style="border: 1px solid #dee2e6; border-radius: 6px; padding: 10px; max-height: 200px; overflow-y: auto;">
                        @foreach ($lesson as $le)
                            <div class="form-check mb-1">
                                <input type="checkbox" name="lesson_id[]" class="form-check-input" value="{{ $le['id'] }}">
                                <label class="form-check-label">
                                    {{ $le['lesson_name'] }}
                                    @if (isset($le['lesson_duration']))
                                        <span style="color:gray"> ({{ $le['lesson_duration'] }} hour(s))</span>
                                    @endif
                                </label>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-muted mb-0">No lessons available</p>
                @endif
            </div>

            {{-- ✅ Submit --}}
            <div class="mt-4 text-end">
                <!-- <button type="submit" class="btn btn-primary">Save</button> -->
            </div>

            {!! Form::close() !!}
        </div>
    </div>

    <script>
    $(document).ready(function() {

        // ✅ Initialize Multi-Date Picker
        $('.date').datepicker({
            startDate: new Date(),
            multidate: true,
            format: 'yyyy-mm-dd',
            todayHighlight: true
        });

        // ✅ Manage Time Slots
        const container = $('#time-ranges');
        const addBtn = $('#add-range-btn');

        addBtn.on('click', function() {
            const newRange = $(`
                <div class="time-range row g-2 mb-2">
                    <div class="col-md-5">
                        <label class="form-label fw-semibold">Start Time</label>
                        <input type="time" name="start_time[]" class="form-control" required>
                    </div>
                    <div class="col-md-5">
                        <label class="form-label fw-semibold">End Time</label>
                        <input type="time" name="end_time[]" class="form-control" required>
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="button" class="btn btn-danger btn-sm remove-range">X</button>
                    </div>
                </div>
            `);
            container.append(newRange);
        });

        container.on('click', '.remove-range', function() {
            if (container.find('.time-range').length > 1) {
                $(this).closest('.time-range').remove();
            } else {
                alert('At least one time range is required');
            }
        });
    });
    </script>
</body>
</html>