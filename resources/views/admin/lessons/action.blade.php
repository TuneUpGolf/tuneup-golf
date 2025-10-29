<div class="action-btn-fix-wraper">
    @can('edit-lessons')
        <a class="btn btn-sm small btn btn-warning action-btn-fix" href="{{ route('lesson.edit', $lesson->id) }}"
            data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-original-title="{{ __('Edit') }}">
            <i class="ti ti-edit text-white"></i>
        </a>
    @endcan
    @can('delete-lessons')
        {!! Form::open([
            'method' => 'DELETE',
            'class' => 'd-flex',
            'route' => ['lesson.destroy', $lesson->id],
            'id' => 'delete-form-' . $lesson->id,
        ]) !!}
        <a href="javascript:void(0);" class="btn btn-sm small btn btn-danger show_confirmm action-btn-fix"
            data-bs-toggle="tooltip" data-bs-placement="bottom" id="delete-form-1"
            data-bs-original-title="{{ __('Delete') }}">
            <i class="ti ti-trash text-white"></i>
        </a>
        {!! Form::close() !!}
    @endcan
</div>

<script>
    $(document).ready(function() {
        $(document).on("click", ".show_confirmm", function(event) {
            var form = $(this).closest("form");
            const swalWithBootstrapButtons = Swal.mixin({
                customClass: {
                    confirmButton: "btn btn-success",
                    cancelButton: "btn btn-danger",
                },
                buttonsStyling: false,
            });
            swalWithBootstrapButtons
                .fire({
                    title: "Are you sure?",
                    text: "This action can not be undone. The slots will be deleted as well. Do you want to continue?",
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonText: "Yes",
                    cancelButtonText: "No",
                    reverseButtons: true,
                    confirmButton: "btn btn-success",
                    cancelButton: "btn btn-danger",
                })
                .then((result) => {
                    if (result.isConfirmed) {
                        form.submit();
                    }
                });
        });
    });
</script>
