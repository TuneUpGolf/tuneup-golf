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

    <div class="modal modal-lg" id="preSetModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title font-bold text-lg">Pre Set Lesson Students</h1>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"
                        onclick="closeInstructorPopup()">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <table id="preSetTable" class="table table-bordered" style="width:100%">
                        <thead>
                            <tr>
                                <th>Student</th>
                                <th>Date & Time</th>
                                <th>Location</th>
                            </tr>
                        </thead>
                    </table>

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
    <script>
        function closeInstructorPopup() {
            $("#preSetModal").modal('hide');
        }
        let preSetTable;


        $(document).ready(function() {
            // lessonTypeFilter ka event
            $(document).on('change', '#lessonTypeFilter', function() {
                let val = $(this).val(); // 👈 yahan 'val' use karo, 'va' nahi
                console.log('selected:', val); // browser console me check karo

                if (val === 'pre-set') {
                    $('#preSetModal').modal('show');

                    if (!$.fn.DataTable.isDataTable('#preSetTable')) {
                        preSetTable = $('#preSetTable').DataTable({
                            processing: true,
                            serverSide: true,
                            searching: false,
                            ajax: {
                                url: '{{ route('purchase.data') }}', // <-- ye JSON route hai
                                type: 'GET',
                                data: function(d) {
                                    d.lesson_type = 'pre-set';
                                }
                            },
                            columns: [{
                                    data: 'student_name',
                                    name: 'student_name'
                                },
                                {
                                    data: 'date_time',
                                    name: 'date_time'
                                },
                                {
                                    data: 'location',
                                    name: 'location'
                                }
                            ]
                        });
                    } else {
                        preSetTable.ajax.reload();
                    }
                } else {
                    $('#purchases-table').DataTable().ajax.reload();
                }
            });
        });
    </script>
@endpush
