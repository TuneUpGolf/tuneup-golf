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
                        <tbody></tbody>
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
        $(document).on('click', '#preSetActionButton', function() {
            $("#preSetModal").modal('show');
            let tenant_id = $(this).attr('data-tenant_id');
            let lesson_id = $(this).attr('data-lesson_id');
            fetch(`{{ route('purchase.data') }}?tenant_id=${tenant_id}&lesson_id=${lesson_id}`)
                .then(res => res.json())
                .then(data => {
                    const tbody = document.querySelector('#preSetTable tbody');
                    tbody.innerHTML = '';

                    if (data.length === 0) {
                        tbody.innerHTML = `<tr><td colspan="3" class="text-center">No data found</td></tr>`;
                        return;
                    }

                    data.forEach(row => {
                        tbody.innerHTML += `
                    <tr>
                        <td>${row.student_name ?? ''}</td>
                        <td>${row.date_time ?? ''}</td>
                        <td>${row.location ?? ''}</td>
                    </tr>
                `;
                    });
                })
                .catch(err => console.error(err));
        })

        function closeInstructorPopup() {
            $("#preSetModal").modal('hide');
        }
       
    </script>
@endpush
