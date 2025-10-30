<div class="modal-content border-0 shadow-sm rounded">
    <div class="modal-header border-bottom-0 pt-4 pb-2 px-4">
        <!-- <h5 class="modal-title fw-semibold">Schedule Lesson</h5> -->
    </div>

    <div class="modal-body px-4">
        <!-- Selected Slot Info -->
        <!-- <div class="selected-slot-info mb-3 p-3 bg-light rounded" id="selectedSlotInfo" 
             style="{{ $selectedDate ? '' : 'display: none;' }}">
            <div class="d-flex align-items-center">
                <i class="bi bi-info-circle me-2"></i>
                <div>
                    <strong>Selected from Calendar</strong><br>
                    <small>Date: <span id="displayDate">{{ $selectedDate ? \Carbon\Carbon::parse($selectedDate)->format('F j, Y') : '' }}</span> | 
                    Time: <span id="displayTime">{{ $startTime24 ?? '' }} - {{ $endTime24 ?? '' }}</span></small>
                </div>
            </div>
        </div> -->

        {!! Form::open([
            'route' => 'lesson.schedule',
            'method' => 'POST',
            'id' => 'scheduleLessonForm'
        ]) !!}

        <!-- Lesson Date -->
        <div class="mb-3">
            <label for="lesson_date" class="form-label fw-semibold">Lesson Date</label>
            <input 
                type="date" 
                id="lesson_date" 
                name="lesson_date" 
                class="form-control" 
                value="{{ $selectedDate ?? old('lesson_date') }}" 
                required
            >
        </div>

        <!-- Editable Lesson Time -->
        <div class="mb-3">
            <label for="start_time" class="form-label fw-semibold">Start Time</label>
            <input type="time" 
                id="start_time" 
                name="start_time" 
                class="form-control" 
                value="{{ $startTime24 ?? old('start_time') }}" 
                required>

            <label for="end_time" class="form-label fw-semibold mt-2">End Time</label>
            <input type="time" 
                id="end_time" 
                name="end_time" 
                class="form-control" 
                value="{{ request('end_time') }}" 
                required>
        </div>

           {{-- ✅ Start on the Hour Option --}}
            <div class="mb-3">
                <div class="form-check">
                    {{ Form::checkbox('start_on_hour', '1', false, [
                        'class' => 'form-check-input',
                        'id' => 'start_on_hour'
                    ]) }}
                    {{ Form::label('start_on_hour', 'Start lessons on the hour', [
                        'class' => 'form-check-label fw-semibold'
                    ]) }}
                </div>
                <div class="start-on-hour-help">
                    When enabled, lessons will start at exact hours (8:00, 9:00, 10:00, etc.) instead of following exact availability start times.
                </div>
            </div>

        <!-- Lesson Title Dropdown -->
        <div class="mb-3">
            <label for="lesson_id" class="form-label fw-semibold">Lesson Title</label>
            <select name="lesson_id" id="lesson_id" class="form-select" required>
                <option value="">Select Lesson</option>
                @foreach($lessons as $lesson)
                    <option value="{{ $lesson->id }}" 
                        data-allows-multiple="{{ $lesson->allows_multiple_students ? 'true' : 'false' }}"
                        {{ old('lesson_id') == $lesson->id ? 'selected' : '' }}>
                        {{ $lesson->lesson_name }} 
                        @if($lesson->lesson_duration)
                            ({{ $lesson->lesson_duration }} hour(s))
                        @endif
                    </option>
                @endforeach
            </select>
        </div>

        <!-- Student Selection -->
        <div class="form-group" id="student-form">
            <div class="flex justify-start">
                <label class="mb-1"><strong>Select Students</strong></label>
            </div>
            <select name="student_id[]" id="student_id" class="form-select w-full" multiple>
                @foreach ($students as $student)
                    <option value="{{ $student->id }}" {{ in_array($student->id, old('student_id', [])) ? 'selected' : '' }}>
                        {{ ucfirst($student->name) }}
                    </option>
                @endforeach
            </select>
        </div>

        <!-- Location -->
        <div class="mb-3">
            <label for="location" class="form-label fw-semibold">Location</label>
            <input type="text" 
                name="location" 
                class="form-control" 
                placeholder="Enter lesson location" 
                value="{{ old('location') }}"
                required>
        </div>

        <!-- Note for Student -->
        <div class="mb-3">
            <label for="note" class="form-label fw-semibold">Note for Student</label>
            <textarea name="note" class="form-control" placeholder="Enter any notes for the student..." rows="4">{{ old('note') }}</textarea>
        </div>

        {!! Form::close() !!}
    </div>

   
</div>

<script>
// Function to submit the form
function submitScheduleLessonForm() {
    const form = document.getElementById('scheduleLessonForm');
    if (form.checkValidity()) {
        // Trigger the SweetAlert confirmation
        const confirmButton = document.querySelector('.swal2-confirm');
        if (confirmButton) {
            confirmButton.click();
        }
    } else {
        form.reportValidity();
    }
}

// Initialize when modal opens
document.addEventListener('DOMContentLoaded', function() {
    // Debug: Check time values
    console.log('Start Time Input Value:', document.getElementById('start_time')?.value);
    console.log('End Time Input Value:', document.getElementById('end_time')?.value);
    
    const lessonSelect = document.getElementById('lesson_id');
    if (lessonSelect) {
        lessonSelect.addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            const allowsMultiple = selectedOption.getAttribute('data-allows-multiple') === 'true';
            
            const multiStudentContainer = document.getElementById('multi-student-container');
            const singleStudentContainer = document.getElementById('single-student-container');
            
            if (allowsMultiple) {
                multiStudentContainer.style.display = 'block';
                singleStudentContainer.style.display = 'none';
            } else {
                multiStudentContainer.style.display = 'none';
                singleStudentContainer.style.display = 'block';
            }
        });
    }
    
    // Initialize Choices for student multi-select
    const studentSelect = document.getElementById('student_id');
    if (studentSelect) {
        new Choices(studentSelect, {
            removeItemButton: true,
            searchEnabled: true,
            placeholder: true,
            placeholderValue: 'Select students',
            shouldSort: false
        });
    }
    
    // Initialize Choices for lesson dropdown
    const lessonDropdown = document.getElementById('lesson_id');
    if (lessonDropdown) {
        new Choices(lessonDropdown, {
            searchEnabled: true,
            shouldSort: false
        });
    }
});
</script>