@extends('layouts.main')
@section('title', __('Import Instructors'))
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('home') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item"><a href="{{ route('instructor.index') }}">{{ __('Instructors') }}</a></li>
    <li class="breadcrumb-item">{{ __('Import Instructors') }}</li>
@endsection
@section('content')
    <div class="main-content">
        <section class="section">
            <div class="m-auto col-lg-6 col-md-8 col-xxl-4">
                <div class="card">
                    <div class="card-header">
                        <h5>{{ __('Import Instructors') }}</h5>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('instructor.import_instructors') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <div class="form-group">
                                <label for="file" class="form-label">{{ __('Select CSV File') }}</label>
                                <input type="file" name="file" class="form-control" required>
                            </div>

                    </div>
                    <div class="card-footer">
                        <div class="float-end">
                            <a href="{{ route('instructor.index') }}" class="btn btn-secondary">{{ __('Cancel') }}</a>
                            {{ Form::button(__('Import'), ['type' => 'submit', 'class' => 'btn btn-primary']) }}
                        </div>
                    </div>
                    </form>
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

@endpush
