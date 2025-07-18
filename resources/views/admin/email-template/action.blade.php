<div class="action-btn-fix-wraper">
@can('edit-email-template')
    <a class="btn btn-sm small btn btn-warning action-btn-fix" href="{{ route('email-template.edit', $row->id) }}" data-bs-toggle="tooltip"
        data-bs-placement="bottom" data-bs-original-title="{{ __('Edit') }}">
        <i class="ti ti-edit text-white"></i>
    </a>
@endcan
