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
                                {{ Form::label('name', __('Name'), ['class' => 'form-label']) }}
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
                            </div>

                            <div class="form-group">
                                {{ Form::label('description', __('Long Description'), ['class' => 'form-label']) }}
                                {!! Form::textarea('long_description', null, [
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
@endpush
@push('javascript')
    <script src="{{ asset('vendor/intl-tel-input/jquery.mask.js') }}"></script>
    <script src="{{ asset('vendor/intl-tel-input/intlTelInput-jquery.min.js') }}"></script>
    <script src="{{ asset('vendor/intl-tel-input/utils.min.js') }}"></script>
    <script src="{{ asset('vendor/ckeditor/ckeditor.js') }}"></script>
    <script>
        CKEDITOR.replace('long_description', {
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

        CKEDITOR.instances.lesson_description.on('key', function() {
            let text = CKEDITOR.instances.lesson_description.document.getBody().getText();
            if (text.length > 300) {
                CKEDITOR.instances.lesson_description.setData(text.substring(0, 100));
            }
        });
    </script>
@endpush
