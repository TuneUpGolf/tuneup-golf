@extends('layouts.main')
@section('title', __('Set Availability for lesson'))
@section('content')
    <div class="main-content">
        <section class="section">
            <div class="section-body">
                @if (tenant('id') == null)
                    <div class="alert alert-warning">
                        {{ __('Your database user must have permission to CREATE DATABASE, because we need to create database when new tenant create.') }}
                    </div>
                @endif
                @if (session('error'))
                    <div class="alert alert-danger">
                        {{ session('error') }}
                    </div>
                @endif
                <div class="m-auto col-lg-6 col-md-8 col-xxl-4">
                    <div class="card">
                        <div class="card-header flex items-center justify-between">
                            <h5>{{ __('Set Availability') }} - 110</h5>

                        </div>
                        <div class="card-body">
                            {!! Form::open([
                                'route' => ['slot.availability', ['redirect' => 1]],
                                'method' => 'Post',
                                'data-validate',
                                'files' => 'true',
                                'enctype' => 'multipart/form-data',
                            ]) !!}
                            <div class="form-group ">
                                {{ Form::label('start_date', __('Select Dates'), ['class' => 'form-label']) }}
                                {{ Form::input('text', 'start_date', 'null', ['id' => 'start_date', 'class' => 'form-control date', 'required', 'autocomplete' => 'off']) }}
                            </div>
                            {{-- <div class="form-group">
                                {{ Form::label('start_time', __('Start Time'), ['class' => 'form-label']) }}
                                {{ Form::input('time', 'start_time', null, ['id' => 'start_time', 'class' => 'form-control', 'required']) }}
                            </div>
                            <div class="form-group ">
                                {{ Form::label('end_time', __('End Time'), ['class' => 'form-label']) }}
                                {{ Form::input('time', 'end_time', null, ['id' => 'end_time', 'class' => 'form-control', 'required']) }}
                            </div> --}}

                             {{-- Dynamic Time Ranges --}}
                            <div id="time-ranges">
                                <div class="time-range row g-2 mb-2">
                                    <div class="col-md-5">
                                        {{ Form::label('start_time[]', __('Start Time'), ['class' => 'form-label']) }}
                                        {{ Form::input('time', 'start_time[]', null, ['class' => 'form-control','required']) }}
                                    </div>
                                    <div class="col-md-5">
                                        {{ Form::label('end_time[]', __('End Time'), ['class' => 'form-label']) }}
                                        {{ Form::input('time', 'end_time[]', null, ['class' => 'form-control','required']) }}
                                    </div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <button type="button" id="add-range" class="btn btn-primary">+ Add </button>
                            </div>
                            <div class="form-group">
                                {{ Form::label('location', __('Location'), ['class' => 'form-label']) }}
                                {!! Form::text('location', null, [
                                    'class' => 'form-control',
                                    'required',
                                    'placeholder' => __('Enter Location'),
                                ]) !!}
                            </div>
                            <div class="form-group">
                                @if (!empty($lesson))
                                    {{ Form::label('package_lesson', __('This Availability Applies To'), ['class' => 'form-label']) }}
                                    <br>
                                    @foreach ($lesson as $le)
                                        <input type="checkbox" name="lesson_id[]" class="form-check-input"
                                            value={{ $le['id'] }}> {{ $le['lesson_name'] }} <span style="color:gray">
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
                        </div>
                        <div class="card-footer">
                            <div class="float-end">
                                <a href="{{ route('student.index') }}" class="btn btn-secondary">{{ __('Cancel') }}</a>
                                {{ Form::button(__('Save'), ['type' => 'submit', 'class' => 'btn btn-primary']) }}
                            </div>
                            {!! Form::close() !!}
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection
@push('css')
    <script src="{{ asset('assets/js/plugins/choices.min.js') }}"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.12/css/intlTelInput.min.css">
