<?php

namespace App\DataTables\Admin;

use App\Facades\UtilityFacades;
use App\Models\Role;
use App\Models\ExpenseType;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class ExpenseTypeDataTable extends DataTable
{
    public $module;

    public function __construct()
    {
        // $this->module = request()->is('expense_type') ? 'expense_type' : 'all-chat';
    }

    public function dataTable($query)
    {
        $loggedInUserId = auth()->id();
        $data = datatables()
            ->eloquent($query)
            ->addIndexColumn()
            ->editColumn('name', function (ExpenseType $expense_type) {
                // $userUrl  = route($this->module . '.show', $expense_type->id);
                $userUrl  = null;
                $html =
                    '
                <div class="flex justify-start items-center">
                <a href="' . $userUrl . '" class="flex items-center text-primary hover:underline">'
                    .
                    "<span class='pl-2'>" . $expense_type->type . " </span>" .
                    '</a></div>';


                return $html;
            })
            ->editColumn('created_at', function ($request) {
                return UtilityFacades::date_time_format($request->created_at);
            })
            ->addColumn('action', function (ExpenseType $expense_type) {
                return view('admin.expense_type.action', compact('expense_type'));
            })
            ->rawColumns(['action']);
        return $data;
    }

    public function query(ExpenseType $model)
    {
        if (tenant('id') == null) {
            return   $model->newQuery()->select(['expenses_types.*', 'domains.domain'])
                ->join('domains', 'domains.tenant_id', '=', 'expenses_types.tenant_id');
        } else {
            return $model->newQuery();
        }
    }

    public function html()
    {
        return $this->builder()
            ->setTableId('expenses-type-table')
            ->addTableClass('display responsive nowrap')
            ->columns($this->getColumns())
            ->minifiedAjax()
            ->orderBy(1)
            ->language([
                "paginate" => [
                    "next" => '<i class="ti ti-chevron-right"></i>',
                    "previous" => '<i class="ti ti-chevron-left"></i>'
                ],
                'lengthMenu' => __('_MENU_ entries per page'),
                "searchPlaceholder" => __('Search...'),
                "search" => ""
            ])
            ->initComplete('function() {
                var table = this;
                var searchInput = $(\'#\'+table.api().table().container().id+\' label input[type="search"]\');
                searchInput.removeClass(\'form-control form-control-sm\');
                searchInput.addClass(\'dataTable-input\');
                var select = $(table.api().table().container()).find(".dataTables_length select").removeClass(\'custom-select custom-select-sm form-control form-control-sm\').addClass(\'dataTable-selector\');
            }')
            ->parameters([
                "dom" =>  "
                        <'dataTable-top row'<'dataTable-title col-lg-3 col-sm-12 d-none d-sm-block'>
                        <'dataTable-botton table-btn col-lg-6 col-sm-12'B><'dataTable-search tb-search col-lg-3 col-sm-12'f>>
                        <'dataTable-container'<'col-sm-12'tr>>
                        <'dataTable-bottom row'<'dataTable-dropdown page-dropdown col-lg-2 col-sm-12'l>
                        <'col-sm-7'p>>
                        ",
                'buttons'   => [
                    ['extend' => 'create', 'className' => 'btn btn-light-primary no-corner me-1 add_module', 'action' => " function ( e, dt, node, config ) {
                         $('#expenseTypeModal').modal('show');
                   }"],
                    [
                        'extend' => 'collection',
                        'className' => 'btn btn-light-secondary me-1 dropdown-toggle',
                        'text' => '<i class="ti ti-download"></i> Export',
                        "buttons" => [
                            ["extend" => "print", "text" => '<i class="fas fa-print"></i> Print', "className" => "btn btn-light text-primary dropdown-item", "exportOptions" => ["columns" => [0, 1, 3]]],
                            ["extend" => "csv", "text" => '<i class="fas fa-file-csv"></i> CSV', "className" => "btn btn-light text-primary dropdown-item", "exportOptions" => ["columns" => [0, 1, 3]]],
                            ["extend" => "excel", "text" => '<i class="fas fa-file-excel"></i> Excel', "className" => "btn btn-light text-primary dropdown-item", "exportOptions" => ["columns" => [0, 1, 3]]],
                            ["extend" => "pdf", "text" => '<i class="fas fa-file-pdf"></i> PDF', "className" => "btn btn-light text-primary dropdown-item", "exportOptions" => ["columns" => [0, 1, 3]]],
                        ],
                    ],

                ],
                "scrollX" => true,
                "responsive" => [
                    "scrollX" => false,
                    "details" => [
                        "display" => "$.fn.dataTable.Responsive.display.childRow", // <- keeps rows collapsed
                        "renderer" => "function (api, rowIdx, columns) {
                            var data = $('<table/>').addClass('vertical-table');
                            $.each(columns, function (i, col) {
                                data.append(
                                    '<tr>' +
                                        '<td><strong>' + col.title + '</strong></td>' +
                                        '<td>' + col.data + '</td>' +
                                    '</tr>'
                                );
                            });
                            return data;
                        }"
                    ]
                ],
                "rowCallback" => 'function(row, data, index) {
                    $(row).addClass("custom-parent-row"); 
                }',
                'headerCallback' => 'function(thead, data, start, end, display) {
                    $(thead).find("th").css({
                        "background-color": "rgba(249, 252, 255, 1)",
                        "font-weight": "400",
                        "font":"sans",
                        "border":"none",
                    });
                }',
                'rowCallback' => 'function(row, data, index) {
                    // Make the first column bold
                    $("td", row).css("font-family", "Helvetica");
                    $("td", row).css("font-weight", "300");
                }',
                "drawCallback" => 'function( settings ) {
                    var tooltipTriggerList = [].slice.call(
                        document.querySelectorAll("[data-bs-toggle=tooltip]")
                      );
                      var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                        return new bootstrap.Tooltip(tooltipTriggerEl);
                      });
                      var popoverTriggerList = [].slice.call(
                        document.querySelectorAll("[data-bs-toggle=popover]")
                      );
                      var popoverList = popoverTriggerList.map(function (popoverTriggerEl) {
                        return new bootstrap.Popover(popoverTriggerEl);
                      });
                      var toastElList = [].slice.call(document.querySelectorAll(".toast"));
                      var toastList = toastElList.map(function (toastEl) {
                        return new bootstrap.Toast(toastEl);
                      });
                }',
            ])->language([
                'buttons' => [
                    'export' => __('Export'),
                    'print' => __('Print'),
                    'reload' => __('Import'),
                    'excel' => __('Excel'),
                    'csv' => __('CSV'),
                ]
            ]);
    }

    protected function getColumns()
    {

        $columns = [
            Column::make('No')->title(__('#'))->data('DT_RowIndex')->name('DT_RowIndex')->searchable(false)->orderable(false),
            Column::make('type')->title(__('Type')),
            Column::make('created_at')->title(__('Created At')),
        ];
        $columns[] = Column::computed('action')->title(__('Action'))
            ->exportable(false)
            ->printable(false)
            ->width(60)
            ->addClass('text-center')
            ->width('20%');
        return $columns;
    }

    protected function filename(): string
    {
        return 'ExpenseType_' . date('YmdHis');
    }
}
