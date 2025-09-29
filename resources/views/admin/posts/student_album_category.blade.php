@extends('layouts.main')
@section('title', __('Album Categories'))
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('home') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item">{{ __('Album Categories') }}</li>
@endsection

@section('content')
    <div class="row">
        <div class="col-xl-12">
            <div class="card ctm-post-card">
                <div id="blog" class="sm:p-4 ">
                    <div class="dropdown dash-h-item drp-company">
                        <a class="dash-head-link dropdown-toggle arrow-none me-0" data-bs-toggle="dropdown"
                            href="javascript:void(0);" role="button" aria-haspopup="false" aria-expanded="false">
                            <span class="hide-mob ms-sm-3 text-lg">Filter</span>
                            <i class="ti ti-chevron-down drp-arrow nocolor hide-mob"></i>
                        </a>
                        <div class="dropdown-menu dash-h-dropdown">
                            <a href="{{ route('album.category.show', ['filter' => 'all']) }}"
                                class="dropdown-item {{ request()->query('filter') === 'all' ? 'active' : '' }}">
                                <span>{{ __('All') }}</span>
                            </a>
                            <a href="{{ route('album.category.show', ['filter' => 'free']) }}"
                                class="dropdown-item  {{ request()->query('filter') === 'free' ? 'active' : '' }}">
                                <span>{{ __('Free') }}</span>
                            </a>
                            <a href="{{ route('album.category.show', ['filter' => 'paid']) }}"
                                class="dropdown-item {{ request()->query('filter') === 'paid' ? 'active' : '' }}">
                                <span>{{ __('Paid') }}</span>
                            </a>
                        </div>
                    </div>

                    <div class="mt-3 mb-3 lg:mt-24">
                        <div class="infinity">
                            <div class="flex flex-wrap w-100">
                                @if ($album_categories->count() > 0)
                                    @foreach ($album_categories as $post)
                                        @php
                                            $has_purchase_album = App\Models\PurchaseAlbum::where([
                                                ['student_id', auth()->user()->id],
                                                ['album_category_id', $post->id],
                                            ])->exists();
                                        @endphp
                                        <div class="focus:outline-none w-full md:w-1/2 lg:w-1/3 py-3 p-sm-3 max-w-md">
                                            <div class="shadow rounded-2 overflow-hidden position-relative">

                                                {{-- Header (author info) --}}
                                                <div
                                                    class="p-3 position-absolute left-0 top-0 z-10 w-full {{ $post->payment_mode == 'paid' ? '' : 'custom-gradient' }}">
                                                    <div class="flex justify-between items-center w-full">
                                                        <div class="flex items-center gap-3">
                                                            <img class="w-16 h-16 rounded-full"
                                                                src="https://upload.wikimedia.org/wikipedia/commons/8/89/Portrait_Placeholder.png"
                                                                alt="Profile" />
                                                            <div>
                                                                <p class="text-xl text-white font-bold mb-0 leading-tight">
                                                                    {{ ucfirst($post->isStudentPost ? $post?->student->name : $post?->instructor?->name) }}
                                                                </p>
                                                                <span class="text-md text-white">
                                                                    {{ $post->isStudentPost ? 'Student' : 'Instructor' }}
                                                                </span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                {{-- Paid Posts --}}
                                                @if ($post->payment_mode == 'paid')
                                                    @if (!$has_purchase_album)
                                                        <div class="relative paid-post-wrap">
                                                            <img class="w-full post-thumbnail"
                                                                src="https://xn--kbenhavnercafeen-lxb.dk/wp-content/uploads/2025/03/Sourdough_Bread1.jpg"
                                                                alt="Post Image" />
                                                            <div
                                                                class="absolute inset-0 flex justify-center items-center paid-post flex-col">
                                                                <div
                                                                    class="ctm-icon-box bg-white rounded-full text-primary w-24 h-24 text-7xl flex items-center justify-content-center text-center border border-5 mb-3">
                                                                    <i class="ti ti-lock-open"></i>
                                                                </div>

                                                                {!! Form::open([
                                                                    'route' => ['album.category.purchase.album.index', ['post_id' => $post->id]],
                                                                    'method' => 'Post',
                                                                    'data-validate',
                                                                ]) !!}
                                                                <div
                                                                    class="bg-orange text-white px-4 py-1 rounded-3xl w-full text-center flex items-center justify-center gap-1">
                                                                    <i class="ti ti-lock-open text-2xl lh-sm"></i>
                                                                    {{ Form::button(__('Unlock for - $' . $post->price), ['type' => 'submit', 'class' => 'btn p-0 pl-1 text-white border-0']) }}
                                                                </div>
                                                                {!! Form::close() !!}
                                                            </div>
                                                        </div>
                                                    @else
                                                        <a href="{{ route('album.category.album', ['id' => $post->id]) }}">
                                                            <img class="w-full post-thumbnail open-full-thumbnail"
                                                                src="{{ asset('public/' . $post->image) }}"
                                                                alt="Post Image" />
                                                        </a>
                                                    @endif
                                                @else
                                                    {{-- Free Posts --}}
                                                    <a href="{{ route('album.category.album', ['id' => $post->id]) }}">
                                                        <img class="w-full post-thumbnail open-full-thumbnail"
                                                            src="{{ asset('public/' . $post->image) }}" alt="Post Image" />
                                                    </a>
                                                @endif

                                                {{-- Footer --}}
                                                <div class="px-4 py-2">
                                                    <div class="text-md italic text-gray-500">
                                                        {{ \Carbon\Carbon::parse($post->created_at)->format('d M Y') }}
                                                    </div>
                                                    @if ($has_purchase_album)
                                                        <a href="{{ route('album.category.album', ['id' => $post->id]) }}">
                                                            <h1 class="text-xl font-bold truncate">{{ $post->title }}</h1>
                                                        </a>
                                                    @else
                                                        <h1 class="text-xl font-bold truncate">{{ $post->title }}</h1>
                                                    @endif

                                                    @if (!empty($post->description))
                                                        <div class="hidden long-text text-gray-600"
                                                            style="font-size: 15px; max-height: 100px; overflow-y: auto;">
                                                            {!! $post->description !!}
                                                        </div>
                                                        <a href="javascript:void(0)"
                                                            data-long_description="{{ e($post->description) }}"
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

        {{-- Long Description Modal --}}
        <div class="modal" id="longDescModal" tabindex="-1" role="dialog">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h1 class="modal-title font-bold" style="font-size: 20px">Description</h1>
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
