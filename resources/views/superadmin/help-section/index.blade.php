@extends('layouts.main')
@section('title', __('Help'))
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('home') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item">{{ __('Help') }}</li>
@endsection
@section('content')
    <div class="row">
        <div class="col-xl-12">
            <div class="card">
                <div class="card-body table-border-style">
                    {{-- <div class="d-flex justify-end">
                        <a class="btn btn-default buttons-create btn-light-primary no-corner me-1 add_module" tabindex="0"
                            aria-controls="users-table" href="{{ route('help-section.create') }}">
                            <span>
                                <i class="fa fa-upload"></i> Upload
                            </span>
                        </a>
                    </div> --}}
                    <h4 class="my-3">
                        Uploaded help videos, files or images.
                    </h4>
                    <hr />

                    {{-- Dummy Cards --}}
                    <div class="row g-3 mt-2">
                        @php
                            $dummyData = [
                                [
                                    'type' => 'image',
                                    'title' => 'Sample Image',
                                    'src' => 'https://ashallendesign.ams3.cdn.digitaloceanspaces.com/public/blog/72/13-placeholder-avatar-and-image-websites.png',
                                ],
                                [
                                    'type' => 'video',
                                    'title' => 'Demo Video',
                                    'src' => 'https://www.w3schools.com/html/mov_bbb.mp4',
                                ],
                                ['type' => 'file', 'title' => 'PDF File', 'src' => 'dummy.pdf'],
                                [
                                    'type' => 'image',
                                    'title' => 'Another Image',
                                    'src' => 'https://mohammadansari.gallerycdn.vsassets.io/extensions/mohammadansari/dummyimage/0.0.1/1688371999089/Microsoft.VisualStudio.Services.Icons.Default',
                                ],
                            ];
                        @endphp

                        @foreach ($dummyData as $item)
                            <div class="col-md-4 col-sm-6 mb-3">
                                <div class="card shadow-sm "
                                    style="display: flex; flex-direction: column;">
                                    <div
                                        class="card-body text-center d-flex flex-column justify-content-between overflow-hidden" style="height: 229px;min-height: 229px;">

                                        {{-- Media Preview --}}
                                        <div class="mb-2">
                                            @if ($item['type'] === 'image')
                                                <img src="{{ $item['src'] }}" class="img-fluid rounded"
                                                    alt="{{ $item['title'] }}"
                                                    style="max-height: 150px; object-fit: cover; width: 100%;">
                                            @elseif($item['type'] === 'video')
                                                <video class="w-100 rounded" style="max-height: 150px; object-fit: cover;"
                                                    controls>
                                                    <source src="{{ $item['src'] }}" type="video/mp4">
                                                    Your browser does not support the video tag.
                                                </video>
                                            @elseif($item['type'] === 'file')
                                                <i class="fa fa-file fa-5x text-danger"></i>
                                            @endif
                                        </div>

                                        {{-- Title --}}
                                        

                                        {{-- Buttons --}}
                                        <div class="d-flex justify-content-center gap-2 mt-2">
                                            <a href="{{ $item['src'] }}" target="_blank" class="btn btn-sm btn-primary">
                                                <i class="fa fa-eye"></i> View
                                            </a>
                                            <button class="btn btn-sm btn-danger">
                                                <i class="fa fa-trash"></i> Delete
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    {{-- Dummy Pagination --}}
                    <div class="d-flex justify-content-end mt-4">
                        <nav>
                            <ul class="pagination">
                                <li class="page-item disabled"><a class="page-link" href="#">Previous</a></li>
                                <li class="page-item active"><a class="page-link" href="#">1</a></li>
                                <li class="page-item"><a class="page-link" href="#">2</a></li>
                                <li class="page-item"><a class="page-link" href="#">3</a></li>
                                <li class="page-item"><a class="page-link" href="#">Next</a></li>
                            </ul>
                        </nav>
                    </div>

                </div>
            </div>
        </div>
    </div>
@endsection
