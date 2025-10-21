@if ($requestdomain->status == 0)
<div class="action-btn-fix-wraper">
    {!! Form::open([
        'method' => 'POST',
        'class' => 'd-flex',
        'route' => ['change.domain.approve', $requestdomain->id],
        'id' => 'delete-form-' . $requestdomain->id,
    ]) !!}
    <a class="btn btn-sm small btn btn-success show_confirm action-btn-fix" data-bs-toggle="tooltip" data-bs-placement="bottom"
        data-bs-original-title="{{ __('Approved') }}">
        <i class="ti ti-access-point text-white"></i>
    </a>
    {!! Form::close() !!}
    <a class="btn btn-sm small btn btn-danger reason action-btn-fix" data-url="/change-domain/disapprove/{{ $requestdomain->id }}"
        href="javascript:void(0)" data-bs-toggle="tooltip" data-bs-placement="bottom"
        data-bs-original-title="{{ __('Disapproved') }}">
        <i class="ti ti-access-point-off text-white"></i>
    </a>
</div>
@endif
