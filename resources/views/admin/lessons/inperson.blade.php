@extends('layouts.main')
@section('title', __('Create In-Person Lesson'))
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('home') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item"><a href="{{ route('lesson.index') }}">{{ __('Lesson') }}</a></li>
    <li class="breadcrumb-item">{{ __('Create In-Person Lesson') }}</li>
@endsection
@section('content')
    <div class="main-content">
        <section class="section">
            <div class="section-body">
                @if (tenant('id') == null)
                    <div class="alert alert-warning">
                        {{ __('Your database user must have permission to CREATE DATABASE, because we need to create database when new tenant create.') }}
                    </div>
                @endif
                <div class="m-auto col-lg-8 col-md-8 col-xxl-8">
                    <div class="card">
                        <div class="card-header">
                            <h5>{{ __('Create In-Person Lesson') }}</h5>
                        </div>
                        <div class="card-body">
                            {!! Form::open([
                                'route' => ['lesson.store', ['type' => 'inPerson']],
                                'method' => 'Post',
                                'data-validate',
                                'files' => 'true',
                                'enctype' => 'multipart/form-data',
                            ]) !!}

                            <!-- Package Lesson Checkbox -->
                            <div class="form-group">
                                <div class="form-check">
                                    {!! Form::radio('is_package_lesson', 1, false, [
                                        'class' => 'form-check-input',
                                        'id' => 'radio_package_lesson',
                                    ]) !!}
                                    {{ Form::label('radio_package_lesson', __('Package Lesson'), ['class' => 'form-check-label']) }}
                                </div>
                                <div class="form-check">
                                    {!! Form::radio('is_package_lesson', 0, false, [
                                        'class' => 'form-check-input',
                                        'id' => 'radio_pre_sets_dates',
                                    ]) !!}
                                    {{ Form::label('radio_pre_sets_dates', __('Pre-sets date Lesson'), ['class' => 'form-check-label']) }}
                                </div>
                            </div>
                            <!-- Name -->
                            <div class="form-group">
                                {{ Form::label('name', __('Name'), ['class' => 'form-label']) }}
                                {!! Form::text('lesson_name', null, ['class' => 'form-control', 'required', 'placeholder' => __('Enter name')]) !!}
                            </div>
                            
                            <div class="flex gap-1 itmes-center mb-2 cursor-pointer add-more-package">
                                <i class="ti ti-plus text-2xl"></i><span>Add Package Options</span>
                            </div>
                            <div id="package-options-container">
                                <div class="flex gap-2" id="number_slot">
                                    <div class="form-group  w-50">
                                        {{ Form::label('no_of_slot', __('Package Size'), ['class' => 'form-label']) }}
                                        <select type="dropdwon" name="package_lesson[0][no_of_slot]" class="form-control"
                                            required>
                                            <option value="">No. of slot</option>
                                            <option value="1">1</option>
                                            <option value="2">2</option>
                                            <option value="3">3</option>
                                            <option value="4">4</option>
                                            <option value="5">5</option>
                                            <option value="6">6</option>
                                            <option value="7">7</option>
                                            <option value="8">8</option>
                                            <option value="9">9</option>
                                            <option value="10">10</option>
                                        </select>
                                    </div>
                                    <!-- Price -->
                                    <div class="form-group  w-50 price-field">
                                        {{ Form::label('price', __('Price'), ['class' => 'form-label']) }}
                                        <input type="number" class="form-control" name="package_lesson[0][price]"
                                            placeholder="Enter Price" required />
                                    </div>
                                </div>
                            </div>
                            <small id="package_note" class="text-muted d-none">
                                {{ __('Since this is a package lesson, the price should account for all booked slots.') }}
                            </small>
                            <div class="form-group" id="lesson_price">
                                {{ Form::label('price', __('Price'), ['class' => 'form-label']) }}
                                {!! Form::number('lesson_price', null, [
                                    'class' => 'form-control',
                                    'required',
                                    'placeholder' => __('Enter Price'),
                                    'min' => 1,
                                ]) !!}
                            </div>
                            <!-- Lesson Duration -->
                            <div class="form-group">
                                {{ Form::label('lesson_duration', __('Duration'), ['class' => 'form-label']) }}
                                {!! Form::select(
                                    'lesson_duration',
                                    [
                                        '0.5' => '30 Minutes',
                                        '0.75' => '45 Minutes',
                                        '1' => '1 Hour',
                                        '1.25' => '1 Hour 15 Minutes',
                                        '1.5' => '1 Hour 30 Minutes',
                                        '1.75' => '1 Hour 45 Minutes',
                                        '2' => '2 Hours',
                                        '2.25' => '2 Hours 15 Minutes',
                                        '2.5' => '2 Hours 30 Minutes',
                                        '2.75' => '2 Hours 45 Minutes',
                                        '3' => '3 Hours',
                                        '3.25' => '3 Hours 15 Minutes',
                                        '3.5' => '3 Hours 30 Minutes',
                                    ],
                                    null,
                                    [
                                        'class' => 'form-control',
                                        'data-trigger',
                                        'required',
                                        'placeholder' => __('Duration'),
                                    ],
                                ) !!}
                            </div>

                            <!-- Group Size -->
                            <div class="form-group">
                                {{ Form::label('max_students', __('Group Size'), ['class' => 'form-label']) }}
                                {!! Form::number('max_students', null, [
                                    'class' => 'form-control',
                                    'required',
                                    'placeholder' => __('Enter group size'),
                                    'min' => 1,
                                ]) !!}
                            </div>

                            <!-- Payment Method -->
                            <div class="form-group">
                                {{ Form::label('payment_method', __('Payment Method'), ['class' => 'form-label']) }}
                                {!! Form::select('payment_method', ['online' => 'Online', 'cash' => 'Cash'], null, [
                                    'class' => 'form-control',
                                    'data-trigger',
                                    'required',
                                    'id' => 'payment_method',
                                ]) !!}
                                <!-- Hidden input to store payment method when disabled -->
                                {{-- {!! Form::hidden('payment_method', 'online', ['id' => 'hidden_payment_method']) !!} --}}
                            </div>
                            <!-- Description -->
                            <div class="form-group">
                                {{ Form::label('description', __('Description'), ['class' => 'form-label']) }}
                                {!! Form::textarea('lesson_description', null, [
                                    'class' => 'form-control',
                                    'required',
                                    'placeholder' => __('Enter Description'),
                                ]) !!}
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
    <style>
        .price-field::after {
            content: "\eb84";
            font-family: tabler-icons;
            position: absolute;
            top: 36px;
            left: 10px;
            font-size: 17px;

        }

        .price-field {
            position: relative;
        }

        .price-field input {
            padding-left: 30px;
        }
    </style>
@endpush

@push('javascript')
    <script src="{{ asset('vendor/intl-tel-input/jquery.mask.js') }}"></script>
    <script src="{{ asset('vendor/intl-tel-input/intlTelInput-jquery.min.js') }}"></script>
    <script src="{{ asset('vendor/intl-tel-input/utils.min.js') }}"></script>
    <script src="{{ asset('vendor/ckeditor/ckeditor.js') }}"></script>
    <script>
        CKEDITOR.replace('lesson_description', {
            filebrowserUploadUrl: "{{ route('ckeditor.upload', ['_token' => csrf_token()]) }}",
            filebrowserUploadMethod: 'form'
        });

        document.addEventListener('DOMContentLoaded', function() {
            //const paymentMethodSelect = document.getElementById('payment_method');
            //const hiddenPaymentMethod = document.getElementById('hidden_payment_method');
            const packageNote = document.getElementById('package_note');
            const lessonPriceWrapper = document.getElementById('lesson_price');
            const lessonPriceInput = lessonPriceWrapper.querySelector('input[name="lesson_price"]');
            const errorMessage = document.getElementById('bouncer-error_lesson_price');
            const numberSlotSection = $("#number_slot");
            const addMoreButton = $(".add-more-package");
            const packageContainer = $('#package-options-container');

            let packageCount = 1;

            numberSlotSection.hide();
            addMoreButton.hide();

            const initialPriceInput = document.querySelector('input[name="package_lesson[0][price]"]');
            const initialSlotSelect = document.querySelector('select[name="package_lesson[0][no_of_slot]"]');
            initialPriceInput.removeAttribute('required');
            initialSlotSelect.removeAttribute('required');

            function togglePackageLessonSettings() {
                const selectedValue = document.querySelector('input[name="is_package_lesson"]:checked')?.value;

                if (selectedValue == 1) {
                    // paymentMethodSelect.value = 'online';
                    // paymentMethodSelect.setAttribute('disabled', 'disabled');
                    // hiddenPaymentMethod.value = 'online';
                    packageNote?.classList.remove('d-none');

                    numberSlotSection.show();
                    addMoreButton.show();

                    lessonPriceWrapper.style.display = 'none';
                    lessonPriceInput.disabled = true;
                    lessonPriceInput.removeAttribute('required');

                    if (errorMessage) errorMessage.remove();
                    lessonPriceInput.classList.remove('error');
                    lessonPriceInput.setAttribute('aria-invalid', 'false');

                    initialPriceInput.setAttribute('required', '');
                    initialSlotSelect.setAttribute('required', '');
                } else {
                    //paymentMethodSelect.removeAttribute('disabled');
                    packageNote?.classList.add('d-none');

                    numberSlotSection.hide();
                    addMoreButton.hide();

                    lessonPriceWrapper.style.display = 'block';
                    lessonPriceInput.disabled = false;
                    lessonPriceInput.setAttribute('required', 'required');

                    initialPriceInput.removeAttribute('required');
                    initialSlotSelect.removeAttribute('required');
                }
            }

            // Bind change event to both radios
            document.querySelectorAll('input[name="is_package_lesson"]').forEach(function(radio) {
                radio.addEventListener('change', togglePackageLessonSettings);
            });

            // Initial setup
            togglePackageLessonSettings();

            addMoreButton.on('click', function() {
                const newPackage = $('#number_slot:first').clone();
                newPackage.find('select').val('');
                newPackage.find('input').val('');
                newPackage.find('select').attr('name', `package_lesson[${packageCount}][no_of_slot]`);
                newPackage.find('input').attr('name', `package_lesson[${packageCount}][price]`);
                packageContainer.append(newPackage);
                packageCount++;
            });
        });
    </script>
@endpush
