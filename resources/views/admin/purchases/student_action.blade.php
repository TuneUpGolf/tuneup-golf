<div class="">

    @php $user = Auth::user(); @endphp
    @if (
        $purchase->status !== 'complete' &&
            $purchase->lesson->payment_method != 'cash' &&
            $user->type == 'Student'
            && $purchase->lesson->active_status == true &&
            $purchase->type != 'package')
        @can('create-purchases')
            {!! Form::open([
                'method' => 'POST',
                'class' => 'd-flex',
                'route' => ['purchase-confirm-redirect', ['purchase_id' => $purchase->id]],
                'id' => 'confirm-form-' . $purchase->id,
            ]) !!}
            {{ Form::button(__('Make Payment'), ['type' => 'submit', 'class' => 'btn btn-sm small btn btn-info action-btn-fix']) }}
             <i class="ti ti-eye text-white"></i> 
            {{-- </a> --}}
            {!! Form::close() !!}
        @endcan
    @endif
    @if (in_array($user->type, ['Student', 'Instructor']) &&
            ($purchase->status == 'complete' || $purchase->lesson->payment_method == 'cash' || $hasBooking) &&
            $purchase->lesson->type != 'online'
            && $purchase->lesson->active_status == true &&
            $purchase->lesson->type != 'inPerson')
        {{--  @if ($purchase->status == 'complete' && ($purchase->type == 'inPerson' || $purchase->type == 'package'))
                 <a href="{{ route('slot.view', ['lesson_id' => $purchase?->lesson_id]) }}" class="btn btn-primary btn-sm">
                    Change Lesson Time
                </a>
            @else  --}}
        <a class="btn btn-sm small btn btn-info action-btn-fix"
            href="{{ route('slot.view', ['lesson_id' => $purchase->lesson_id]) }}" data-bs-toggle="tooltip"
            data-bs-placement="bottom" data-bs-original-title="{{ __('Manage Slots') }}">
            <svg width="800px" height="800px" viewBox="0 0 1024 1024" class="icon" version="1.1"
                xmlns="http://www.w3.org/2000/svg">
                <path
                    d="M864 512a32 32 0 0 0-32 32v96a32 32 0 0 0 64 0v-96a32 32 0 0 0-32-32zM881.92 389.44a23.68 23.68 0 0 0-5.76-2.88 19.84 19.84 0 0 0-6.08-1.92 32 32 0 0 0-28.8 8.64A32 32 0 0 0 832 416a32 32 0 1 0 64 0 33.6 33.6 0 0 0-9.28-22.72z"
                    fill="#FFFFFF" />
                <path
                    d="M800 128h-32a96 96 0 0 0-96-96H352a96 96 0 0 0-96 96H224a96 96 0 0 0-96 93.44v677.12A96 96 0 0 0 224 992h576a96 96 0 0 0 96-93.44V736a32 32 0 0 0-64 0v162.56a32 32 0 0 1-32 29.44H224a32 32 0 0 1-32-29.44V221.44A32 32 0 0 1 224 192h32a96 96 0 0 0 96 96h320a96 96 0 0 0 96-96h32a32 32 0 0 1 32 29.44V288a32 32 0 0 0 64 0V221.44A96 96 0 0 0 800 128z m-96 64a32 32 0 0 1-32 32H352a32 32 0 0 1-32-32V128a32 32 0 0 1 32-32h320a32 32 0 0 1 32 32z"
                    fill="#FFFFFF" />
                <path
                    d="M712.32 426.56L448 721.6l-137.28-136.32A32 32 0 0 0 265.6 630.4l160 160a32 32 0 0 0 22.4 9.6 32 32 0 0 0 23.04-10.56l288-320a32 32 0 0 0-47.68-42.88z"
                    fill="#FFFFFF" />
            </svg>
        </a>
        {{--  @endif  --}}
        {{--  New COde  --}}
    @elseif(
        $user->type == 'Student' &&
            $purchase->status == 'complete' &&
            $purchase->lesson->payment_method == 'cash' &&
            $purchase->lesson->type != 'online')
        <a class="btn btn-sm small btn btn-info action-btn-fix"
            href="{{ route('slot.view', ['lesson_id' => $purchase->lesson_id]) }}" data-bs-toggle="tooltip"
            data-bs-placement="bottom" data-bs-original-title="{{ __('Manage Slots') }}">
            <svg width="800px" height="800px" viewBox="0 0 1024 1024" class="icon" version="1.1"
                xmlns="http://www.w3.org/2000/svg">
                <path
                    d="M864 512a32 32 0 0 0-32 32v96a32 32 0 0 0 64 0v-96a32 32 0 0 0-32-32zM881.92 389.44a23.68 23.68 0 0 0-5.76-2.88 19.84 19.84 0 0 0-6.08-1.92 32 32 0 0 0-28.8 8.64A32 32 0 0 0 832 416a32 32 0 1 0 64 0 33.6 33.6 0 0 0-9.28-22.72z"
                    fill="#FFFFFF" />
                <path
                    d="M800 128h-32a96 96 0 0 0-96-96H352a96 96 0 0 0-96 96H224a96 96 0 0 0-96 93.44v677.12A96 96 0 0 0 224 992h576a96 96 0 0 0 96-93.44V736a32 32 0 0 0-64 0v162.56a32 32 0 0 1-32 29.44H224a32 32 0 0 1-32-29.44V221.44A32 32 0 0 1 224 192h32a96 96 0 0 0 96 96h320a96 96 0 0 0 96-96h32a32 32 0 0 1 32 29.44V288a32 32 0 0 0 64 0V221.44A96 96 0 0 0 800 128z m-96 64a32 32 0 0 1-32 32H352a32 32 0 0 1-32-32V128a32 32 0 0 1 32-32h320a32 32 0 0 1 32 32z"
                    fill="#FFFFFF" />
                <path
                    d="M712.32 426.56L448 721.6l-137.28-136.32A32 32 0 0 0 265.6 630.4l160 160a32 32 0 0 0 22.4 9.6 32 32 0 0 0 23.04-10.56l288-320a32 32 0 0 0-47.68-42.88z"
                    fill="#FFFFFF" />
            </svg>
        </a>
    @endif
    {{--  @if ($user->type == 'Student' && $purchase->type != 'online' && $purchase->status == 'complete')
         <button type="button" data-lesson_name="{{ $purchase->lesson->lesson_name }}"
            data-instructor_name="{{ $purchase->lesson->user->name }}"
            data-slot_id="{{ $purchase->slot_id }}"
            data-start_time="{{ $purchase->slot }}"
            class="btn btn-danger btn-sm me-2 cancelBooking">
            Cancel Lesson
        </button>
        @endif  --}}
    {{-- @if ($purchase->status == 'complete' && $purchase->lesson->lesson_quantity !== $purchase->lessons_used && $user->type == 'Student' && $purchase->lesson->type === 'online')
    @can('manage-purchases')
        <a class="btn btn-sm small btn btn-warning "
            href="{{ route('purchase.video.index', ['purchase_id' => $purchase->id]) }}" data-bs-toggle="tooltip"
            data-bs-placement="bottom" data-bs-original-title="{{ __('Add Video') }}">
            <i class="ti ti-plus text-white"></i>
        </a>
    @endcan
@endif --}}
    @if ($user->type == 'Instructor')
        @can('manage-purchases')
            <a class="btn btn-sm small btn btn-warning action-btn-fix"
                href="{{ route('purchase.feedback.create', ['purchase_id' => $purchase->id]) }}" data-bs-toggle="tooltip"
                data-bs-placement="bottom" data-bs-original-title="{{ __('Provide Feedback') }}">
                <i class="ti ti-plus text-white"></i>
            </a>
        @endcan
    @endif
    @if (in_array($user->type, ['Student', 'Instructor']) &&
            $purchase->status == 'completed' &&
            ($purchaseVideo = $purchase->videos->first()))
        @can('manage-purchases')
            <a class="btn btn-sm small btn btn-warning action-btn-fix"
                href="{{ route('purchase.feedback.index', ['purchase_id' => $purchase->id]) }}" data-bs-toggle="tooltip"
                data-bs-placement="bottom" data-bs-original-title="{{ __('View Feedback') }}">
                <i class="ti ti-eye text-white"></i>
            </a>
        @endcan
    @endif




    @can('edit-purchase')
        <a class="btn btn-sm small btn btn-warning action-btn-fix" href="{{ route('purchase.edit', $purchase->id) }}"
            data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-original-title="{{ __('Edit') }}">
            <i class="ti ti-edit text-white"></i>
        </a>
    @endcan
    @can('delete-purchase')
        {!! Form::open([
            'method' => 'DELETE',
            'class' => 'd-flex',
            'route' => ['purchase.destroy', $purchase->id],
            'id' => 'delete-form-' . $purchase->id,
        ]) !!}
        <a href="javascript:void(0);" class="btn btn-sm small btn btn-danger show_confirm action-btn-fix"
            data-bs-toggle="tooltip" data-bs-placement="bottom" id="delete-form-1"
            data-bs-original-title="{{ __('Delete') }}">
            <i class="ti ti-trash text-white"></i>
        </a>
        {!! Form::close() !!}
    @endcan
