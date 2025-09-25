@extends('layouts.main')
@section('title', __('Albums'))
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('home') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item">{{ __('Albums') }}</li>
@endsection

@section('content')
    <div class="row">
        <div class="col-xl-12">
            <div class="card ctm-post-card">
                <div id="blog" class="sm:p-4 ">
                    <div class="">
                        <div class="focus:outline-none mt-3 mb-3 lg:mt-24">
                            <div class="infinity">
                                <div class="flex flex-wrap w-100">
                                    @if ($albums->count() > 0)
                                        @foreach ($albums as $post)
                                            <div
                                                @if (request()->get('view') == '') class="focus:outline-none w-full md:w-1/2 lg:w-1/3 py-3 p-sm-3 max-w-md" @endif>
                                                <div class="shadow rounded-2 overflow-hidden position-relative">

                                                    <?php $cls = 'p-3 position-absolute left-0 top-0 z-10 w-full custom-gradient'; ?>
                                                    <div class="{{ $cls }}">
                                                        <div class="flex justify-between items-center w-full">
                                                            <div class="flex items-center gap-3">
                                                                <img class="w-16 h-16 rounded-full"
                                                                    src="https://upload.wikimedia.org/wikipedia/commons/8/89/Portrait_Placeholder.png"
                                                                    alt="Profile" />
                                                                <div>
                                                                    <p
                                                                        class="text-xl text-white font-bold mb-0 leading-tight">
                                                                        {{ ucfirst($post->isStudentPost ? $post?->student->name : $post?->instructor?->name) }}
                                                                    </p>
                                                                    <span class="text-md text-white">
                                                                        {{ $post->isStudentPost ? 'Student' : 'Instructor' }}
                                                                    </span>
                                                                </div>
                                                            </div>

                                                            <div class="bg-white py-2 px-3 rounded-3xl shadow">
                                                                {!! Form::open([
                                                                    'route' => ['album.category.album.like', ['post_id' => $post->id]],
                                                                    'method' => 'Post',
                                                                    'data-validate',
                                                                ]) !!}
                                                                <button type="submit"
                                                                    class="text-md font-semibold flex items-center gap-2"><i
                                                                        class="text-2xl lh-sm ti ti-heart"></i><span>
                                                                        {{ $post->likeAlbum()->count() }}
                                                                        Likes</span></button>
                                                                {!! Form::close() !!}
                                                            </div>

                                                        </div>
                                                    </div>

                                                    @if ($post->payment_mode == 'paid')
                                                        <div class="relative paid-post-wrap">
                                                            <img class=" w-full post-thumbnail"
                                                                src="https://xn--kbenhavnercafeen-lxb.dk/wp-content/uploads/2025/03/Sourdough_Bread1.jpg"
                                                                alt="Post Image" />
                                                            <div
                                                                class="absolute inset-0 flex justify-center items-center paid-post flex-col">
                                                                <div
                                                                    class="ctm-icon-box bg-white rounded-full text-primary w-24 h-24 text-7xl flex items-center justify-content-center text-center border border-5 mb-3">
                                                                    <i class="ti ti-lock-open"></i>
                                                                </div>

                                                                {!! Form::open([
                                                                    'route' => ['purchase.post.index', ['post_id' => $post->id]],
                                                                    'method' => 'Post',
                                                                    'data-validate',
                                                                ]) !!}

                                                                <div
                                                                    class="bg-orange text-white px-4 py-1 rounded-3xl w-full text-center flex items-center justify-center gap-1">
                                                                    <i class="ti ti-lock-open text-2xl lh-sm"></i>
                                                                    {{ Form::button(__('Unlock for - $' . $post->price), ['type' => 'submit', 'class' => 'btn p-0 pl-1 text-white border-0']) }}
                                                                    {!! Form::close() !!}
                                                                </div>
                                                            </div>
                                                        </div>
                                                    @else
                                                        <img class=" w-full post-thumbnail open-full-thumbnail"
                                                            src="{{ asset('/storage' . '/' . tenant('id') . '/' . $post->media) }}"
                                                            alt="Profile" />
                                                        <div id="imageModal" class="modal">
                                                            <span class="close" id="closeBtn">&times;</span>
                                                            <img class="modal-content" id="fullImage">
                                                        </div>
                                                    @endif
                                                    {{--  @if ($post->payment_mode == 'paid')
                                                            <div
                                                                class="relative bg-black h-48 flex justify-center items-center">
                                                                {!! Form::open([
                                                                    'route' => ['purchase.post.index', ['post_id' => $post->id]],
                                                                    'method' => 'Post',
                                                                    'data-validate',
                                                                ]) !!}
                                                                {{ Form::button(__('Purchase Post - $' . $post->price), ['type' => 'submit', 'class' => 'btn btn-primary bg-white text-black px-4 py-2 rounded-lg']) }}
                                                                {!! Form::close() !!}
                                                            </div>
                                                        @else
                                                            <video controls class="w-full post-thumbnail">
                                                                <source
                                                                    src="{{ Storage::url(tenant('id') . '/' . $post?->file) }}"
                                                                    type="video/mp4">
                                                            </video>
                                                        @endif  --}}

                                                    <div class="px-4 py-2">
                                                        <div class="text-md italic text-gray-500">
                                                            {{ \Carbon\Carbon::parse($post->created_at)->format('d M Y') }}
                                                        </div>
                                                        <h1 class="text-xl font-bold truncate">
                                                            {{ $post->title }}
                                                        </h1>

                                                        @php
                                                            $description = $post->description;
                                                            $shortDescription = \Illuminate\Support\Str::limit(
                                                                $description,
                                                                80,
                                                                '',
                                                            );
                                                        @endphp

                                                        @if (!empty($description))
                                                            <div class="hidden long-text text-gray-600"
                                                                style="font-size: 15px; max-height: 100px; overflow-y: auto;">
                                                                {!! $description !!}
                                                            </div>
                                                            <a href="javascript:void(0)"
                                                                data-long_description="{{ e($description) }}"
                                                                class="text-blue-600 font-medium mt-1 inline-block viewDescription"
                                                                tabindex="0">
                                                                View Description
                                                            </a>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    @else
                                        <p class="text-gray-500 text-lg mt-5">No Album Category Available.</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal" id="longDescModal" tabindex="-1" role="dialog">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h1 class="modal-title font-bold" style="font-size: 20px">Long Description</h1>
                        <button type="button"
                            class="bg-gray-900 flex font-bold h-8 items-center justify-center m-2 right-2 rounded-full shadow-md text-2xl top-2 w-8 z-10"
                            onclick="closeLongDescModal()" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="longDescContent"></div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="lesson-btn" onclick="closeLongDescModal()">Close</button>
                    </div>
                </div>
            </div>
        </div>
    @endsection

    @push('javascript')
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/2.1.4/jquery.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jscroll/2.3.7/jquery.jscroll.min.js"></script>
        <script type="text/javascript">
            $('ul.pagination').hide();
            $(function() {
                $('.infinity').jscroll({
                    autoTrigger: true,
                    debug: false,
                    loadingHtml: '<img class="center-block" src="/images/loading.gif" alt="Loading..." />',
                    padding: 0,
                    nextSelector: '.pagination li.active + li a',
                    contentSelector: '.infinity',
                    callback: function() {
                        $('ul.pagination').remove();
                    }
                });
            });

            $(document).on('click', '.viewDescription', function() {
                const desc = $(this).siblings('.long-text').html();
                $('#longDescModal').modal('show');
                $('.longDescContent').html(desc);
            })

            function closeLongDescModal() {
                $('#longDescModal').modal('hide');
            }

            const modal = document.getElementById("imageModal");
            const fullImage = document.getElementById("fullImage");
            const closeBtn = document.getElementById("closeBtn");

            document.addEventListener('click', function(event) {
                const target = event.target;
                if (target.classList.contains('open-full-thumbnail')) {
                    fullImage.src = target.src;
                    modal.style.display = "block";
                    document.body.classList.add('modal-open');
                }
            });

            closeBtn.onclick = () => {
                modal.style.display = "none";
                document.body.classList.remove('modal-open');
            };
        </script>
        @include('layouts.includes.datatable_js')
    @endpush
