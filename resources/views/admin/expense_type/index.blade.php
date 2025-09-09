
@extends('layouts.main')
@section('title', __('Expense Type'))
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('home') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item">{{ __('Expense Type') }}</li>
@endsection
@section('content')
    <div class="row">
        <div class="col-xl-12">
            <div class="card">
                <div class="card-body table-border-style">
                    <div class="table-responsive">
                        {{ $dataTable->table(['width' => '100%']) }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal" id="expenseTypeModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title font-bold" style="font-size: 20px">Add Expense Type</h1>
                    <button type="button"
                        class="bg-gray-900 flex font-bold h-8 items-center justify-center m-2 right-2 rounded-full shadow-md text-2xl top-2 w-8 z-10"
                        onclick="closeExpenseTypeModal()" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    {!! Form::open([
                        'route' => ['expense.type.store'],
                        'method' => 'Post',
                        'data-validate',
                        'files' => 'true',
                    ]) !!}
                    <div class="form-group">
                        {{ Form::label('name', __('Expense Type'), ['class' => 'form-label']) }}
                        {!! Form::text('type', null, ['class' => 'form-control', 'required', 'placeholder' => __('Enter Expense Type')]) !!}
                    </div>
                </div>
                <div class="card-footer">
                    <div class="float-end">
                        <a href="javascript:void(0)" onclick="closeExpenseTypeModal()" class="btn btn-secondary">{{ __('Cancel') }}</a>
                        {{ Form::button(__('Save'), ['type' => 'submit', 'class' => 'btn btn-primary']) }}
                    </div>
                    {!! Form::close() !!}
                </div>
            </div>
        </div>
    </div>

    <div class="modal" id="editExpenseTypeModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title font-bold" style="font-size: 20px">Edit Expense Type</h1>
                    <button type="button"
                        class="bg-gray-900 flex font-bold h-8 items-center justify-center m-2 right-2 rounded-full shadow-md text-2xl top-2 w-8 z-10"
                        onclick="closeEditExpenseTypeModal()" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    {!! Form::open([
                        'route' => ['expense.type.update'],
                        'method' => 'Post',
                        'data-validate',
                        'files' => 'true',
                    ]) !!}
                    <input type="hidden" name="id" id="id">
                    <div class="form-group">
                        {{ Form::label('name', __('Expense Type'), ['class' => 'form-label']) }}
                        {!! Form::text('type', null, ['class' => 'form-control edit_expense_type', 'required', 'placeholder' => __('Enter Expense Type')]) !!}
                    </div>
                </div>
                <div class="card-footer">
                    <div class="float-end">
                        <a href="javascript:void(0)" onclick="closeEditExpenseTypeModal()" class="btn btn-secondary">{{ __('Cancel') }}</a>
                        {{ Form::button(__('Save'), ['type' => 'submit', 'class' => 'btn btn-primary']) }}
                    </div>
                    {!! Form::close() !!}
                </div>
            </div>
        </div>
    </div>
@endsection
@push('css')
    @include('layouts.includes.datatable_css')
@endpush
@push('javascript')
    @include('layouts.includes.datatable_js')
    {{ $dataTable->scripts() }}
    <script type="text/javascript">
        $(document).ready(function() {
            var html =
                $('.dataTable-title').html(
                    "<div class='flex justify-start items-center'><div class='custom-table-header'></div><span class='font-medium text-2xl pl-4'>Expense Type</span></div>"
                );
        });

        function closeExpenseTypeModal() {
            $('#expenseTypeModal').modal('hide');
        }

        function closeEditExpenseTypeModal() {
            $('#editExpenseTypeModal').modal('hide');
        }

        function showEditExpenseTypeModal(el) {
            let id = $(el).attr('data-id');
            let type = $(el).attr('data-type');
            $('#id').val(id);
            $('.edit_expense_type').val(type);
            $('#editExpenseTypeModal').modal('show');
        }
    </script>
@endpush
