<div class="action-btn-fix-wraper">
<a class="btn btn-sm small btn btn-warning action-btn-fix" href="{{ route('modules.edit', $module->id) }}" data-bs-toggle="tooltip"
    data-bs-placement="bottom" data-bs-original-title="{{ __('Edit') }}">
    <i class="ti ti-edit text-white"></i>
</a>
{!! Form::open([
    'method' => 'DELETE',
    'class' => 'd-flex',
    'route' => ['modules.destroy', $module->id],
    'id' => 'delete-form-' . $module->id,
]) !!}
<a href="javascript:void(0);" class="btn btn-sm small btn btn-danger show_confirm action-btn-fix" data-bs-toggle="tooltip" data-bs-placement="bottom"
    id="delete-form-1" data-bs-original-title="{{ __('Delete') }}">
    <i class="ti ti-trash text-white"></i>
</a>
{!! Form::close() !!}
</div>