@endpush
@push('javascript')
    <script src="{{ asset('vendor/intl-tel-input/jquery.mask.js') }}"></script>
    <script src="{{ asset('vendor/intl-tel-input/intlTelInput-jquery.min.js') }}"></script>
    <script src="{{ asset('vendor/intl-tel-input/utils.min.js') }}"></script>
    {{-- <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.0/jquery.min.js"></script> --}}
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.3.0/js/bootstrap-datepicker.js"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.8.0/css/bootstrap-datepicker.css"
        rel="stylesheet" />
    </script>

    <script type="text/javascript">
        $('.date').datepicker({
            startDate: new Date(),
            multidate: true,
            format: 'yyyy-mm-dd'
        });
        $('.date').datepicker('setDates', [new Date(2014, 2, 5), new Date(2014, 3, 5)])

        // time ranges start
        let container = $('#time-ranges');
        let addBtn = $('#add-range');


        addBtn.on('click', function() {
            let newRange = container.find('.time-range:first').clone();

            // Reset inputs
            newRange.find('input').val('');

            // Add remove button if not exists
            if (newRange.find('.remove-range').length === 0) {
                newRange.append(`
                <div class="col-md-1 d-flex align-items-end">
                    <button type="button" class="btn btn-danger remove-range">X</button>
                </div>
            `);
            }

            container.append(newRange);
        });

        // Handle remove
        container.on('click', '.remove-range', function() {
            $(this).closest('.time-range').remove();
        });

        // time ranges end
    </script>
@endpush

@push('javascript')
<script>
$(function() {
  // Allowed minute options (only these minutes will be used)
  const ALLOWED_MINUTES = [0, 15, 30];

  function pad(n){ return String(n).padStart(2, '0'); }

  /**
   * Round a "HH:MM" (or "H:MM") time string to the nearest allowed minute.
   * Returns "HH:MM" 24-hour formatted.
   */
  function roundToAllowedTime(timeStr) {
    if (!timeStr) return timeStr;
    // Accept "HH:MM" or "HH:MM:SS"
    const m = timeStr.match(/^(\d{1,2}):(\d{2})(?::\d{2})?$/);
    if (!m) return timeStr;

    const hours = parseInt(m[1], 10);
    const minutes = parseInt(m[2], 10);
    const t = hours * 60 + minutes;

    let best = null;
    let bestDiff = Number.POSITIVE_INFINITY;

    // consider allowed minutes in previous, same and next hour to permit nearest rounding
    for (let hDelta = -1; hDelta <= 1; hDelta++) {
      const hCand = hours + hDelta;
      if (hCand < 0 || hCand > 23) continue;
      for (const am of ALLOWED_MINUTES) {
        const candidate = hCand * 60 + am;
        const diff = Math.abs(candidate - t);
        if (diff < bestDiff) {
          bestDiff = diff;
          best = candidate;
        }
      }
    }

    if (best === null) return timeStr;

    const newH = Math.floor(best / 60) % 24;
    const newM = best % 60;
    return pad(newH) + ':' + pad(newM);
  }

  // Delegated handler: when user finishes editing / time changes, round it
  $(document).on('blur change input', 'input[type="time"][name="start_time[]"], input[type="time"][name="end_time[]"]', function(e) {
    // Only act on blur or change or when input length looks like a full value (some browsers fire input during typing)
    // We still handle on 'input' to keep mobile pickers responsive.
    const $el = $(this);
    const oldVal = $el.val();
    if (!oldVal) return;
    const newVal = roundToAllowedTime(oldVal);
    if (newVal !== oldVal) {
      $el.val(newVal);
      // trigger change so any other listeners react
      $el.trigger('change');
    }
  });

  // Optional: restrict key presses to digits, colon and control keys (prevents typing weird characters)
  $(document).on('keydown', 'input[type="time"][name="start_time[]"], input[type="time"][name="end_time[]"]', function(e) {
    // Allow: backspace(8), tab(9), enter(13), escape(27), arrows (37..40), delete(46)
    const allowedKeys = [8,9,13,27,37,38,39,40,46];
    if (allowedKeys.includes(e.keyCode)) return;
    // Allow numbers and colon
    if (/^[0-9:]$/.test(e.key)) return;
    e.preventDefault();
  });

  // If you previously added step="900" on the input, it's okay to REMOVE it — the JS rounding is authoritative.
  // The script works for dynamically cloned/added rows because it uses delegation.
});
</script>
@endpush

