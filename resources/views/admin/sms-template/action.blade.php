<div class="action-btn-fix-wraper">
@can('edit-sms-template')
    <a class="btn btn-sm small btn btn-warning action-btn-fix" href="{{ route('sms-template.edit', $smsTemplate->id) }}" data-bs-toggle="tooltip"
        data-bs-placement="bottom" data-bs-original-title="{{ __('Edit') }}">
        <i class="ti ti-edit text-white"></i>
    </a>
@endcan
</div>
