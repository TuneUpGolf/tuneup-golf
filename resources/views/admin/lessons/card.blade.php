@props([
    'image' => '',
    'title' => '',
    'subtitle' => '',
    'description' => '',
    'withBackground' => false,
    'model',
    'packages',
    'actions' => [],
    'hasDefaultAction' => false,
    'selected' => false,
])
@php
    $firstSlot = $model->slots->first();
    $bookedCount = $firstSlot?->student()->count();
    $availableSlots = $firstSlot?($firstSlot->lesson->max_students - (int)$bookedCount):0;
@endphp
<div
    class="w-full max-w-sm bg-white border border-gray-200 rounded-lg shadow dark:bg-gray-800 dark:border-gray-700 flex flex-col h-full">
    <div class="relative text-center p-3 flex gap-3">
        {{-- <img src="{{ $image }}" alt="{{ $image }}"
            class="hover:shadow-lg cursor-pointer rounded-xl h-56 w-full object-cover"> --}}
        <img src="{{ $image }}" alt="{{ $image }}" class="hover:shadow-lg cursor-pointer rounded-lg h-32 w-24 object-cover">
        <div class="text-left">
            <a class="font-bold text-dark text-xl"
                href="{{ route('instructor.profile', ['instructor_id' => $model?->user?->id]) }}">
                {!! \Illuminate\Support\Str::limit(ucfirst($model?->user?->name), 40, '...') !!}
            </a>
            <div class="text-lg font-bold tracking-tight text-primary">
                {!! $subtitle !!}
                {!! $availableSlots > 0?"<p>$availableSlots Slots available.</p>":'' !!}
            </div>
            <div class="text-sm font-medium text-gray-500 italic">
                {{-- <span class="">({!! \App\Models\Purchase::where('lesson_id', $model->id)->where('status',
                    'complete')->count() !!} Purchased)</span> --}}
                <div class="flex flex-row justify-between">
                    @if ($model->is_package_lesson)
                        <div class="bg-green-500 text-white text-sm font-bold px-2 py-1 rounded-full">
                            Package
                            Lesson
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="px-3 pb-4 mt-1 flex flex-col flex-grow">

        <span class="text-xl font-semibold text-dark">{!! $title !!}</span>
        <div class="font-thin text-gray-600 overflow-hidden whitespace-nowrap overflow-ellipsis ck-content">
            {!! $description !!}
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
                    <select class="form-select" name="package_slot" id="package_slot_{{ $model->id}}">
                        <option value="0">Select Package</option>
                        @foreach ($model->packages as $package)
                            <option value="{{ $package->price }}">{!! $package->number_of_slot !!} Lesson &nbsp;-&nbsp;
                                ${!! $package->price !!} CAD</option>
                        @endforeach
                    </select>
                </form>
            </div>
        @endif

        @if($model->type == 'online')
        <div class="mt-auto bg-gray-200 gap-1 rounded-lg px-4 py-3 flex">
            <div class="text-center w-50">
                <span class="text-xl font-bold">{!! $model->lesson_quantity !!}</span>
                <div class="text-sm rtl:space-x-reverse">Number of <br> Lessons</div>

            </div>
            <div class="text-center w-50">
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
                    @php
                        $allSlots = $model->slots;
                    @endphp
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
                        @if($firstSlot->isFullyBooked() || $model->payment_method == 'cash')
                            <a href="{{ route('slot.view', ['lesson_id' => $model->id]) }}">
                                <button class="lesson-btn">Purchase</button>
                            </a>
                        @else
                            <button class="lesson-btn"
                                onclick="openBookingPopup({{ json_encode($allSlots) }}, '{{ $model->type }}' ,'{{ $model->lesson_price }}', {{ $model->id}})">
                                Purchase
                            </button>
                        @endif
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
</div>

@push('javascript')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        function openBookingPopup(allSlots, type, price, lessonId) {

            if (type == 'package') {
                price = $("#package_slot_"+lessonId).val();
                if (price == 0) {
                    alert('Please select package option!');
                    return;
                }
            } else {
                price = price;
            }
            document.getElementById('packagePrice').value = price;

            //const firstSlot = allSlots[0];
            //document.getElementById('slotIdInput').value = firstSlot.id;


            document.getElementById("friendNamesInput").value = JSON.stringify([]);
            // document.getElementById("bookingForm").submit();
            if (type == 'package') {
                price = $("#package_slot_"+lessonId).val();
                if (price == 0) {
                    alert('Please select package option');
                    return;
                }
            } else {
                price = price;
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
            </div>
        `;
            });

            Swal.fire({
                title: "Slot Details",
                html: `
        <div style="text-align: left; font-size: 14px;">
            <span><strong>Lesson:</strong> ${lesson.lesson_name}</span><br/>
            <span><strong>Location:</strong> ${firstSlot.location}</span><br/>
            <span><strong>Available Spots:</strong> ${availableSeats}</span><br/>
            <div class="slot-list">
                <h6 class="mt-2"><strong>Slots Available:</strong></h6>
                ${slotDetailsHtml}
            </div>
            <label for="studentFriends"><strong>Book for Friends (Optional):</strong></label>
            <input type="text" id="studentFriends" class="form-control" placeholder="Enter friend names, separated by commas">
        </div>
        `,
                showCancelButton: true,
                confirmButtonText: "Book Slot",
                cancelButtonText: "Cancel",
                preConfirm: () => {
                    const friendNames = document.getElementById('studentFriends')?.value.trim();
                    const friendNamesArray = friendNames ? friendNames.split(',').map(name => name.trim()) : [];

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
    </script>
@endpush
