<div class="action-btn-fix-wraper">
@can('edit-sms-template')
    <a class="btn btn-sm small btn-warning action-btn-fix" href="{{ route('sms-template.edit', $smsTemplate->id) }}" data-bs-toggle="tooltip"
        data-bs-placement="bottom" data-bs-original-title="{{ __('Edit') }}">
        <i class="text-white ti ti-edit"></i>
    </a>
@endcan
</div>