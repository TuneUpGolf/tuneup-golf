@props([
    'image' => '',
    'title' => '',
    'subtitle' => '',
    'description' => '',
    'withBackground' => false,
    'model',
    'actions' => [],
    'hasDefaultAction' => false,
    'selected' => false,
])

<div class="w-full max-w-sm bg-white border border-gray-200 rounded-lg shadow dark:bg-gray-800 dark:border-gray-700">
    <div class="relative text-center p-2">
        <img src="{{ $image }}" alt="{{ $image }}"
            class="hover:shadow-lg cursor-pointer rounded-xl h-40 w-full object-cover">
        <div class="text-bottom-img">
            <span> By: <a class="text-white"
                    href="{{ route('instructor.profile', ['instructor_id' => $model->user->id]) }}">{!! \Illuminate\Support\Str::limit(ucfirst($model->user->name), 40, '...') !!}</a>
            </span>
        </div>
    </div>
    <div class="px-3 pb-4 mt-1">
        <span class="text-xl font-semibold tracking-tight text-gray-900 dark:text-white">{!! $subtitle !!}
        </span>
        <br>
        <span class="text-xl">
            {!! $title !!}
        </span>
        <p class="font-thin text-gray-600 overflow-hidden whitespace-nowrap overflow-ellipsis">
            {!! \Illuminate\Support\Str::limit($description, 40, '...') !!}
        </p>
        <div class="flex justify-between mb-1 ">
            <div class="flex items-center space-x-1 rtl:space-x-reverse">
                Number of Lessons
            </div>
            <span class="">{!! $model->lesson_quantity !!}</span>
        </div>
        <div class="flex justify-between mt-2.5 mb-2">
            <div class="flex items-center space-x-1 rtl:space-x-reverse">
                Expected Response Time
            </div>
            <span class="">{!! $model->required_time !!}
                Days</span>
        </div>
        <div class="flex justify-between mb-1 ">
            <div class="flex items-center space-x-1 rtl:space-x-reverse">
                Purchases
            </div>

            <span class="">{!! \App\Models\Purchase::where('lesson_id', $model->id)->count() !!}</span>
        </div>
        <div class="w-100 mt-3">
            @if ($model->type === 'online')
                {!! Form::open([
                    'route' => ['purchase.store', ['lesson_id' => $model->id]],
                    'method' => 'Post',
                    'enctype' => 'multipart/form-data',
                    'class' => 'form-horizontal',
                    'data-validate',
                ]) !!}
                {{ Form::button(__('Purchase'), ['type' => 'submit', 'class' => 'lesson-btn']) }}
                {!! Form::close() !!}
            @endif
            @if ($model->type === 'inPerson')
                <div>
                    <a href="{{ route('slot.view', ['lesson_id' => $model->id]) }}">
                        <button class="lesson-btn">
                            Book Slot
                        </button>
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>
