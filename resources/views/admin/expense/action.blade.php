<div class="action-btn-fix-wraper">

{{--  @can('edit-user')  --}}
    <a class="btn btn-sm small btn btn-warning action-btn-fix" data-id="{{ $expense->id }}" data-expense_type_id="{{ $expense->expenses_type_id }}" data-notes="{{ $expense->notes }}" data-amount="{{ $expense->amount }}" href="javascript:void(0);" data-bs-toggle="tooltip"
        data-bs-placement="bottom" onclick="showEditExpenseTypeModal(this)" data-bs-original-title="{{ __('Edit') }}">
        <i class="ti ti-edit text-white"></i>
    </a>
{{--  @endcan  --}}
{{--  @can('delete-user')  --}}
    {!! Form::open([
        'method' => 'DELETE',
        'class' => 'd-flex',
        'route' => ['expense.destroy', $expense->id],
        'id' => 'delete-form-' . $expense->id,
    ]) !!}
    <a href="javascript:void(0);" class="btn btn-sm small btn btn-danger show_confirm action-btn-fix" data-bs-toggle="tooltip"
        data-bs-placement="bottom" id="delete-form-1" data-bs-original-title="{{ __('Delete') }}">
        <i class="ti ti-trash text-white"></i>
    </a>
    {!! Form::close() !!}
{{--  @endcan  --}}
</div>
