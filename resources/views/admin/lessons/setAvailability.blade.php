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
                                'files' => true,
                                'enctype' => 'multipart/form-data',
                            ]) !!}

                            {{-- Select dates --}}
                            <div class="form-group">
                                {{ Form::label('start_date', __('Select Dates'), ['class' => 'form-label']) }}
                                {{ Form::input('text', 'start_date', null, [
                                    'id' => 'start_date',
                                    'class' => 'form-control date',
                                    'required',
                                    'autocomplete' => 'off',
                                ]) }}
                            </div>

                            {{-- Time ranges --}}
                            <div id="time-ranges">
                                <div class="time-range row g-2 mb-2">
                                    <div class="col-md-5">
                                        {{ Form::label('start_time[]', __('Start Time'), ['class' => 'form-label']) }}
                                        {{ Form::input('time', 'start_time[]', null, ['class' => 'form-control', 'required']) }}
                                    </div>
                                    <div class="col-md-5">
                                        {{ Form::label('end_time[]', __('End Time'), ['class' => 'form-label']) }}
                                        {{ Form::input('time', 'end_time[]', null, ['class' => 'form-control', 'required']) }}
                                    </div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <button type="button" id="add-range" class="btn btn-primary">+ Add</button>
                            </div>

                            {{-- Location --}}
                            <div class="form-group">
                                {{ Form::label('location', __('Location'), ['class' => 'form-label']) }}
                                {!! Form::text('location', null, [
                                    'class' => 'form-control',
                                    'required',
                                    'placeholder' => __('Enter Location'),
                                ]) !!}
                            </div>

                            {{-- Availability applies to --}}
                            <div class="form-group">
                                @if (!empty($lesson))
                                    {{ Form::label('package_lesson', __('This Availability Applies To'), ['class' => 'form-label']) }}
                                    <br>
                                    @foreach ($lesson as $le)
                                        <input type="checkbox" name="lesson_id[]" class="form-check-input"
                                            value="{{ $le['id'] }}">
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

                            {{-- ✅ Mobile buttons --}}
                            <div class="card-footer d-md-none">
                                <div class="float-end">
                                    <a href="{{ route('student.index') }}" class="btn btn-secondary">
                                        {{ __('Cancel') }}
                                    </a>
                                    {{ Form::button(__('Save'), [
                                        'type' => 'submit',
                                        'class' => 'btn btn-primary',
                                    ]) }}
                                </div>
                            </div>

                        </div> {{-- end card-body --}}

                        {{-- ✅ Desktop buttons --}}
                        <div class="card-footer d-none d-md-block">
                            <div class="float-end">
                                <a href="{{ route('student.index') }}" class="btn btn-secondary">
                                    {{ __('Cancel') }}
                                </a>
                                {{ Form::button(__('Save'), ['type' => 'submit', 'class' => 'btn btn-primary ms-2']) }}
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
    <style>
        @media (max-width: 768px) {
            .card-body {
                max-height: 100% !important;
            }
        }
    </style>
@endpush

@push('javascript')
    <script src="{{ asset('vendor/intl-tel-input/jquery.mask.js') }}"></script>
    <script src="{{ asset('vendor/intl-tel-input/intlTelInput-jquery.min.js') }}"></script>
    <script src="{{ asset('vendor/intl-tel-input/utils.min.js') }}"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.3.0/js/bootstrap-datepicker.js"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.8.0/css/bootstrap-datepicker.css"
        rel="stylesheet" />

    <script type="text/javascript">
        $('.date').datepicker({
            startDate: new Date(),
            multidate: true,
            format: 'yyyy-mm-dd'
        });
        $('.date').datepicker('setDates', [new Date(2014, 2, 5), new Date(2014, 3, 5)]);

        let container = $('#time-ranges');
        let addBtn = $('#add-range');
        addBtn.on('click', function() {
            let newRange = container.find('.time-range:first').clone();
            newRange.find('input').val('');
            if (newRange.find('.remove-range').length === 0) {
                newRange.append(`
                    <div class="col-md-1 d-flex align-items-end">
                        <button type="button" class="btn btn-danger remove-range">X</button>
                    </div>
                `);
            }
            container.append(newRange);
        });

        container.on('click', '.remove-range', function() {
            $(this).closest('.time-range').remove();
        });
    </script>
@endpush
