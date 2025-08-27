@extends('layouts.main')
@section('title', __('Edit MyPlan'))
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('home') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item"><a href="{{ route('plans.myplan') }}">{{ __('MyPlans') }}</a></li>
    <li class="breadcrumb-item">{{ __('Edit MyPlan') }}</li>
@endsection
@section('content')
    <div class="main-content">
        <section class="section">
            <div class="page-header">
            </div>
            <div class="col-lg-6 col-md-8 col-xxl-8 m-auto">
                <div class="card">
                    <div class="card-header">
                        <h5>{{ __('Edit MyPlan') }}</h5>
                    </div>
                    <div class="card-body">
                        {!! Form::model($plan, [
                            'route' => ['plans.update', $plan->id],
                            'method' => 'put',
                            'data-validate',
                        ]) !!}
                        <div class="form-group">
                            {{ Form::label('name', __('Name'), ['class' => 'form-label']) }}
                            <div class="input-group">
                                {!! Form::text('name', null, ['placeholder' => __('Enter name'), 'class' => 'form-control', 'required']) !!}
                            </div>
                        </div>
                        <div class="form-group">
                            {{ Form::label('price', __('Price'), ['class' => 'form-label']) }}
                            {!! Form::text('price', null, ['placeholder' => __('Enter price'), 'class' => 'form-control', 'required']) !!}
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    {{ Form::label('duration', __('Duration'), ['class' => 'form-label']) }}
                                    {!! Form::number('duration', null, [
                                        'placeholder' => __('Enter duration'),
                                        'class' => 'form-control',
                                        'required',
                                    ]) !!}
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    {{ Form::label('durationtype', __('Duration'), ['class' => 'form-label']) }}
                                    {!! Form::select('durationtype', ['Month' => 'Month', 'Year' => 'Year'], $plan->durationtype, [
                                        'class' => 'form-control',
                                        'data-trigger',
                                    ]) !!}
                                </div>
                            </div>
                        </div>
                        @if (Auth::user()->type != 'Super Admin')
                            <div class="form-group">
                                {{ Form::label('max_users', __('Maximum users'), ['class' => 'form-label']) }}
                                {!! Form::number('max_users', null, [
                                    'placeholder' => __('Enter maximum users'),
                                    'class' => 'form-control',
                                    'required',
                                ]) !!}
                            </div>
                        @endif
                        <div class="form-group">
                            {{ Form::label('description', __('Description'), ['class' => 'form-label']) }}
                            {!! Form::textarea('description', null, [
                                'autocomplete' => 'off',
                                'class' => 'form-control form-control-lg form-control-solid',
                            ]) !!}
                        </div>
                        @if (Auth::user()->type == 'Influencer')
                            <div class="form-group flex flex-row gap-4">
                                <div class="flex flex-col">
                                    {{ Form::label('Chat', __('Chat *'), ['class' => 'form-label']) }}
                                    {!! Form::checkbox('chat', 1, $plan->is_chat_enabled, [
                                        'class' => 'form-check form-control',
                                        'data-onstyle' => 'primary',
                                        'data-toggle' => 'switchbutton',
                                    ]) !!}
                                </div>
                                <div class="flex flex-col">
                                    {{ Form::label('Feed', __('Feed *'), ['class' => 'form-label']) }}
                                    {!! Form::checkbox('feed', 1, $plan->is_feed_enabled, [
                                        'class' => 'form-check form-control',
                                        'data-onstyle' => 'primary',
                                        'data-toggle' => 'switchbutton',
                                    ]) !!}
                                </div>
                            </div>
                        @endif
                    </div>
                    <div class="card-footer">
                        <div class="text-end">
                            <a href="{{ route('plans.myplan') }}" class="btn btn-secondary">{{ __('Cancel') }}</a>
                            {{ Form::button(__('Save'), ['type' => 'submit', 'class' => 'btn btn-primary']) }}
                        </div>
                        {!! Form::close() !!}
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection
@push('javascript')
    <script src="{{ asset('assets/js/plugins/choices.min.js') }}"></script>
    <script src="{{ asset('vendor/ckeditor/ckeditor.js') }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var genericExamples = document.querySelectorAll('[data-trigger]');
            for (i = 0; i < genericExamples.length; ++i) {
                var element = genericExamples[i];
                new Choices(element, {
                    placeholderValue: 'This is a placeholder set in the config',
                    searchPlaceholderValue: 'This is a search placeholder',
                });
            }
        });

        CKEDITOR.replace('description', {
            allowedContent: true,
            filebrowserUploadUrl: "{{ route('ckeditor.upload', ['_token' => csrf_token()]) }}",
            filebrowserUploadMethod: 'form'
        });
    </script>
@endpush
