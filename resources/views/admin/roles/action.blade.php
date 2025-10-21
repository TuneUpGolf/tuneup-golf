<div class="action-btn-fix-wraper">
<a class="btn btn-sm small btn btn-info action-btn-fix" href="{{ route('roles.show', $role->id) }}" data-bs-toggle="tooltip"
    data-bs-placement="bottom" data-bs-original-title="{{ __('Permission') }}" aria-label="{{ __('Permission') }}">
    <i class="ti ti-key text-white"></i>
</a>
@can('edit-role')
    <a class="btn btn-sm small btn btn-warning action-btn-fix" href="{{ route('roles.edit', $role->id) }}" data-bs-toggle="tooltip"
        data-bs-placement="bottom" data-bs-original-title="{{ __('Edit') }}">
        <i class="ti ti-edit text-white"></i>
    </a>
@endcan
@can('delete-role')
    {!! Form::open([
        'method' => 'DELETE',
        'class' => 'd-flex',
        'route' => ['roles.destroy', $role->id],
        'id' => 'delete-form-' . $role->id,
    ]) !!}
    <a href="javascript:void(0);" class="btn btn-sm small btn btn-danger show_confirm action-btn-fix" data-bs-toggle="tooltip"
        data-bs-placement="bottom" id="delete-form-1" data-bs-original-title="{{ __('Delete') }}">
        <i class="ti ti-trash text-white"></i>
    </a>
    {!! Form::close() !!}
@endcan
</div>
