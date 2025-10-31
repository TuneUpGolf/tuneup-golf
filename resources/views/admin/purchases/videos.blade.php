@extends('layouts.main')
@section('title', __('Purchase User Details'))
@section('content')
    <div class="flex flex-col md:flex-row md:items-start justify-between border-b border-gray-400 pb-4 mb-5">
    </div>

    @php
        $purchaseVideo = $purchase->videos->first();
        $purchaseVideo2Url = $purchaseVideo->video_url_2 ?? '';
    @endphp

    <div class="flex flex-col xl:flex-row gap-6 bg-white p-4 rounded-lg">
        <!-- Video Section -->
        <div class="flex flex-col lg:flex-row gap-4 w-full xl:w-2/3">
            <div class="video-wrap lg:pr-4 lg:border-r border-gray-300 flex flex-col items-center lg:items-start">
                <video controls autoplay loop muted src="{{ $purchase->videos->first()->video_url }}"
                    class="w-full sm:w-80 md:w-96 lg:w-[28rem] h-auto rounded-lg shadow"></video>

                @if (auth()->user()->type == 'Instructor')
                    <div class="flex flex-wrap justify-center lg:justify-start gap-2 mt-4">
                        <a href="{{ 'https://annotation.tuneup.golf?userid=' . Auth::user()->uuid . '&videourl=' . $purchase->videos->first()->video_url }}"
                            class="rounded-full px-4 py-2 text-white font-bold flex items-center gap-1 btn btn-danger text-sm md:text-base">
                            <i class="ti ti-search text-xl"></i> Analyze
                        </a>
                    </div>
                @endif

                @if ($purchaseVideo2Url)
                    <video controls autoplay loop muted src="{{ $purchaseVideo2Url }}"
                        class="w-full sm:w-80 md:w-96 lg:w-[28rem] h-auto rounded-lg mt-4 shadow"></video>

                    @if (auth()->user()->type == 'Instructor')
                        <div class="flex flex-wrap justify-center lg:justify-start gap-2 mt-4">
                            <a href="{{ 'https://annotation.tuneup.golf?userid=' . Auth::user()->uuid . '&videourl=' . $purchaseVideo2Url }}"
                                class="rounded-full px-4 py-2 text-white font-bold flex items-center gap-1 btn btn-danger text-sm md:text-base">
                                <i class="ti ti-search text-xl"></i> Analyze
                            </a>
                        </div>
                    @endif
                @endif

                {{-- @if (auth()->user()->type == 'Instructor')
                    <div class="flex flex-wrap justify-center lg:justify-start gap-2 mt-4">
                        <a href="{{ 'https://annotation.tuneup.golf?userid=' . Auth::user()->uuid . '&videourl=' . $purchase->videos->first()->video_url }}"
                            class="rounded-full px-4 py-2 text-white font-bold flex items-center gap-1 btn btn-danger text-sm md:text-base">
                            <i class="ti ti-search text-xl"></i> Analyze
                        </a>
                    </div>
                @endif --}}
            </div>

            <!-- Details Section -->
            <div class="mt-6 lg:mt-0 w-full lg:w-auto">
                <ul>
                    <li class="mb-3">
                        <p class="text-gray-500 text-sm">Lesson Name:</p>
                        <p class="text-lg font-semibold break-words">{{ $purchase->lesson->lesson_name }}</p>
                    </li>
                    <li class="mb-3">
                        <p class="text-gray-500 text-sm">Date Submitted:</p>
                        <p class="text-lg font-semibold">{{ $purchase->lesson->created_at }}</p>
                    </li>
                    <li class="mb-3">
                        <p class="text-gray-500 text-sm">Lesson Number:</p>
                        <p class="text-lg font-semibold">{{ $purchase->lesson->id }}</p>
                    </li>
                    <li class="mb-3">
                        <p class="text-gray-500 text-sm">Student Name:</p>
                        <p class="text-lg font-semibold">{{ $purchase->student->name }}</p>
                    </li>
                    <li class="mb-3">
                        <p class="text-gray-500 text-sm">Payment:</p>
                        <p class="text-lg font-semibold">${{ $purchase->lesson->lesson_price }}</p>
                    </li>
                    <li>
                        <p class="text-gray-500 text-sm">Payment Status:</p>
                        <div
                            class="rounded-full px-4 py-1 inline-flex items-center gap-1 bg-green-600 text-white font-semibold text-sm">
                            <i class="ti ti-check text-lg"></i>
                            <span>{{ $purchase->lesson->payment_method }}</span>
                        </div>
                    </li>
                </ul>
            </div>
        </div>

        <!-- Feedback Section -->
        <div class="feedback-sec border border-gray-300 rounded-lg p-3 w-full xl:w-1/3 mt-6 xl:mt-0">
            <h2 class="font-bold text-2xl md:text-3xl mb-3 border-b border-gray-400 pb-2">Feedback Provided</h2>
            <div>
                <p class="text-lg text-gray-700 font-bold">{{ $purchase->lesson->created_at->format('F j, Y') }}</p>
                <p class="text-gray-500">
                    {{ auth()->user()->name == $purchase->student->name ? 'Your Note' : 'Note by ' . $purchase->student->name }}:
                </p>
                <p class="text-base md:text-lg font-semibold break-words">{{ $purchaseVideo->note }}</p>

                @if ($purchaseVideo->feedback)
                    <br>
                    <p class="text-gray-500">{{ auth()->user()->type == 'Influencer' ? 'Your Feedback' : 'Feedback' }}</p>
                    <p class="text-base md:text-lg font-semibold break-words">{{ $purchaseVideo->feedback }}</p>
                @endif

                <div class="flex flex-wrap items-start gap-3 mt-4">
                    @if ($purVid = $purchase->videos->first())
                        @if ($vid = $purVid->feedbackContent->first())
                            <img class="w-32 h-20 object-cover rounded cursor-pointer border border-gray-300"
                                src="{{ asset('assets/images/video-thumbanail.jpeg') }}" alt="Thumbnail"
                                id="videoThumbnail">

                            <!-- Modal -->
                            <div id="videoModal" class="modal">
                                <span class="close">&times;</span>
                                <div class="modal-content">
                                    <video id="videoPlayer" controls>
                                        <source src="{{ $vid->url }}" type="video/mp4">
                                        Your browser does not support HTML5 video.
                                    </video>
                                </div>
                            </div>
                        @endif
                    @endif

                    @if (auth()->user()->type == 'Influencer')
                        <div class="flex flex-col sm:flex-row gap-2">
                            <a href="{{ route('purchase.feedback.create', ['purchase_video' => $purchaseVideo->video_url]) }}"
                                class="btn btn-outline-secondary rounded-full px-4 py-2 flex items-center gap-1 text-sm md:text-base">
                                @if (trim($purchaseVideo->feedback))
                                    <i class="ti ti-pencil text-xl"></i> Edit Feedback
                                @else
                                    <i class="ti ti-plus text-xl"></i> Provide Feedback
                                @endif
                            </a>
                            @if (trim($purchaseVideo->feedback))
                                <form action="{{ route('purchase.feedback.delete', $purchaseVideo->id) }}" method="POST"
                                    onsubmit="return confirm('Are you sure you want to delete this feedback?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                        class="btn btn-outline-secondary rounded-full px-4 py-2 flex items-center gap-1 text-sm md:text-base">
                                        <i class="ti ti-trash text-xl"></i> Delete
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
        /* Mobile modal optimization */
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
            width: 95%;
            max-width: 700px;
            background-color: #fff;
            border-radius: 10px;
        }

        .modal-content video {
            width: 100%;
            height: auto;
            border-radius: 10px;
        }

        .close {
            position: absolute;
            top: 10px;
            right: 20px;
            color: #fff;
            font-size: 24px;
            font-weight: bold;
            cursor: pointer;
            height: 30px;
            width: 30px;
            border-radius: 50%;
            background-color: #0071ce;
            text-align: center;
            line-height: 28px;
        }

        @media (max-width: 768px) {
            .feedback-sec {
                margin-top: 2rem;
            }
        }
    </style>
@endpush

@push('javascript')
    <script>
        const modal = document.getElementById("videoModal");
        const thumbnail = document.getElementById("videoThumbnail");
        const closeBtn = document.querySelector(".close");
        const video = document.getElementById("videoPlayer");

        if (thumbnail) {
            thumbnail.onclick = function() {
                modal.style.display = "block";
                video.play();
            }
        }

        if (closeBtn) {
            closeBtn.onclick = function() {
                modal.style.display = "none";
                video.pause();
                video.currentTime = 0;
            }
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
