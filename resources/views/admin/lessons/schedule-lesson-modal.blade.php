<div class="modal-content border-0 shadow-sm rounded">
    <div class="modal-header border-bottom-0 pt-4 pb-2 px-4">
        <h5 class="modal-title fw-semibold">Schedule Lesson</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
    </div>

    <div class="modal-body px-4">
        {!! Form::open([
            'route' => 'lesson.schedule',
            'method' => 'POST',
            'id' => 'scheduleLessonForm'
        ]) !!}

        <!-- Hidden fields for time -->
        <input type="hidden" name="start_time" value="{{ request('start_time') }}">
        <input type="hidden" name="end_time" value="{{ request('end_time') }}">

        <!-- Lesson Time Display -->
        <div class="mb-3">
            <label class="form-label fw-semibold">Lesson Time</label>
            <div class="form-control bg-light">
                <strong>{{ request('start_time_display') }} - {{ request('end_time_display') }}</strong>
            </div>
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
        <div id="single-student-container">
            <div class="mb-3">
                {{ Form::label('student_id', 'Student', ['class' => 'form-label fw-semibold']) }}
                <select name="student_id" id="student_id" class="form-select" required>
                    <option value="">Select Student</option>
                    @foreach($students as $student)
                        <option value="{{ $student->id }}">{{ $student->name }}</option>
                    @endforeach
                </select>
            </div>
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

    <div class="modal-footer border-top-0 px-4 pb-4">
        <button type="button" class="btn btn-secondary me-2" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-primary" onclick="submitScheduleLessonForm()">Book Lesson</button>
    </div>
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