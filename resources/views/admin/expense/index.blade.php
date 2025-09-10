@extends('layouts.main')
@section('title', __('Expense'))
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('home') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item">{{ __('Expense') }}</li>
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
                    <h1 class="modal-title font-bold" style="font-size: 20px">Add Expense</h1>
                    <button type="button"
                        class="bg-gray-900 flex font-bold h-8 items-center justify-center m-2 right-2 rounded-full shadow-md text-2xl top-2 w-8 z-10"
                        onclick="closeExpenseTypeModal()" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    {!! Form::open([
                        'route' => ['expense.store'],
                        'method' => 'Post',
                        'data-validate',
                        'files' => 'true',
                    ]) !!}
                    <div class="form-group">
                        {{ Form::label('expense_type', __('Expense Type'), ['class' => 'form-label']) }}
                        {!! Form::select('expense_type', $expense_types->pluck('type', 'id'), null, [
                            'class' => 'form-control',
                            'required',
                            'placeholder' => __('Select Expense Type'),
                        ]) !!}
                    </div>

                    <div class="form-group">
                        {{ Form::label('amount', __('Amount'), ['class' => 'form-label']) }}
                        {!! Form::text('amount', null, ['class' => 'form-control', 'required', 'placeholder' => __('Enter Amount')]) !!}
                    </div>

                    <div class="form-group">
                        {{ Form::label('notes', __('Notes'), ['class' => 'form-label']) }}
                        {!! Form::textarea('notes', null, ['class' => 'form-control', 'required', 'placeholder' => __('Enter Notes')]) !!}
                    </div>


                </div>
                <div class="card-footer">
                    <div class="float-end">
                        <a href="javascript:void(0)" onclick="closeExpenseTypeModal()"
                            class="btn btn-secondary">{{ __('Cancel') }}</a>
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
                    <h1 class="modal-title font-bold" style="font-size: 20px">Edit Expense</h1>
                    <button type="button"
                        class="bg-gray-900 flex font-bold h-8 items-center justify-center m-2 right-2 rounded-full shadow-md text-2xl top-2 w-8 z-10"
                        onclick="closeEditExpenseTypeModal()" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    {!! Form::open([
                        'route' => ['expense.update'],
                        'method' => 'Post',
                        'data-validate',
                        'files' => 'true',
                    ]) !!}
                    <input type="hidden" name="id" id="id">
                    <div class="form-group">
                        {{ Form::label('expense_type', __('Expense Type'), ['class' => 'form-label']) }}
                        {!! Form::select('expense_type', $expense_types->pluck('type', 'id'), null, [
                            'class' => 'form-control',
                            'required',
                            'placeholder' => __('Select Expense Type'),
                        ]) !!}
                    </div>

                     <div class="form-group">
                        {{ Form::label('amount', __('Amount'), ['class' => 'form-label']) }}
                        {!! Form::text('amount', null, ['class' => 'form-control', 'required', 'placeholder' => __('Enter Amount')]) !!}
                    </div>

                    <div class="form-group">
                        {{ Form::label('notes', __('Notes'), ['class' => 'form-label']) }}
                        {!! Form::textarea('notes', null, ['class' => 'form-control', 'required', 'placeholder' => __('Enter Notes')]) !!}
                    </div>
                </div>
                <div class="card-footer">
                    <div class="float-end">
                        <a href="javascript:void(0)" onclick="closeEditExpenseTypeModal()"
                            class="btn btn-secondary">{{ __('Cancel') }}</a>
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
            let id = $(el).data('id');
            let expense_type_id = $(el).data('expense_type_id');
            let amount = $(el).data('amount');
            let notes = $(el).data('notes');

            // set values into modal fields
            $('#id').val(id);
            $('select[name="expense_type"]').val(expense_type_id).trigger('change');
            $('input[name="amount"]').val(amount); // 👈 rename to amount if needed
            $('textarea[name="notes"]').val(notes);

            // show modal
            $('#editExpenseTypeModal').modal('show');
        }
    </script>
@endpush
