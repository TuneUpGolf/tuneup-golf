@extends('layouts.main')
@section('title', __('Purchases'))
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('home') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item">{{ __('Purchases') }}</li>
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
    {{--  <div class="modal fade" id="preSetModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header" style="justify-content: end">
                    <button onclick="closeInstructorPopup()"
                        class="absolute bg-gray-900 flex font-bold h-8 items-center justify-center m-2 right-2 rounded-full shadow-md text-2xl top-2 w-8 z-10">
                        ×
                    </button>
                </div>
                <div class="modal-body">
                    Pre-set lesson type selected. No filtering applied.
                </div>
                <div class="modal-footer">
                    <button type="button" class="lesson-btn" onclick="closeLongDescModal()">Close</button>
y
                </div>
            </div>
        </div>
    </div>  --}}

    <div class="modal" id="preSetModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title font-bold" style="font-size: 20px">Pre Set Lesson Students</h1>
                    <button type="button"
                        class="bg-gray-900 flex font-bold h-8 items-center justify-center m-2 right-2 rounded-full shadow-md text-2xl top-2 w-8 z-10"
                        onclick="closeInstructorPopup()" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <h3 class="longDescContent"></h3>
                </div>
                <div class="modal-footer">
                    <button type="button" class="lesson-btn" onclick="closeInstructorPopup()">Close</button>
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
            $('.dataTable-title').html(
                "<div class='flex justify-start items-center'><div class='custom-table-header'></div><span class='font-medium text-2xl pl-4'>All Purchases</span></div>"
            );
        });

        function closeInstructorPopup() {
            $("#preSetModal").modal('hide');
        }
    </script>
@endpush
