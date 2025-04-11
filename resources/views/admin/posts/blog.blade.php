@php
    $purchasePost = \App\Models\PurchasePost::where('post_id', $post->id)
        ->where('student_id', Auth::user()->id)
        ->where('active_status', true)
        ->first();
@endphp

<div class="focus:outline-none w-1/3 mb-8 shadow:md">
    <div class="bg-white px-4 py-2 rounded-tl-3xl rounded-tr-3xl">
        <div class="flex justify-between items-center w-full">
            <div class="flex justify-between items-center gap-2">
                @if ($post->isStudentPost)
                    <img role="img" class="w-10 h-10 rounded-full"
                        src="{{ asset('/storage' . '/' . tenant('id') . '/' . $post?->student?->dp) }}" alt="games" />
                @else
                    <img role="img" class="w-10 h-10 rounded-full"
                        src="{{ asset('/storage' . '/' . tenant('id') . '/' . $post?->instructor?->logo) }}"
                        alt="games" />
                @endif
                <p class="focus:outline-none text-md text-gray-900 font-semibold mb-0 leading-none">
                    @if ($post->isStudentPost)
                        {{ ucfirst($post?->student->name) }}
                    @else
                        {{ ucfirst($post?->instructor?->name) }}
                    @endif
                    <br>
                    <span
                        class="text-xs italic text-gray-600">{{ $post->isStudentPost ? 'Student' : 'Instructor' }}</span>
                </p>
            </div>
            <div class="focus:outline-none text-md text-gray-900">
                {{ \Carbon\Carbon::parse($post->created_at)->format('d M Y') }}
            </div>
        </div>
    </div>
    @if ($post->file_type == 'image')
        @if ($post->paid && !isset($purchasePost))
            <div class="relative text-center bg-black">
                <img role="img" class="focus:outline-none rounded-md w-full h-100 opacity-10"
                    src="{{ asset('/storage' . '/' . tenant('id') . '/' . $post->file) }}" alt="games" />
                <div class="overlay-black flex justify-center items-center">
                    <div class="flex justify-center items-center">
                        {!! Form::open([
                            'route' => [
                                'purchase.post.index',
                                [
                                    'post_id' => $post->id,
                                ],
                            ],
                            'method' => 'Post',
                            'data-validate',
                        ]) !!}
                        {{ Form::button(__('Purchase Post - ' . '$' . $post->price), ['type' => 'submit', 'class' => 'btn btn-primary bg-white text-black']) }}
                        {!! Form::close() !!}
                    </div>
                </div>
            </div>
        @else
            <img role="img" class="focus:outline-none rounded-md w-full h-100"
                src="{{ asset('/storage' . '/' . tenant('id') . '/' . $post->file) }}" alt="games" />
        @endif
    @else
        @if ($post->paid && !isset($purchasePost))
            <div class="relative text-center bg-black">
                <div class="h-48">
                </div>
                <div class="overlay-black flex justify-center items-center">
                    <div class="flex justify-center items-center">
                        {!! Form::open([
                            'route' => [
                                'purchase.post.index',
                                [
                                    'post_id' => $post->id,
                                ],
                            ],
                            'method' => 'Post',
                            'data-validate',
                        ]) !!}
                        {{ Form::button(__('Purchase Post - ' . '$' . $post->price), ['type' => 'submit', 'class' => 'btn btn-primary bg-white text-black']) }}
                        {!! Form::close() !!}
                    </div>
                </div>
            </div>
        @else
            <video controls width="100%" height="100%" controls>
                <source src="{{ Storage::url(tenant('id') . '/' . $post?->file) }}" type='video/mp4'>
            </video>
        @endif
    @endif
    <div class="flex justify-between px-4 h-8 items-center w-full background-primary">
        <h1 class="focus:outline-none text-lg text-white font-bold mb-0">
            {{ $post->title }}</h1>
        <div class="flex justify-center items-center">
            {!! Form::open([
                'route' => [
                    'purchase.like',
                    [
                        'post_id' => $post->id,
                    ],
                ],
                'method' => 'Post',
                'data-validate',
            ]) !!}
            {{ Form::button(__(' ' . $post->likePost->count()), ['type' => 'submit', 'class' => 'fa fa-heart text-lg font-bold']) }}
            {!! Form::close() !!}
        </div>
    </div>
    <div class="bg-white px-4 py-2 rounded-bl-3xl rounded-br-3xl">
        <p class="focus:outline-none text-gray-700 text-sm mt-2 indent-4">
            {{ \Illuminate\Support\Str::limit(strip_tags($post?->description), 100) }}
            @if (strlen(strip_tags($post->decription)) > 50)
                ... <a href="#" class="btn btn-info btn-sm">Read
                    More</a>
            @endif
        </p>
    </div>
</div>

@push('css')
    {{-- <link rel="stylesheet" type="text/css" href="{{ asset('vendor/css/app.css') }}"> --}}
@endpush
