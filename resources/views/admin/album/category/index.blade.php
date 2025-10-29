@extends('layouts.main')
@section('title', __('Create Category'))
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('home') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item">{{ __('Create Category') }}</li>
@endsection
@section('content')
    <div class="row">
        <div class="col-xl-12">
            <div class="card">
                <div class="card-body table-border-style">
                    <div class="table-responsive">
                        <table class="table table-bordered data-table w-100">
                            <thead>
                                <tr>
                                    <th id="icon12"></th> <!-- ✅ Responsive control column -->
                                    <th></th> <!-- ✅ Reorder handle column -->
                                    <th>Title</th>
                                    <th>Paid</th>
                                    <th>Price ($)</th>
                                    <th>Sales</th>
                                    <th>Photo</th>
                                    <th>Created At</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@push('css')
    @include('layouts.includes.datatable_css')
    <link rel="stylesheet" href="https://cdn.datatables.net/rowreorder/1.4.1/css/rowReorder.dataTables.min.css">
    <style>
        @media (max-width: 768px) {
            .card-body {
                max-height: 100%;
            }
        }

        .drag-handle {
            cursor: grab;
            font-size: 18px;
            color: #6c757d;
            user-select: none;
            transition: color 0.2s ease, transform 0.2s ease;
        }

        .drag-handle:active {
            cursor: grabbing;
            color: #000;
            transform: scale(1.2);
        }

        tbody tr {
            transition: transform 0.25s ease, background-color 0.25s ease;
        }

        tbody tr.dt-rowReorder-moving {
            background-color: #f1f3f5 !important;
        }
    </style>
@endpush
@push('javascript')
    @include('layouts.includes.datatable_js')
    <script src="https://cdn.datatables.net/rowreorder/1.4.1/js/dataTables.rowReorder.min.js"></script>

    <script type="text/javascript">
        $(document).ready(function() {
            var html =
                $('.dataTable-title').html(
                    "<div class='flex justify-start items-center'><div class='custom-table-header'></div><span class='font-medium text-2xl pl-4'>Create Category</span></div>"
                );
            $(function() {
                let createUrl = "{{ route('album.category.create') }}";
                let table = $('.data-table').DataTable({
                    processing: true,
                    serverSide: true,
                    ajax: "{{ route('album.category.manage') }}",
                    columns: [{
                            className: 'dt-control',
                            orderable: false,
                            data: null,
                            defaultContent: ''
                        },
                        {
                            data: null,
                            className: 'reorder-handle text-center',
                            orderable: false,
                            searchable: false,
                            render: () =>
                                '<span class="drag-handle" style="cursor: grab;">⋮⋮</span>'
                        },
                        // {
                        //     data: 'DT_RowIndex',
                        //     name: 'DT_RowIndex',
                        //     orderable: false,
                        //     searchable: false
                        // },
                        {
                            data: 'title',
                            name: 'title',
                        },
                        {
                            data: 'paid',
                            name: 'paid'
                        },
                        {
                            data: 'price',
                            name: 'price'
                        },
                        {
                            data: 'sales',
                            name: 'sales'
                        },
                        {
                            data: 'photo',
                            name: 'photo'
                        },
                        {
                            data: 'created_at',
                            name: 'created_at',
                            orderable: false
                        },
                        {
                            data: 'action',
                            name: 'action'
                        },

                    ],
                    rowReorder: {
                        selector: 'td.reorder-handle',
                        update: false
                    },

                    responsive: {
                        details: {
                            display: $.fn.dataTable.Responsive.display.childRow,
                            renderer: function(api, rowIdx, columns) {
                                let data = $('<table/>').addClass('vertical-table');
                                console.log(columns);
                                $.each(columns, function(i, col) {
                                    if (i === 0 || i === 1 || i === 2)
                                        return; // skip first two columns
                                    data.append(
                                        '<tr>' +
                                        '<td><strong>' + col.title +
                                        '</strong></td>' +
                                        '<td>' + (col.data !== null ? col.data :
                                            '') + '</td>' +
                                        '</tr>'
                                    );
                                });
                                return data;
                            }
                        }
                    },

                    dom: "<'dataTable-top row'<'dataTable-title col-lg-3 col-sm-12'<'custom-title'>>" +
                        "<'dataTable-botton table-btn col-lg-6 col-sm-12'B>" +
                        "<'dataTable-search tb-search col-lg-3 col-sm-12'f>>" +
                        "<'dataTable-container'<'col-sm-12'tr>>",
                    // "<'dataTable-bottom row'<'dataTable-dropdown page-dropdown col-lg-2 col-sm-12'l><'col-sm-7'p>>",
                    buttons: [{
                        text: '<i class="fa fa-plus" aria-hidden="true"></i> Create',
                        className: 'btn btn-light-primary no-corner me-1 add_module',
                        action: function() {
                            window.location.href = createUrl;
                        }
                    }],

                    initComplete: function() {
                        var table = this;
                        var tableContainer = $(table.api().table().container());

                        // Customize search input
                        var searchInput = $('#' + table.api().table().container().id +
                            ' label input[type="search"]');
                        searchInput.removeClass('form-control form-control-sm').addClass(
                            'dataTable-input');

                        // Customize length selector
                        $(table.api().table().container()).find(".dataTables_length select")
                            .removeClass(
                                'custom-select custom-select-sm form-control form-control-sm')
                            .addClass('dataTable-selector');

                        // Custom table title
                        tableContainer.find(".dataTable-title").html(
                            $("<div>").addClass("flex justify-start items-center").append(
                                $("<div>").addClass("custom-table-header"),
                                $("<span>").addClass("font-medium text-2xl pl-4").text(
                                    "Create Category")
                            )
                        );
                    }
                });
                // Smooth reorder handler
                $(".dt-buttons").removeClass("btn-group flex-wrap");
                table.on('row-reorder', function(e, diff, edit) {
                    if (diff.length === 0) return;

                    let order = [];
                    diff.forEach(function(move) {
                        let rowData = table.row(move.node).data();
                        order.push({
                            id: rowData.id,
                            position: move.newPosition + 1
                        });
                    });

                    $.ajax({
                        url: "{{ route('album.category.reorder') }}",
                        method: "POST",
                        contentType: "application/json",
                        data: JSON.stringify({
                            order: order,
                            _token: "{{ csrf_token() }}"
                        }),
                        success: function() {
                            table.ajax.reload(null, false);
                        },
                        error: function(err) {
                            console.error('Reorder failed:', err);
                        }
                    });

                });

                function handleResponsiveColumn(table) {
                    if (window.innerWidth <= 1352) {
                        // Show responsive icon column
                        $('#icon12').show();
                        table.column(0).visible(true);
                    } else {
                        // Hide responsive icon column
                        $('#icon12').hide();
                        table.column(0).visible(false);
                    }
                }

                // Run on load and resize
                handleResponsiveColumn(table);
                $(window).on('resize', function() {
                    handleResponsiveColumn(table);
                });

            });
        });
    </script>
@endpush
