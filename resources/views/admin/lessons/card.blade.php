@props([
    'image' => '',
    'title' => '',
    'subtitle' => '',
    'short_description' => '',
    'long_description' => '',
    'withBackground' => false,
    'model',
    'packages',
    'actions' => [],
    'hasDefaultAction' => false,
    'selected' => false,
])

<div
    class="w-full max-w-sm bg-white border border-gray-200 rounded-lg shadow dark:bg-gray-800 dark:border-gray-700 flex flex-col h-full">
    <div class="relative text-center p-3 flex gap-3">
        {{-- <img src="{{ $image }}" alt="{{ $image }}"
            class="hover:shadow-lg cursor-pointer rounded-xl h-56 w-full object-cover"> --}}
        <img src="{{ $image }}" alt="{{ $image }}"
            class="hover:shadow-lg cursor-pointer rounded-lg h-32 w-24 object-cover">
        <div class="text-left">
            <a class="font-bold text-dark text-xl"
                href="{{ route('instructor.profile', ['instructor_id' => $model?->user?->id]) }}">
                {!! \Illuminate\Support\Str::limit(ucfirst($model?->user?->name), 100, '...') !!}
            </a>
            <div class="text-lg font-bold tracking-tight text-primary">
                {!! $subtitle !!}
                {{-- {!! $availableSlots > 0?"<p>$availableSlots Slots available.</p>":'' !!} --}}
            </div>
            <div class="text-sm font-medium text-gray-500 italic">
                {{-- <span class="">({!! \App\Models\Purchase::where('lesson_id', $model->id)->where('status',
                    'complete')->count() !!} Purchased)</span> --}}
                {{-- <div class="flex flex-row justify-between">
                    @if ($model->is_package_lesson && !$model->packages->isEmpty())
                        <div class="bg-green-500 text-white text-sm font-bold px-2 py-1 rounded-full">
                            Package
                            Lesson
                        </div>
                    @endif
                </div> --}}
            </div>
        </div>
    </div>

    <div class="px-3 pb-4 mt-1 flex flex-col flex-grow">

        <span class="text-xl font-semibold text-dark">{!! $title !!}</span>
        {{--  <div class="font-thin text-gray-600 overflow-hidden whitespace-nowrap overflow-ellipsis ck-content">
            {!! $short_description !!}
        </div>  --}}

        @php
            $description = str_replace(
                "\xC2\xA0",
                ' ',
                html_entity_decode(strip_tags($short_description), ENT_QUOTES | ENT_HTML5, 'UTF-8'),
            );
            $shortDescription = \Illuminate\Support\Str::limit($description, 80, '');

        @endphp

        <p class="text-gray-500 text-md mt-1 description font-medium ctm-min-h p-2">
            <span class="short-text" style="font-size:15px">{{ $shortDescription }}</span>
            @if (strlen($description) > 100)
                <span class="hidden full-text" style="font-size:15px">{{ $description }}</span>
                <a href="javascript:void(0);" style="font-size:15px"
                    class="text-blue-600 toggle-read-more font-semibold" onclick="toggleDescription(this)">View Lesson
                    Description</a>
            @endif
        </p>
        <div class="description-wrapper relative expanded mb-2">
            {{--  @if (!is_null($long_description))
                <a href="javascript:void(0)"
                    data-long_description="{{ strip_tags($long_description, '<strong><b><ul><li>') }}"
                    class=" text-blue-600 font-medium mt-1 inline-block viewDescription" tabindex="0"> View
                    Description</a>
            @endif  --}}
            @if (!empty($long_description))
                <a href="javascript:void(0)" data-long_description="{!! e($long_description) !!}"
                    class="text-blue-600 font-medium mt-1 inline-block viewDescription" tabindex="0">
                    View Description
                </a>
            @endif

        </div>
        @if ($model->type == 'package')
            <div class="mb-3 p-3 border rounded-lg shadow-sm bg-white">
                <h2 class="text-lg font-semibold flex items-center mb-2">
                    <svg class="w-5 h-5 mr-2 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-width="2"
                            d="M8 7V3m8 4V3m-9 8h10m-10 4h6m4 8H5a2 2 0 01-2-2V7a2 2 0 012-2h3l2-2h4l2 2h3a2 2 0 012 2v12a2 2 0 01-2 2z" />
                    </svg>
                    Package Options Available
                </h2>
                <p class="text-sm text-gray-500 mb-3">Save more with multi-lesson packages</p>
                <form class="space-y-3">
                    <select class="form-select" name="package_slot" id="package_slot_{{ $model->id }}">
                        <option value="0">Select Package</option>
                        @foreach ($model->packages as $package)
                            <option value="{{ $package->price }}">{!! $package->number_of_slot !!} Pack &nbsp;-&nbsp;
                                {{ $currencySymbol }} {!! $package->price !!} {{ $currency }}</option>
                        @endforeach
                    </select>
                </form>
            </div>
        @endif

        @if ($model->type == 'online')
            <div class="mt-auto bg-gray-200 gap-1 rounded-lg px-4 py-3">
                {{-- <div class="text-center w-50">
                <span class="text-xl font-bold">{!! $model->lesson_quantity !!}</span>
                <div class="text-sm rtl:space-x-reverse">Number of <br> Lessons</div>

            </div> --}}
                <div class="text-center">
                    <span class="text-xl font-bold">{!! $model->required_time !!} Days</span>
                    <div class="text-sm rtl:space-x-reverse">Expected Response <br> Time</div>

                </div>
            </div>
        @endif
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

            @if ($model->type === 'inPerson' || $model->type == 'package')
                {{-- @if ($model->is_package_lesson) --}}
                @if ($firstSlot)
                    {{-- @if ($firstSlot && !$firstSlot->isFullyBooked()) --}}
                    {{-- @php
                            $isAlreadyBooked = false;
                            if ($model->type === 'inPerson') {
                                $isAlreadyBooked = $model->slots->contains(function ($slot) {
                                    return $slot->student->contains(Auth::id());
                                });
                            }
                            if ($model->type == 'package') {
                                $isAlreadyBooked = $model->purchases->contains(function ($purchase) {
                                    return $purchase->student_id == Auth::id();
                                });
                            }
                        @endphp
                        
                        @if ($isAlreadyBooked)
                            <button class="lesson-btn opacity-50 cursor-not-allowed" disabled>
                                Already Enrolled
                            </button>
                        @else
                            <button class="lesson-btn"
                                onclick="openBookingPopup({{ json_encode($allSlots) }}, '{{ $model->type }}' ,'{{ $model->lesson_price }}', {{ $model->id}})">
                                Purchase
                            </button>
                        @endif --}}
                    {{-- @if ($isFullyBooked)
                            <a href="{{ route('slot.view', ['lesson_id' => $model->id]) }}">
                                <button class="lesson-btn">Purchase</button>
                            </a>
                        @else --}}
                    <button class="lesson-btn"
                        onclick="openBookingPopup({{ json_encode($allSlots) }}, '{{ $model->type }}', {{ $model->is_package_lesson }} ,'{{ $model->lesson_price }}', {{ $model->id }})">
                        Schedule Lesson
                    </button>
                    {{-- @endif --}}
                @else
                    <button class="lesson-btn opacity-50 cursor-not-allowed" disabled>
                        No Slots Available
                    </button>
                @endif
                {{-- @else
                    <div>
                        <a href="{{ route('slot.view', ['lesson_id' => $model->id]) }}">
                            <button class="lesson-btn">Purchase</button>
                        </a>
                    </div>
                @endif --}}
            @endif
        </div>
    </div>
    <form id="bookingForm" method="POST" action="{{ route('slot.book', ['redirect' => 1]) }}">
        @csrf
        <input type="hidden" id="packagePrice" name="package_price">
        <input type="hidden" id="slotIdInput" name="slot_id">
        <input type="hidden" id="friendNamesInput" name="friend_names">
    </form>

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
                    <h3 class="longDescContent"></h3>
                </div>
                <div class="modal-footer">
                    <button type="button" class="lesson-btn" onclick="closeLongDescModal()">Close</button>
                </div>
            </div>
        </div>
    </div>
