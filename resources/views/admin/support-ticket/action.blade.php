<div class="action-btn-fix-wraper">
@can('edit-support-ticket')
    <a class="btn btn-sm small btn btn-warning action-btn-fix" href="{{ route('support-ticket.edit', $supportTicket->id) }}" data-bs-toggle="tooltip"
        data-bs-placement="bottom" data-bs-original-title="{{ __('Edit') }}">
        <i class="ti ti-corner-up-left text-white"></i>
    </a>
@endcan
@can('delete-support-ticket')
    {!! Form::open([
        'method' => 'DELETE',
        'class' => 'd-flex',
        'route' => ['support-ticket.destroy', $supportTicket->id],
        'id' => 'delete-form-' . $supportTicket->id,
    ]) !!}
    <a href="javascript:void(0);" class="btn btn-sm small btn btn-danger show_confirm action-btn-fix" data-bs-toggle="tooltip"
        data-bs-placement="bottom" id="delete-form-1" data-bs-original-title="{{ __('Delete') }}">
        <i class="ti ti-trash text-white"></i>
    </a>
    {!! Form::close() !!}
@endcan
</div>
