@extends('layouts.main')
@section('title', __('Create Lesson'))
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('home') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item"><a href="{{ route('lesson.index') }}">{{ __('Lesson') }}</a></li>
    <li class="breadcrumb-item">{{ __('Create Lesson') }}</li>
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
                            <h5>{{ __('Create Lesson') }}</h5>
                        </div>
                        <div class="card-body">
                            {!! Form::open([
                                'route' => ['lesson.store', ['type' => 'online']],
                                'method' => 'Post',
                                'data-validate',
                                'files' => 'true',
                                'enctype' => 'multipart/form-data',
                            ]) !!}
                            <div class="form-group">
                                {{ Form::label('name', __('Lesson Title'), ['class' => 'form-label']) }}
                                {!! Form::text('lesson_name', null, ['class' => 'form-control', 'required', 'placeholder' => __('Enter name')]) !!}
                            </div>

                            <div class="form-group">
                                {{ Form::label('price', __('Price'), ['class' => 'form-label']) }}
                                {!! Form::number('lesson_price', null, [
                                    'class' => 'form-control',
                                    'required',
                                    'placeholder' => __('Enter Price'),
                                ]) !!}
                            </div>
                            <div class="form-group">
                                {{ Form::label('quantity', __('Quantity'), ['class' => 'form-label']) }}
                                {!! Form::number('lesson_quantity', null, [
                                    'class' => 'form-control',
                                    'required',
                                    'placeholder' => __('Enter Quantity'),
                                ]) !!}
                            </div>
                            <div class="form-group">
                                {{ Form::label('response_time', __('Response Time'), ['class' => 'form-label']) }}
                                {!! Form::number('required_time', null, [
                                    'class' => 'form-control',
                                    'required',
                                    'placeholder' => __('Enter Required Time'),
                                ]) !!}
                            </div>

                            <div class="form-group">
                                {{ Form::label('description', __('Short Description'), ['class' => 'form-label']) }}
                                {!! Form::textarea('lesson_description', null, [
                                    'class' => 'form-control',
                                    'required',
                                    'placeholder' => __('Enter Description'),
                                ]) !!}
                                <p>Total Characters: <span id="count"></span></p>
                            </div>

                            <div class="form-group">
                                {{ Form::label('description', __('Long Description'), ['class' => 'form-label']) }}
                                {!! Form::textarea('long_description', null, [
                                    'class' => 'form-control',
                                    'required',
                                    'placeholder' => __('Enter Description'),
                                ]) !!}
                                <p>Total Characters: <span id="long_count"></span></p>
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
    <script src="{{ asset('vendor/ckeditor/ckeditor.js') }}"></script>
    <script>
        CKEDITOR.replace('long_description', {
            removePlugins: 'image,link,anchor,elementspath',
            filebrowserUploadUrl: "{{ route('ckeditor.upload', ['_token' => csrf_token()]) }}",
            filebrowserUploadMethod: 'form'
        });

        CKEDITOR.replace('lesson_description', {
            toolbar: [{
                    name: 'basicstyles',
                    items: ['Bold', 'Italic']
                },
                {
                    name: 'paragraph',
                    items: ['BulletedList']
                }
            ],
            removePlugins: 'image,table,link,uploadimage,elementspath',
            filebrowserUploadUrl: "{{ route('ckeditor.upload', ['_token' => csrf_token()]) }}",
            filebrowserUploadMethod: 'form'
        });

        // Handle typing
        CKEDITOR.instances.lesson_description.on('key', function(evt) {
            let editor = CKEDITOR.instances.lesson_description;
            let text = editor.document.getBody().getText();
            let maxLength = 300;

            // Allow Backspace (8) and Delete (46) even if at max length
            if (text.length >= maxLength && evt.data.keyCode !== 8 && evt.data.keyCode !== 46) {
                evt.cancel();
            }
        });

        // Handle pasting with truncation
        CKEDITOR.instances.lesson_description.on('paste', function(evt) {
            let editor = CKEDITOR.instances.lesson_description;
            let currentText = editor.document.getBody().getText();
            let pastedText = (evt.data.dataValue || '').replace(/<[^>]*>/g, ''); // Strip HTML tags
            let maxLength = 300;
            let remainingLength = maxLength - currentText.length;

            // If pasted text exceeds remaining length, truncate it
            if (remainingLength < pastedText.length) {
                pastedText = pastedText.substring(0, remainingLength);
                evt.data.dataValue = pastedText; // Update the pasted content
            }
        });

        // Update character count on change
        CKEDITOR.instances.lesson_description.on('change', function() {
            let editor = CKEDITOR.instances.lesson_description;
            let text = editor.document.getBody().getText().trim(); // Get plain text and trim
            let countElement = document.getElementById('count');
            if (countElement) {
                countElement.textContent = text.length; // Update the count display
            }
        });

        // Update character count after paste
        CKEDITOR.instances.lesson_description.on('paste', function(evt) {
            // Use setTimeout to allow paste to process before counting
            setTimeout(function() {
                let editor = CKEDITOR.instances.lesson_description;
                let text = editor.document.getBody().getText().trim(); // Get plain text and trim
                let countElement = document.getElementById('count');
                if (countElement) {
                    countElement.textContent = text.length; // Update the count display
                }
            }, 0);
        });

        // Initialize character count on editor load
        CKEDITOR.instances.lesson_description.on('instanceReady', function() {
            let editor = CKEDITOR.instances.lesson_description;
            let text = editor.document.getBody().getText().trim(); // Get plain text and trim
            let countElement = document.getElementById('count');
            if (countElement) {
                countElement.textContent = text.length; // Set initial count
            }
        });

        //Long Desc

        CKEDITOR.instances.long_description.on('change', function() {
            let editor = CKEDITOR.instances.long_description;
            let text = editor.document.getBody().getText().trim(); // Get plain text and trim
            let countElement = document.getElementById('long_count');
            if (countElement) {
                countElement.textContent = text.length; // Update the count display
            }
        });

        CKEDITOR.instances.long_description.on('instanceReady', function() {
            let editor = CKEDITOR.instances.long_description;
            let text = editor.document.getBody().getText().trim(); // Get plain text and trim
            let countElement = document.getElementById('long_count');
            if (countElement) {
                countElement.textContent = text.length;
            }
        });
    </script>
@endpush