</div>
{{-- @dump($model->is_package_lesson) --}}
@push('javascript')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        $(document).on('click', '.viewDescription', function() {
            $('#longDescModal').modal('show');
            let desc = $(this).attr('data-long_description');
            $('.longDescContent').html(desc);
        })

        function closeLongDescModal() {
            $('#longDescModal').modal('hide');
        }

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

        function openBookingPopup(allSlots, type, isPackageLesson, price, lessonId) {
            if (type === 'package') {
                price = $("#package_slot_" + lessonId).val();
                if (price == 0) {
                    alert('Please select package option!');
                    return;
                }
            }
            document.getElementById('packagePrice').value = price;

            if (isPackageLesson == 1) {
                const firstSlot = allSlots[0];
                document.getElementById('slotIdInput').value = firstSlot.id;
                document.getElementById("friendNamesInput").value = JSON.stringify([]);
                document.getElementById("bookingForm").submit();
            } else {
                if (type === 'package') {
                    price = $("#package_slot_" + lessonId).val();
                    if (price == 0) {
                        alert('Please select package option');
                        return;
                    }
                }
                if (!allSlots || allSlots.length === 0) {
                    console.error("No slots available!");
                    return;
                }
                const firstSlot = allSlots[0]; // Extract first slot dynamically
                document.getElementById('slotIdInput').value = firstSlot.id;
                document.getElementById('packagePrice').value = price;

                const availableSeats = firstSlot.lesson.max_students - firstSlot.student.length;
                const lesson = firstSlot.lesson;

                // Format All Slots' Date & Time
                let slotDetailsHtml = "";
                allSlots.forEach((s, index) => {
                    const formattedTime = new Intl.DateTimeFormat('en-US', {
                        weekday: 'long',
                        day: 'numeric',
                        month: 'short',
                        hour: '2-digit',
                        minute: '2-digit',
                        hour12: true
                    }).format(new Date(s.date_time.replace(/-/g, "/")));

                    slotDetailsHtml += `
                        <div class="slot-item">
                            <span><strong>Slot ${index + 1}:</strong> ${formattedTime}</span><br/>
                        </div>`;
                });

                Swal.fire({
                    title: "Slot Details",
                    html: `<div style="text-align: left; font-size: 14px;">
                            <span><strong>Lesson:</strong> ${lesson.lesson_name}</span><br/>
                            <span><strong>Location:</strong> ${firstSlot.location}</span><br/>
                            <span><strong>Available Spots:</strong> ${availableSeats}</span><br/>
                            <div class="slot-list">
                                <h6 class="mt-2"><strong>Slots Available:</strong></h6>
                                ${slotDetailsHtml}
                            </div>
                            <label for="studentFriends"><strong>Book for Friends (Optional):</strong></label>
                            <input type="text" id="studentFriends" class="form-control" placeholder="Enter friend names, separated by commas">
                        </div>`,
                    showCancelButton: true,
                    confirmButtonText: "Book Slot",
                    cancelButtonText: "Cancel",
                    preConfirm: () => {
                        const friendNames = document.getElementById('studentFriends')?.value.trim();
                        const friendNamesArray = friendNames ? friendNames.split(',').map(name => name.trim()) :
                            [];
                        // Ensure it's passed as an array
                        document.getElementById("friendNamesInput").value = JSON.stringify(friendNamesArray);
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        Swal.fire({
                            title: "Processing...",
                            text: "Please wait while we confirm your booking...",
                            allowOutsideClick: false,
                            didOpen: () => {
                                Swal.showLoading();
                            }
                        });
                        // Submit the hidden form
                        document.getElementById("bookingForm").submit();
                    }
                });
            }
        }
    </script>
@endpush
