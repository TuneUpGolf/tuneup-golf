<div class="d-flex justify-content-end">
    {{-- View Button --}}
    <div class="action-btn bg-light-secondary ms-2">
        <a href="{{ route('announcements.show', $announcement->id) }}" 
           class="mx-3 btn btn-sm d-inline-flex align-items-center" 
           data-bs-toggle="tooltip" data-bs-placement="top" 
           title="{{ __('View') }}">
            <i class="ti ti-eye"></i>
        </a>
    </div>

    {{-- Edit Button --}}
    @can('edit-announcements')
    <div class="action-btn bg-light-primary ms-2">
        <a href="{{ route('announcements.edit', $announcement->id) }}" 
           class="mx-3 btn btn-sm d-inline-flex align-items-center" 
           data-bs-toggle="tooltip" data-bs-placement="top" 
           title="{{ __('Edit') }}">
            <i class="ti ti-edit"></i>
        </a>
    </div>
    @endcan

    {{-- Delete Button --}}
    @can('delete-announcements')
    <div class="action-btn bg-light-danger ms-2">
        <a href="#" class="mx-3 btn btn-sm d-inline-flex align-items-center bs-pass-para show_confirm" 
           data-bs-toggle="tooltip" data-bs-placement="top" 
           title="{{ __('Delete') }}"
           data-confirm="{{ __('Are You Sure?') }}"
           data-text="{{ __('This action can not be undone. Do you want to continue?') }}"
           data-confirm-yes="delete-form-{{ $announcement->id }}">
            <i class="ti ti-trash"></i>
        </a>
        {!! Form::open([
            'method' => 'DELETE',
            'route' => ['announcements.destroy', $announcement->id],
            'id' => 'delete-form-' . $announcement->id
        ]) !!}
        {!! Form::close() !!}
    </div>
    @endcan
</div>