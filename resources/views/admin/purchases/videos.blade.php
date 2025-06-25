@extends('layouts.main')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('home') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item"><a href="{{ route('purchase.index') }}">{{ __('Purchase') }}</a></li>
    <li class="breadcrumb-item">{{ __('Videos') }}</li>
@endsection
@section('content')
<div
    class="flex items-start justify-content-between border-b border-gray-400 pb-4 mb-5">
    <div class="max-w-lg">
        <h2 class="font-bold text-3xl mb-3">Purchase User Details</h2>
    </div>
</div>
@php
$purchaseVideo = $purchase->videos->first();
$purchaseVideoUrl = $purchaseVideo->video_url??'';
$feedback = $purchaseVideo?trim($purchaseVideo->feedback):false;
$feedbackContent = $purchaseVideo->feedbackContent->first();
$feedbackUrl = $feedbackContent->url??false;
@endphp
<div class="flex justify-content-between items-start bg-white p-4 rounded-lg">
    <div class="video-section-col flex gap-4">
        @if($purchaseVideoUrl)
        <div class="video-wrap border-r border-gray-400 pr-4">
            <video width='320' height='240' controls autoplay="autoplay" loop muted src="{{ $purchaseVideoUrl }}" class="w-80 h-60 rounded-lg"></video>
            @if(auth()->user()->type == 'Instructor')
            <div class="flex gap-1 mt-3">

                <a href="{{ route('purchase.feedback.create', ['purchase_video' => $purchaseVideoUrl]) }}"
                    class="rounded-pill px-4 py-2 w-auto text-white font-bold flex itmes-center gap-1  btn btn-warning">
                    <i class="ti ti-notebook text-2xl"></i>
                    Feedback
                </a>
                <a href="{{ 'https://annotation.tuneup.golf?userid=' . Auth::user()->uuid . '&videourl='  . $purchaseVideoUrl }}"
                    class="rounded-pill px-4 py-2 w-auto text-white font-bold flex itmes-center gap-1 btn btn-danger ">
                    <i class="ti ti-search text-2xl"></i>
                    Analyze
                </a>
            </div>
            @endif
        </div>
        @endif
        <div>
            <ul>
                <li class="mb-4">
                    <p class="text-gray-500">Lesson Name:</p>
                    <p class="text-xl font-semibold">{{ $purchase->lesson->lesson_name }}</p>
                </li>
                <li class="mb-4">
                    <p class="text-gray-500">Created At</p>
                    <p class="text-xl font-semibold">{{ $purchase->lesson->created_at }}</p>
                </li>
                <li class="mb-4">
                    <p class="text-gray-500">Lesson Number:</p>
                    <p class="text-xl font-semibold">{{ $purchase->lesson->id }}</p>
                </li>
                <li class="mb-4">
                    <p class="text-gray-500">Student Name:</p>
                    <p class="text-xl font-semibold">{{ $purchase->student->name }}</p>
                </li>
                <li class="mb-4">
                    <p class="text-gray-500">Payment</p>
                    <p class="text-xl font-semibold">${{ $purchase->lesson->lesson_price }}</p>
                </li>
                <li>
                    <p class="text-gray-500">Payment Status</p>
                    <div
                        class="rounded-pill px-4 py-2 w-auto text-white text-md font-bold inline-flex itmes-center gap-1 btn btn-success ">
                        <i class="ti ti-check text-2xl"></i>
                        {{ $purchase->lesson->payment_method }}
                    </div>
                </li>
            </ul>
        </div>

    </div>
    <div class="feedback-sec border border-gray rounded-lg p-3 w-full">
        <h2 class="font-bold text-3xl mb-3 border-b border-gray-500 mb-3 pb-2">Feedback</h2>
        <div class="">
            <p class="text-2xl text-gray-700 font-bold">{{ $purchase->lesson->created_at->format('F j, Y') }}</p>
            @if($purchaseVideo->note??false)
                <p class="text-gray-500">{{ auth()->user()->name == $purchase->student->name?'Your Note':'Note by '.$purchase->student->name }}:</p>
                <p class="text-xl font-semibold">{{ $purchaseVideo->note }}</p>
            @endif

            @if($purchaseVideo->feedback??false)
            <br>
            <p class="text-gray-500">{{ auth()->user()->type == 'Instructor'?'Your feedback':'Feedback' }}</p>
            <p class="text-xl font-semibold">{{ $purchaseVideo->feedback }}</p>
            @endif

            <div class="flex items-start gap-3 mt-4">
                @if($feedbackContent->type != 'video' && $feedbackUrl)
                    <a href="{{ asset('storage/'.tenant()->id.'/'.$feedbackUrl) }}">View feedback content</a>
                 @else
                    <img class="w-15 h-10"  src="{{ asset('/assets/images/video-thumbanail.jpeg') }}" alt="Thumbnail" id="videoThumbnail">
                @endif

                @if($feedbackUrl)
                <!-- Modal -->
                <div id="videoModal" class="modal">
                <span class="close">&times;</span>
                    <div class="modal-content">
                        <video id="videoPlayer" controls>
                            <source src="{{ asset('storage/'.tenant()->id.'/'.$feedbackUrl) }}" type="video/mp4">
                            Your browser does not support HTML5 video.
                        </video>
                    </div>
                </div>
                @endif
                
                @if(auth()->user()->type == 'Instructor')
                <div class="flex gap-2">
                    <a href="{{ route('purchase.feedback.create', ['purchase_id' => $purchase->id]) }}"
                        class="btn btn-outline-secondary rounded-pill px-4 py-2 d-flex align-items-center gap-1">
                        @if($feedback)
                        <i class="ti ti-pencil text-2xl"></i> Edit Feedback
                        @else
                        <i class="ti ti-plus text-2xl"></i> Provide Feedback
                        @endif
                    </a>
                    @if($feedback)
                    <form action="{{ route('purchase.feedback.delete', $purchaseVideo->id) }}" method="POST"
                        onsubmit="return confirm('Are you sure you want to delete this feedback?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit"
                            class="btn btn-outline-secondary rounded-pill px-4 py-2 d-flex align-items-center gap-1">
                            <i class="ti ti-trash text-2xl"></i> Delete
                        </button>
                    </form>
                    @endif
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

