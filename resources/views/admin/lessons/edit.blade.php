@extends('layouts.main')
@section('title', __('Edit Lesson'))
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('home') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item"><a href="{{ route('lesson.index') }}">{{ __('Lesson') }}</a></li>
    <li class="breadcrumb-item">{{ __('Edit Lesson') }}</li>
@endsection
@section('content')
    <div class="main-content">
        <section class="section">
            <div class="m-auto col-lg-6 col-md-8 col-xxl-4">
                <div class="card">
                    <div class="card-header">
                        <h5>{{ __('Edit Lesson') }}</h5>
                    </div>
                    <div class="card-body">
                        {!! Form::model($user, [
                            'route' => ['lesson.update', $user->id],
                            'method' => 'Put',
                            'data-validate',
                        ]) !!}

                        <!-- Package Lesson Checkbox (Disabled if it's a package lesson) -->
                        @if ($user->type == 'package')
                            <div class="form-group">
                                <div class="form-check">
                                    {!! Form::checkbox('is_package_lesson', 1, true, [
                                        'class' => 'form-check-input',
                                        'id' => 'is_package_lesson',
                                        'disabled' => 'disabled',
                                    ]) !!}
                                    {{ Form::label('is_package_lesson', __('Package Lesson (Cannot be changed)'), ['class' => 'form-check-label']) }}
                                </div>
                            </div>
                        @endif

                        <div class="form-group ">
                            {{ Form::label('name', __('Name'), ['class' => 'form-label']) }}
                            {!! Form::text('lesson_name', null, ['class' => 'form-control', 'required', 'placeholder' => __('Enter name')]) !!}
                        </div>
                        <div class="form-group">
                            {{ Form::label('description', __('Description'), ['class' => 'form-label']) }}
                            {!! Form::text('lesson_description', null, [
                                'class' => 'form-control',
                                'required',
                                'placeholder' => __('Enter Description'),
                            ]) !!}
                        </div>
                        @if ($user->type == 'package')
                            <div class="form-group">
                        <div class="flex gap-1 itmes-center mb-2 cursor-pointer add-more-package">
                                <i class="ti ti-plus text-2xl"></i><span>Add Package Options</span>
                            </div>
                            @foreach ($user->packages as $package)
                           
                                <div class="flex gap-2 mb-2 slots" id="number_slot">
                                    <div class="form-group w-50">
                                        {{ Form::label('no_of_slot', __('Package Size'), ['class' => 'form-label']) }}
                                        <select type="dropdown" name="exist_package_lesson[{{ $loop->index }}][no_of_slot]"
                                            class="form-control" required >
                                            <option value="">No. of slot</option>
                                            @for ($i = 1; $i <= 10; $i++)
                                                <option value={{ $i }} {{ $package->number_of_slot == $i ? 'selected' : '' }} >
                                                    {{ $i }} </option>
                                            @endfor
                                        </select>
                                    </div>
                                    <!-- Price -->
                                    <div class="form-group w-50 price-field">
                                        {{ Form::label('price', __('Price'), ['class' => 'form-label']) }}
                                        <input type="number" class="form-control" name="exist_package_lesson[{{ $loop->index }}][price]"
                                            placeholder="Enter Price" value="{{ $package->price }}" />
                                    </div>
                                </div>
                            @endforeach
                         @else
                            <div class="form-group">
                                {{ Form::label('price', __('Price ($)'), ['class' => 'form-label']) }}
                                {!! Form::number('lesson_price', null, [
                                    'class' => 'form-control',
                                    'required',
                                    'placeholder' => __('Enter Price'),
                                ]) !!}
                            </div>
                        @endif
                        @if ($user->type !== 'inPerson' && $user->type !== 'package')
                            <div class="form-group">
                                {{ Form::label('quantity', __('Quantity'), ['class' => 'form-label']) }}
                                {!! Form::number('lesson_quantity', null, [
                                    'class' => 'form-control',
                                    'placeholder' => __('Enter Quantity'),
                                ]) !!}
                            </div>

                            <div class="form-group">
                                {{ Form::label('response_time', __('Response Time'), ['class' => 'form-label']) }}
                                {!! Form::number('required_time', null, [
                                    'class' => 'form-control',
                                    'placeholder' => __('Enter Required Time'),
                                ]) !!}
                            </div>
                        @endif

                        @if ($user->type === 'inPerson' || $user->type === 'package')
                            <div class="form-group">
                                {{ Form::label('lesson_duration', __('Duration (hours)'), ['class' => 'form-label']) }}
                                {!! Form::select(
                                    'lesson_duration',
                                    [
                                        '0.5' => '30 Minutes',
                                        '0.75' => '45 Minutes',
                                        '1.0' => '1 Hour',
                                        '1.25' => '1 Hour 15 Minutes',
                                        '1.5' => '1 Hour 30 Minutes',
                                        '1.75' => '1 Hour 45 Minutes',
                                        '2.0' => '2 Hours',
                                        '2.25' => '2 Hours 15 Minutes',
                                        '2.5' => '2 Hours 30 Minutes',
                                        '2.75' => '2 Hours 45 Minutes',
                                        '3.0' => '3 Hours',
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
                            <div class="form-group">
                                {{ Form::label('max_students', __('Group Size'), ['class' => 'form-label']) }}
                                {!! Form::number('max_students', null, [
                                    'class' => 'form-control',
                                    'required',
                                    'placeholder' => __('Enter group size'),
                                    'min' => 1,
                                ]) !!}
                            </div>
                            <div class="form-group">
                                {{ Form::label('payment_method', __('Payment Method'), ['class' => 'form-label']) }}
                                {!! Form::select('payment_method', ['online' => 'Online', 'cash' => 'Cash', 'both' => 'Both'], null, [
                                    'class' => 'form-control',
                                    'data-trigger',
                                    'placeholder' => __('Payment Method'),
                                ]) !!}
                            </div>
                        @endif
                    </div>
                    <div class="card-footer">
                        <div class="float-end">
                            <a href="{{ route('lesson.index') }}" class="btn btn-secondary">{{ __('Cancel') }}</a>
                            {{ Form::button(__('Save'), ['type' => 'submit', 'class' => 'btn btn-primary']) }}
                        </div>
                        {!! Form::close() !!}
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection

@push('css')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.12/css/intlTelInput.min.css">
@endpush
@push('javascript')
    <script src="{{ asset('assets/js/plugins/choices.min.js') }}"></script>
    <script src="{{ asset('vendor/intl-tel-input/jquery.mask.js') }}"></script>
    <script src="{{ asset('vendor/intl-tel-input/intlTelInput-jquery.min.js') }}"></script>
    <script src="{{ asset('vendor/intl-tel-input/utils.min.js') }}"></script>
    
    <script type="text/javascript">
        $(document).ready(function () {
            let index = 1;
            // Add more package options dynamically
            $('.add-more-package').on('click', function () {
                const newPackage = `
                    <div class="flex gap-2 mb-2" id="number_slot">
                        <div class="form-group w-50">
                            {{ Form::label('no_of_slot', __('Package Size'), ['class' => 'form-label']) }}
                            <select type="dropdown" name="package_lesson[${index}][no_of_slot]" class="form-control" required>
                                <option value="">No. of slot</option>
                                @for ($i = 1; $i <= 10; $i++)
                                    <option value="{{ $i }}">{{ $i }}</option>
                                @endfor
                            </select>
                        </div>
                        <div class="form-group w-50 price-field">
                            {{ Form::label('price', __('Price'), ['class' => 'form-label']) }}
                            <input type="number" class="form-control" name="package_lesson[${index}][price]"
                                placeholder="Enter Price" required />
                        </div>
                    </div>`;
                $(this).after(newPackage);
                index++;
            });
        });
    </script>
@endpush