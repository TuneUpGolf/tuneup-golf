@extends('layouts.main')

@section('title', __('Create Instructor'))

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('home') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item"><a href="{{ route('super-admin-instructors.index') }}">{{ __('Instructors') }}</a></li>
    <li class="breadcrumb-item">{{ __('Create Instructor') }}</li>
@endsection

@section('content')
    <div class="main-content">
        <section class="section">
            <div class="col-sm-12 col-md-8 m-auto">
                <div class="card">
                    <div class="card-header">
                        <h5>{{ __('Create Instructor') }}</h5>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('super-admin-instructors.store') }}" method="POST" enctype="multipart/form-data" data-validate>
                            @csrf
                            <div class="row">
                                <div class="mb-3">
                                    <label for="name" class="form-label">Instructor Name</label>
                                    <input type="text" name="name" id="name" 
                                        class="form-control" 
                                        value="{{ old('name', $superAdminInstructor->name ?? '') }}" 
                                        required>
                                </div>
                                <div class="col-xl-6">
                                    <div class="form-group">
                                        <label for="instructor_image" class="form-label">{{ __('Profile Image') }}</label>
                                        <input type="file" name="instructor_image" id="instructor_image" class="form-control" accept="image/jpeg, image/png" required>
                                    </div>
                                </div>
                                <div class="col-xl-6">
                                    <div class="form-group">
                                        <label for="domain" class="form-label">{{ __('Domain') }}</label>
                                        <input type="text" name="domain" id="domain" class="form-control" required placeholder="{{ __('Enter domain') }}">
                                    </div>
                                </div>
                                <div class="col-xl-12">
                                    <div class="form-group">
                                        <label for="bio" class="form-label">{{ __('Bio') }}</label>
                                        <textarea name="bio" id="bio" rows="4" class="form-control" required placeholder="{{ __('Enter bio') }}"></textarea>
                                    </div>
                                </div>
                            </div>
                            <div class="card-footer">
                                <div class="float-end">
                                    <a href="{{ route('super-admin-instructors.index') }}" class="btn btn-secondary">{{ __('Cancel') }}</a>
                                    <button type="submit" class="btn btn-primary">{{ __('Save') }}</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection
