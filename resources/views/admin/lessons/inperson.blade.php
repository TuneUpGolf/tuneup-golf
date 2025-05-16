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
                <div class="m-auto col-lg-6 col-md-8 col-xxl-4">
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
                                    {!! Form::checkbox('is_package_lesson', 1, false, [
                                        'class' => 'form-check-input',
                                        'id' => 'is_package_lesson',
                                    ]) !!}
                                    {{ Form::label('is_package_lesson', __('Package Lesson'), ['class' => 'form-check-label']) }}
                                </div>
                            </div>
                            <!-- Name -->
                            <div class="form-group">
                                {{ Form::label('name', __('Name'), ['class' => 'form-label']) }}
                                {!! Form::text('lesson_name', null, ['class' => 'form-control', 'required', 'placeholder' => __('Enter name')]) !!}
                            </div>

                            <!-- Description -->
                            <div class="form-group">
                                {{ Form::label('description', __('Description'), ['class' => 'form-label']) }}
                                {!! Form::text('lesson_description', null, [
                                    'class' => 'form-control',
                                    'required',
                                    'placeholder' => __('Enter Description'),
                                ]) !!}
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
                                        '1.5' => '1.5 Hours',
                                        '2' => '2 Hours',
                                        '2.5' => '2.5 Hours',
                                        '3' => '3 Hours',
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
                                {!! Form::select('payment_method', ['online' => 'Online', 'cash' => 'Cash', 'both' => 'Both'], null, [
                                    'class' => 'form-control',
                                    'data-trigger',
                                    'required',
                                    'id' => 'payment_method',
                                ]) !!}
                                <!-- Hidden input to store payment method when disabled -->
                                {!! Form::hidden('payment_method', 'online', ['id' => 'hidden_payment_method']) !!}
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

    <script>
        // // $('.add-more-package').hide().prop('disabled', true);
        // //$("#package_type").hide();
        // $("#number_slot").hide();
        // //$('#number_slot input').prop('disabled', true).prop('required', false);
        // //$('#number_slot select').prop('disabled', true).prop('required', false);
        // $(".add-more-package").hide();
        // const priceInput = document.querySelector('input[name="package_lesson[0][price]"]');
        // priceInput.removeAttribute('required'); // when hiding
        // priceInput.setAttribute('required', ''); // when showing again

       

        // document.addEventListener('DOMContentLoaded', function() {
        //     let packageLessonCheckbox = document.getElementById('is_package_lesson');
        //     let paymentMethodSelect = document.getElementById('payment_method');
        //     let hiddenPaymentMethod = document.getElementById('hidden_payment_method');
        //     let packageNote = document.getElementById('package_note');

        //     const slotInput = document.querySelector('select[name="package_lesson[0][no_of_slot]"]');
        //     slotInput.removeAttribute('required'); // when hiding
        //     slotInput.setAttribute('required', ''); // when showing again

        //     function togglePackageLessonSettings() {
        //         if (packageLessonCheckbox.checked) {
        //             paymentMethodSelect.value = 'online';
        //             paymentMethodSelect.setAttribute('disabled', 'disabled');
        //             hiddenPaymentMethod.value = 'online'; // Ensure it's sent in form data
        //             packageNote.classList.remove('d-none'); // Show the price note
        //         } else {
        //             paymentMethodSelect.removeAttribute('disabled');
        //             packageNote.classList.add('d-none'); // Hide the price note
        //         }
        //     }

        //     packageLessonCheckbox.addEventListener('change', togglePackageLessonSettings);
        //     togglePackageLessonSettings(); // Run on page load
        // });
        // //const price = document.getElementById('lesson_price');
        // $("#is_package_lesson").change(function() {
        //     const wrapper = document.getElementById('lesson_price');
        //     const input = wrapper.querySelector('input[name="lesson_price"]');
        //     const errorMessage = document.getElementById('bouncer-error_lesson_price');
        //     if ($(this).prop("checked") == true) {
        //         //$("#package_type").show();
        //         $("#number_slot").show();
        //         $('.add-more-package').show();
        //         $(this).val(1);
        //         wrapper.style.display = 'none';
        //         input.disabled = true;
        //         input.removeAttribute('required');

        //         // Remove error message if present
        //         if (errorMessage) {
        //             errorMessage.remove();
        //         }
        //         // Also reset the error class
        //         input.classList.remove('error');
        //         input.setAttribute('aria-invalid', 'false');

        //     } else {
        //         $("#number_slot").remove();
        //        // $("#package_type").hide();
        //         $("#number_slot").hide();
        //         $('.add-more-package').hide();
        //         $(this).val(0);
        //         wrapper.style.display = 'block';
        //         input.disabled = false;
        //         input.setAttribute('required', 'required');
        //     }
        // });
        // $(document).ready(function() {
           
        //     let count = 1;
        //     $('.add-more-package').on('click', function() {
        //         let cloned = $('#number_slot:first').clone();
        //         // Clear values
        //         cloned.find('select').val('');
        //         cloned.find('input').val('');

        //         // Optional: Adjust the names to have array format
        //         cloned.find('select').attr('name', `package_lesson[${count}][no_of_slot]`);
        //         cloned.find('input').attr('name', `package_lesson[${count}][price]`);

        //         $('#package-options-container').append(cloned);
        //         count++;
        //     });
        // });
        
        
        document.addEventListener('DOMContentLoaded', function () {
        const packageLessonCheckbox = document.getElementById('is_package_lesson');
        const paymentMethodSelect = document.getElementById('payment_method');
        const hiddenPaymentMethod = document.getElementById('hidden_payment_method');
        const packageNote = document.getElementById('package_note');
        const lessonPriceWrapper = document.getElementById('lesson_price');
        const lessonPriceInput = lessonPriceWrapper.querySelector('input[name="lesson_price"]');
        const errorMessage = document.getElementById('bouncer-error_lesson_price');
        const numberSlotSection = $("#number_slot");
        const addMoreButton = $(".add-more-package");
        const packageContainer = $('#package-options-container');

        let packageCount = 1;

        // Initial state setup
        numberSlotSection.hide();
        addMoreButton.hide();

        // Remove and re-add 'required' on original package input elements
        const initialPriceInput = document.querySelector('input[name="package_lesson[0][price]"]');
        const initialSlotSelect = document.querySelector('select[name="package_lesson[0][no_of_slot]"]');
        initialPriceInput.removeAttribute('required');
        initialSlotSelect.removeAttribute('required');

        /**
         * Toggle package-related fields and payment method depending on checkbox
         */
        function togglePackageLessonSettings() {
            const isChecked = packageLessonCheckbox.checked;

            if (isChecked) {
                paymentMethodSelect.value = 'online';
                paymentMethodSelect.setAttribute('disabled', 'disabled');
                hiddenPaymentMethod.value = 'online';
                packageNote.classList.remove('d-none');

                numberSlotSection.show();
                addMoreButton.show();
                $(packageLessonCheckbox).val(1);

                lessonPriceWrapper.style.display = 'none';
                lessonPriceInput.disabled = true;
                lessonPriceInput.removeAttribute('required');

                if (errorMessage) errorMessage.remove();
                lessonPriceInput.classList.remove('error');
                lessonPriceInput.setAttribute('aria-invalid', 'false');

                // Set required again
                initialPriceInput.setAttribute('required', '');
                initialSlotSelect.setAttribute('required', '');
            } else {
                paymentMethodSelect.removeAttribute('disabled');
                packageNote.classList.add('d-none');

                numberSlotSection.hide();
                addMoreButton.hide();
                $(packageLessonCheckbox).val(0);

                lessonPriceWrapper.style.display = 'block';
                lessonPriceInput.disabled = false;
                lessonPriceInput.setAttribute('required', 'required');

                // Remove required attributes when hiding
                initialPriceInput.removeAttribute('required');
                initialSlotSelect.removeAttribute('required');
            }
        }

        packageLessonCheckbox.addEventListener('change', togglePackageLessonSettings);
        togglePackageLessonSettings(); // Run on initial load

        /**
         * Add more package rows
         */
        addMoreButton.on('click', function () {
            const newPackage = $('#number_slot:first').clone();

            // Reset values
            newPackage.find('select').val('');
            newPackage.find('input').val('');

            // Rename input fields with updated index
            newPackage.find('select').attr('name', `package_lesson[${packageCount}][no_of_slot]`);
            newPackage.find('input').attr('name', `package_lesson[${packageCount}][price]`);

            packageContainer.append(newPackage);
            packageCount++;
        });
    });
    </script>
@endpush
