<?php
namespace App\DataTables\Admin;

use App\Facades\UtilityFacades;
use App\Models\PurchaseVideos;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class PurchaseLessonDataTable extends DataTable
{
    public function dataTable($query)
    {
        return datatables()
            ->eloquent($query)
            ->addIndexColumn()
            ->editColumn('purchase_id', function (PurchaseVideos $purchaseVideo) {

                return $purchaseVideo->purchase_id;
            })
            ->editColumn('note', function (PurchaseVideos $purchaseVideo) {
                $note = $purchaseVideo->note ? nl2br(e($purchaseVideo->note)) : "No note provided";
                return '<div style="white-space: pre-wrap; word-wrap: break-word; max-width: 400px;">' . $note . '</div>';
            })
            ->editColumn('instructor_id', function () {
                $instructor_name = User::find($this->purchase->instructor_id);
                return $instructor_name->name;
            })
            ->editColumn('video', function (PurchaseVideos $purchaseVideo) {
                $video = $purchaseVideo->video_url;
                return view('admin.purchases.renderVideo', compact('video'));
            })
            ->editColumn('feedback', function (PurchaseVideos $purchaseVideo) {
                $feedback = $purchaseVideo->feedback ? nl2br(e($purchaseVideo->feedback)) : "Feedback pending";
                return '<div style="white-space: pre-wrap; word-wrap: break-word; max-width: 400px;">' . $feedback . '</div>';
            })
            ->editColumn('created_at', function ($request) {
                $created_at = UtilityFacades::date_time_format($request->created_at);
                return $created_at;
            })
            ->addColumn('action', function (PurchaseVideos $purchaseVideo) {
                return view('admin.purchases.purchaseVideoAction', compact('purchaseVideo'));
            })
            ->rawColumns(['action', 'logo_image', 'feedback', 'note']);
    }

    public function query(PurchaseVideos $model)
    {
        return $model->newQuery()->where('purchase_id', $this->purchase->id);
    }

    public function html()
    {
        return $this->builder()
            ->setTableId('purchases-table')
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
                "searchPlaceholder" => __('Search...'), "search" => ""
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
                               <'dataTable-top row'<'dataTable-dropdown page-dropdown col-lg-2 col-sm-12'l><'dataTable-botton table-btn col-lg-6 col-sm-12'B><'dataTable-search tb-search col-lg-3 col-sm-12'f>>
                             <'dataTable-container'<'col-sm-12'tr>>
                             <'dataTable-bottom row'<'col-sm-5'i><'col-sm-7'p>>
                               ",

                "scrollX" => true,
                "responsive" => [
                    "scrollX"=> false,
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
                }'
            ]);
    }

    protected function getColumns()
    {
        $columns = [
            Column::make('No')->title(__('No'))->data('DT_RowIndex')->name('DT_RowIndex')->searchable(false)->orderable(false),
            Column::make('note')->title(__('Note')),
            Column::make('video')->title(__('Video'))->searchable(false),
            Column::make('feedback')->title(__('Feedback')),
            Column::make('created_at')->title(__('Created At')),
            Column::computed('action')->title(__('Action'))
                ->exportable(false)
                ->printable(false)
                ->width(60)
                ->addClass('text-center')
                ->width('20%'),

        ];

        if (Auth::user()->type == Role::ROLE_STUDENT) {
            unset($columns[6]);
        }

        return $columns;
    }

    protected function filename(): string
    {
        return 'Purchases_' . date('YmdHis');
    }
}
