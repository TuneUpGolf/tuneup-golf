<div class="action-btn-fix-wraper">
@can('edit-faq')
    <a class="btn btn-sm small btn btn-warning action-btn-fix" href="{{ route('faq.edit', $faq->id) }}" data-bs-toggle="tooltip"
        data-bs-placement="bottom" data-bs-original-title="{{ __('Edit') }}">
        <i class="ti ti-edit text-white"></i>
    </a>
@endcan
@can('delete-faq')
    {!! Form::open([
        'method' => 'DELETE',
        'class' => 'd-flex',
        'route' => ['faq.destroy', $faq->id],
        'id' => 'delete-form-' . $faq->id,
    ]) !!}
    <a href="javascript:void(0);" class="btn btn-sm small btn btn-danger show_confirm action-btn-fix" data-bs-toggle="tooltip"
        data-bs-placement="bottom" id="delete-form-1" data-bs-original-title="{{ __('Delete') }}">
        <i class="ti ti-trash text-white"></i>
    </a>
    {!! Form::close() !!}
@endcan
</div>