@endsection

@push('css')
<style>
    #videoThumbnail {
        cursor: pointer;
        
    }

    .modal {
        display: none;
        position: fixed;
        z-index: 99999;
        padding-top: 60px;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        overflow: auto;
        background-color: rgba(0, 0, 0, 0.7);
    }

    .modal-content {
        position: relative;
        margin: auto;
        padding: 0;
        width: 90%;
        max-width: 100%;
        background-color: #fff;
        border-radius: 10px;
        max-height: calc(100vh - 200px);
        overflow: hidden;
    }

    .modal-content video {
        width: 100%;
        height: auto;
    }

    .close {
        position: absolute;
        top: 10px;
        right: 20px;
        color: #fff;
        font-size: 28px;
        font-weight: bold;
        cursor: pointer;
        height: 30px;
        width: 30px;
        border-radius: 100px;
        background-color: #0071ce;
        text-align: center;
        line-height: 30px;
    }

    .close:hover {
        color: #000;
    }
</style>
@endpush

@push('javascript')
<script>
    const modal = document.getElementById("videoModal");
    const thumbnail = document.getElementById("videoThumbnail");
    const closeBtn = document.querySelector(".close");
    const video = document.getElementById("videoPlayer");

    thumbnail.onclick = function() {
        modal.style.display = "block";
        video.play();
    }

    closeBtn.onclick = function() {
        modal.style.display = "none";
        video.pause();
        video.currentTime = 0;
    }

    window.onclick = function(event) {
        if (event.target === modal) {
            modal.style.display = "none";
            video.pause();
            video.currentTime = 0;
        }
    }
</script>
@endpush