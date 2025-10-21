@extends('layouts.main-landing')

@section('content')
    <!-- ✅ Top Navigation -->
    <header>
        <nav class="navbar navbar-expand-lg navbar-dark bg-white px-0 py-3">
            <div class="container-xl">
                <a class="navbar-brand" href="/">
                    <img src="{{ asset('assets/images/landing-page-images/logo-1.png') }}" class="h-8" alt="...">
                </a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarCollapse"
                    aria-controls="navbarCollapse" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon">
                        <img src="{{ asset('assets/images/landing-page-images/icon-1.png') }}" class="navbar-toggler-icon"
                            alt="...">
                    </span>
                </button>
                <div class="collapse navbar-collapse" id="navbarCollapse">
                    <div class="navbar-nav mx-lg-auto" id="mynav">
                        <a class="nav-item nav-link item-custom anker" href="/">Why TuneUp?</a>
                        <a class="nav-item nav-link item-custom anker" href="./Course.html">Golf Course Benefits</a>
                        <a class="nav-item nav-link item-custom anker" href="./Instructor.html">Instructors</a>
                        <a class="nav-item nav-link item-custom anker" href="./Golfer.html">Golfers</a>
                        <a class="nav-item nav-link item-custom anker" href="./About.html">About Us</a>
                        <a class="nav-item nav-link item-custom anker active" href="{{ url('our-instructors') }}">Our Instructors</a>
                    </div>

                    <div class="d-flex align-items-lg-center mt-3 mt-lg-0">
                        <a class="request-text border-0 rounded-pill demo text-white"
                           href="{{ url('request-demo') }}"
                           style="background-color: #0033a1; text-decoration: none">Request a Demo</a>
                        <a class="request-text border-0 rounded-pill demo mx-2 text-white"
                           href="{{ route('login') }}"
                           style="background-color: #0033a1; text-decoration: none">Login</a>
                    </div>
                </div>
            </div>
        </nav>
    </header>

    <!-- ✅ Instructors Section -->
    <section class="container">
        <div class="container ctm-container">
            <div class="flex flex-wrap gap-5 w-full my-10">
                <h2 class="flex flex-col items-center text-center">
                    {{ $instructor_heading ?? 'Our Instructors' }}
                </h2>

                @if (!$instructors->isEmpty())
                    <div class="row justify-center">
                        @foreach ($instructors as $instructor)
                            @php
                                $imgSrc = $instructor->instructor_image
                                    ? asset('storage/app/public/' . $instructor->instructor_image)
                                    : asset("assets/images/default-user.png");
                            @endphp

                            <div class="col-md-3 col-sm-6 mb-4 d-flex flex-column align-items-center text-center">
                                <a href="{{ $instructor->domain }}" target="_blank" rel="noopener noreferrer">
                                    <img class="custom-instructor-avatar rounded img-fluid"
                                        src="{{ $imgSrc }}"
                                        alt="Instructor Image">
                                </a>
                                <h5 class="mt-3 font-semibold text-lg">{{ $instructor->name }}</h5>

                                <!-- View Bio link -->
                                <a href="javascript:void(0);"
                                class="text-blue-600 underline text-sm mt-1"
                                data-bs-toggle="modal"
                                data-bs-target="#bioModal{{ $instructor->id }}">
                                    View Bio
                                </a>
                            </div>

                            <!-- Modal -->
                            <div class="modal fade" id="bioModal{{ $instructor->id }}" tabindex="-1"
                                aria-labelledby="bioModalLabel{{ $instructor->id }}" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="bioModalLabel{{ $instructor->id }}">
                                                {{ $instructor->name }}
                                            </h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            {{ $instructor->bio }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-center text-gray-600">No instructors found.</p>
                @endif

            </div>
        </div>
    </section>
@endsection
