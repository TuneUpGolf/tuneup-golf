<div class="modal-content border-0 shadow-sm rounded">
    <div class="modal-header border-bottom-0 pt-4 pb-2 px-4">
        <h5 class="modal-title fw-semibold">Schedule Lesson</h5>
    </div>

    <div class="modal-body px-4">
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
            value="{{ request('lesson_date') }}" 
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
                value="{{ request('start_time') }}" 
                required>

            <label for="end_time" class="form-label fw-semibold mt-2">End Time</label>
            <input type="time" 
                id="end_time" 
                name="end_time" 
                class="form-control" 
                value="{{ request('end_time') }}" 
                required>
        </div>

        <!-- Lesson Title Dropdown -->
        <div class="mb-3">
            {{ Form::label('lesson_id', 'Lesson Title', ['class' => 'form-label fw-semibold']) }}
            <select name="lesson_id" id="lesson_id" class="form-select" required>
                <option value="">Select Lesson</option>
                @foreach($lessons as $lesson)
                    <option value="{{ $lesson->id }}" 
                        data-allows-multiple="{{ $lesson->allows_multiple_students ? 'true' : 'false' }}">
                        {{ $lesson->lesson_name }} 
                        @if($lesson->lesson_duration)
                            ({{ $lesson->lesson_duration }} hour(s))
                        @endif
                    </option>
                @endforeach
            </select>
        </div>

        <!-- Multi-Student Section (Initially Hidden) -->
        <div id="multi-student-container" style="display: none;">
            <div class="mb-3">
                <label class="form-label fw-semibold">Students</label>
                <div id="student-fields">
                    <!-- Student fields will be added dynamically -->
                </div>
                <button type="button" id="add-student" class="btn btn-outline-primary mt-2">
                    + Add Another Student
                </button>
            </div>
        </div>

        <!-- Single Student Field (Initially Visible) -->
        <!-- <div id="single-student-container">
            <div class="mb-3">
                {{ Form::label('student_id', 'Student', ['class' => 'form-label fw-semibold']) }}
                <select name="student_id" id="student_id" class="form-select" required>
                    <option value="">Select Student</option>
                    @foreach($students as $student)
                        <option value="{{ $student->id }}">{{ $student->name }}</option>
                    @endforeach
                </select>
            </div>
        </div> -->
          <div class="form-group" id="student-form">
            <div class="flex justify-start">
                <label class="mb-1"><strong>Select Students</strong></label>
            </div>
            <select name="student_id[]" id="student_id" class="form-select w-full" multiple>
                @foreach ($students as $student)
                    <option value="{{ $student->id }}">
                        {{ ucfirst($student->name) }}
                    </option>
                @endforeach
            </select>
        </div>

        <!-- Location -->
        <div class="mb-3">
            {{ Form::label('location', 'Location', ['class' => 'form-label fw-semibold']) }}
            {{ Form::text('location', null, [
                'class' => 'form-control',
                'placeholder' => 'Enter lesson location',
                'required'
            ]) }}
        </div>

        <!-- Note for Student -->
        <div class="mb-3">
            {{ Form::label('note', 'Note for Student', ['class' => 'form-label fw-semibold']) }}
            {{ Form::textarea('note', null, [
                'class' => 'form-control',
                'placeholder' => 'Enter any notes for the student...',
                'rows' => 4
            ]) }}
        </div>

        {!! Form::close() !!}
    </div>

    <!-- <div class="modal-footer border-top-0 px-4 pb-4">
        <button type="button" class="btn btn-secondary me-2" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-primary" onclick="submitScheduleLessonForm()">Book Lesson</button>
    </div> -->
</div>

<script>
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
                // Add first student field
                addStudentField();
            } else {
                multiStudentContainer.style.display = 'none';
                singleStudentContainer.style.display = 'block';
                // Clear student fields
                const studentFields = document.getElementById('student-fields');
                if (studentFields) {
                    studentFields.innerHTML = '';
                }
            }
        });
    }
});
</script>