</div>
<script>
    function cancelLesson(lessonId, rowIdx, buttonElement) {
    // Capture textarea value immediately before opening confirmation (DOM still exists)
    var textareaSelector = '#lessonNotes-' + rowIdx;
    var notes = $(textareaSelector).val() || ''; // Capture value now, fallback to empty string
    
    console.log('Captured notes before confirmation:', notes); // Debug log
    
    Swal.fire({
        title: 'Are you sure?',
        text: 'Do you want to cancel this lesson?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Yes, cancel it!',
        cancelButtonText: 'No, keep it'
    }).then((result) => {
        if (result.isConfirmed) {
            // Use the pre-captured notes (no DOM query needed)
            $.ajax({
                url: "{{ route('slot.update') }}",
                type: 'POST',
                data: {
                    _token: $('meta[name="csrf-token"]').attr('content'),
                    unbook: 1,
                    lessonId: lessonId, // Use lesson_id as slot_id
                    student_ids: ["{{ auth()->user()->id }}"], // Adjust if you have specific student IDs
                    redirect: 1,
                    notes: notes
                },
                success: function(response) {
                    Swal.fire('Success', 'Students unbooked successfully!', 'success');
                    showPageLoader();
                    window.location.reload();
                },
                error: function(error) {
                    Swal.fire('Error', 'There was a problem processing the request.', 'error');
                    console.log('AJAX error:', error);
                }
            });
        }
    });
}
</script>
