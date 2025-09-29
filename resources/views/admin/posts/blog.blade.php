@push('css')
    <style>
        /* Modal styling */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0, 0, 0, 0.8);
        }

        .modal-content {
            margin: 5% auto;
            display: block;
            max-width: 90%;
            max-height: 80%;
            width: auto;
            height: auto;

        }

        .close {
            position: absolute;
            top: 20px;
            right: 40px;
            color: white;
            font-size: 40px;
            font-weight: bold;
            cursor: pointer;
        }

        .close:hover {
            color: #0071ce;
        }

        body.modal-open {
            overflow-y: hidden;
        }

        body.modal-open .dash-container {
            z-index: 99999;
        }
    </style>
@endpush

<div @if (request()->get('view') == '') class="focus:outline-none w-full md:w-1/2 lg:w-1/3 py-3 p-sm-3 max-w-md" @endif>
    <div class="shadow rounded-2 overflow-hidden position-relative">
        @if ($post->paid && !isset($purchasePost))
            <?php $cls = 'p-3 position-absolute left-0 top-0 z-10 w-full'; ?>
        @else
            <?php $cls = 'p-3 position-absolute left-0 top-0 z-10 w-full custom-gradient'; ?>
        @endif
        <div class="{{ $cls }}">
            <div class="flex justify-between items-center w-full">
                <div class="flex items-center gap-3">
                    <!-- @if ($post->isStudentPost)
<img class="w-10 h-10 rounded-full"
                        src="{{ asset('/storage' . '/' . tenant('id') . '/' . $post?->student?->dp) }}" alt="Profile" />
@else
<img class="w-10 h-10 rounded-full"
                        src="{{ asset('/storage' . '/' . tenant('id') . '/' . $post?->instructor?->logo) }}"
                        alt="Profile" />
@endif -->
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

                <div class="bg-white py-2 px-3 rounded-3xl shadow">
                    {!! Form::open([
                        'route' => ['purchase.like', ['post_id' => $post->id]],
                        'method' => 'Post',
                        'data-validate',
                    ]) !!}

                    <button type="submit" class="text-md font-semibold flex items-center gap-2"><i
                            class="text-2xl lh-sm ti ti-heart"></i><span> {{ $post->likePost->count() }}
                            Likes</span></button>
                    {!! Form::close() !!}
                </div>

            </div>
        </div>

        @if ($post->file_type == 'image')
            @if ($post->paid && !isset($purchasePost))
                <div class="relative paid-post-wrap">
                    <img class=" w-full post-thumbnail"
                        src="https://xn--kbenhavnercafeen-lxb.dk/wp-content/uploads/2025/03/Sourdough_Bread1.jpg"
                        alt="Post Image" />
                    <div class="absolute inset-0 flex justify-center items-center paid-post flex-col">
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
                <!-- <img class="rounded-md w-full" src="{{ asset('/storage' . '/' . tenant('id') . '/' . $post->file) }}"
                    alt="Post Image" /> -->
                <img class=" w-full post-thumbnail open-full-thumbnail"
                    src="{{ asset('/storage' . '/' . tenant('id') . '/' . $post->file) }}" alt="Profile" />
                <div id="imageModal" class="modal">
                    <span class="close" id="closeBtn">&times;</span>
                    <img class="modal-content" id="fullImage">
                </div>
            @endif
        @else
            @if ($post->paid && !isset($purchasePost))
                <div class="relative bg-black h-48 flex justify-center items-center">
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
                    <source src="{{ Storage::url(tenant('id') . '/' . $post?->file) }}" type="video/mp4">
                </video>
            @endif
        @endif

        <div class="px-4 py-2">
            <div class="text-md italic text-gray-500">
                {{ \Carbon\Carbon::parse($post->created_at)->format('d M Y') }}
            </div>
            <h1 class="text-xl font-bold truncate">
                {{ $post->title }}
            </h1>

            @php
                $description = strip_tags($post->description);
                $shortDescription = \Illuminate\Support\Str::limit($description, 80, '');
            @endphp

            <p class="text-gray-500 text-md mt-1 description font-medium ctm-min-h">
                <span class="short-text">{{ $shortDescription }}</span>
                @if (strlen($description) > 20)
                    <span class="hidden full-text">{{ $description }}</span>
                    <a href="javascript:void(0);" class="text-blue-600 toggle-read-more font-semibold underline"
                        onclick="toggleDescription(this)">Read More</a>
                @endif
            </p>
        </div>
    </div>
</div>
@push('javascript')
    <script>
        function toggleDescription(button) {
            let parent = button.closest('.description');
            let shortText = parent.querySelector('.short-text');
            let fullText = parent.querySelector('.full-text');

            if (shortText.classList.contains('hidden')) {
                shortText.classList.remove('hidden');
                fullText.classList.add('hidden');
                button.innerText = "Read More";
            } else {
                shortText.classList.add('hidden');
                fullText.classList.remove('hidden');
                button.innerText = "Show Less";
            }
        }
    </script>
@endpush
