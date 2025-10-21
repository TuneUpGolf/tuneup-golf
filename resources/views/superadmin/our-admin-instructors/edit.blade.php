@extends('layouts.main')

@section('title', __('Edit Instructor'))

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('home') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item"><a href="{{ route('super-admin-instructors.index') }}">{{ __('Instructors') }}</a></li>
    <li class="breadcrumb-item">{{ __('Edit Instructor') }}</li>
@endsection

@section('content')
    <div class="main-content">
        <section class="section">
            <div class="m-auto col-lg-6 col-md-8 col-xxl-4">
                <div class="card">
                    <div class="card-header">
                        <h5>{{ __('Edit Instructor') }}</h5>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('super-admin-instructors.update', $instructor->id) }}" method="POST" enctype="multipart/form-data" data-validate>
                            @csrf
                            @method('PUT')
                            <div class="form-group">
                                <label for="name" class="form-label">{{ __('Instructor Name') }}</label>
                                <input type="text" name="name" id="name" class="form-control" value="{{ old('name', $instructor->name) }}">
                            </div>
                            <div class="form-group">
                                <label for="instructor_image" class="form-label">{{ __('Profile Image') }}</label>
                                <input type="file" name="instructor_image" id="instructor_image" class="form-control" accept="image/jpeg, image/png">
                                @if($instructor->instructor_image)
                                    <div class="mt-2">
                                        <img src="{{ asset('storage/app/public/'.$instructor->instructor_image) }}" class="w-20 h-20 rounded-full">
                                    </div>
                                @endif
                            </div>

                            <div class="form-group">
                                <label for="domain" class="form-label">{{ __('Domain') }}</label>
                                <input type="text" name="domain" id="domain" value="{{ old('domain', $instructor->domain) }}" class="form-control" required placeholder="{{ __('Enter domain') }}">
                            </div>

                            <div class="form-group">
                                <label for="bio" class="form-label">{{ __('Bio') }}</label>
                                <textarea name="bio" id="bio" rows="4" class="form-control" required placeholder="{{ __('Enter bio') }}">{{ old('bio', $instructor->bio) }}</textarea>
                            </div>

                            <div class="card-footer text-end">
                                <a href="{{ route('super-admin-instructors.index') }}" class="btn btn-secondary">{{ __('Cancel') }}</a>
                                <button type="submit" class="btn btn-primary">{{ __('Update') }}</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection
