<?php

namespace App\DataTables\Admin;

use App\Models\Lesson;
use App\Models\Purchase;
use App\Models\Role;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class PurchaseDataTable extends DataTable
{
    public function dataTable($query)
    {
        return datatables()
            ->eloquent($query)
            ->smart(false)
            ->addIndexColumn()
            ->editColumn('instructor_id', function (Purchase $purchase) {
                $imageSrc = $purchase->instructor->dp ?  asset('/storage' . '/' . tenant('id') . '/' . $purchase->instructor->dp) : asset('assets/img/logo/logo.png');
                $html =
                    '
                <div class="flex justify-start items-center">'
                    .
                    "<img src=' " . $imageSrc . " ' width='20' class='rounded-full'/>"
                    .
                    "<span class='px-0'>" . $purchase->instructor->name . " </span>" .
                    '</div>';
                return $html;
            })
            ->editColumn('lesson_id', function (Purchase $purchase) {
                $s = Lesson::TYPE_MAPPING[$purchase->lesson->type];
                $lesson_type = $purchase->lesson->type;
                if ($lesson_type == Lesson::LESSON_TYPE_ONLINE) {
                    return '
                    <div class="flex justify-between">
                    <span class="mr-2">' . $purchase->lesson->lesson_name . '</span>' .
                        '<label class="badge rounded-pill bg-green-600 p-2 px-3">' . $s . '
                    </label>
                    </div>
                    ';
                }
                if ($lesson_type == Lesson::LESSON_TYPE_INPERSON) {
                    return '
                    <div class="flex justify-between">
                    <span class="mr-2">' . $purchase->lesson->lesson_name . '</span>' .
                        '<label class="badge rounded-pill bg-cyan-500 p-2 px-3">' . $s . '
                    </label>
                    </div>
                    ';
                }
                return '<label class="badge rounded-pill bg-green-600 p-2 px-3">' . $s . '</label>';;
            })
            ->editColumn('student_id', function (Purchase $purchase) {
                $imageSrc = $purchase->student->dp ?  asset('/storage' . '/' . tenant('id') . '/' . $purchase->student->dp) : asset('assets/img/logo/logo.png');
                $html =
                    '
                <div class="flex justify-start items-center">'
                    .
                    "<img src=' " . $imageSrc . " ' width='20' class='rounded-full'/>"
                    .
                    "<span class='px-0'>" . $purchase->student->name . " </span>" .
                    '</div>';
                return $html;
            })
            ->addColumn('status', function (Purchase $purchase) {
                $s = Purchase::STATUS_MAPPING[$purchase->status];
                if ($purchase->status == Purchase::STATUS_COMPLETE) {
                    return '<label class="badge rounded-pill bg-green-600 p-2 px-3">' . $s . '</label>';
                }
                if ($purchase->status == Purchase::STATUS_INCOMPLETE) {
                    return '<label class="badge rounded-pill bg-red-600 p-2 px-3">' . $s . '</label>';
                }
                return '<label class="badge rounded-pill bg-green-600 p-2 px-3">' . $s . '</label>';;
            })
            ->editColumn('due_date', function (Purchase $purchase) {
                $date = Carbon::parse($purchase?->created_at);
                return $date->toFormattedDateString();
            })
            ->addColumn('action', function (Purchase $purchase) {
                return view('admin.purchases.action', compact('purchase'));
            })
            ->rawColumns(['action', 'logo_image', 'status', "student_id", "instructor_id", 'lesson_id']);
    }

    public function query(Purchase $model)
    {
        $user = Auth::user();
        if ($user->type == Role::ROLE_STUDENT)
            return $model->newQuery()->where('student_id', $user->id)->where('status', Purchase::STATUS_COMPLETE);
        if ($user->type == Role::ROLE_ADMIN)
            return $model->newQuery()->whereHas('lesson', function ($query) {
                $query->where('type', 'online');
            })->where('status', 'complete')
                ->orWhere(function ($query) {
                    $query->whereHas('lesson', function ($subQuery) {
                        $subQuery->where('type', 'inPerson');
                    })->whereIn('status', ['complete', 'incomplete'])->with('lesson');
                });
        if (Auth::user()->type == Role::ROLE_INSTRUCTOR)
            return $model->newQuery()->where('instructor_id', $user->id)->where('status', Purchase::STATUS_COMPLETE);
    }

    public function html()
    {
        $buttons = [
            ['extend' => 'reset', 'className' => 'btn btn-light-danger me-1'],
            ['extend' => 'reload', 'className' => 'btn btn-light-warning'],
        ];
        if (Auth::user()->type == Role::ROLE_INSTRUCTOR) {
            unset($buttons[0]);
        }

        return $this->builder()
            ->setTableId('purchases-table')
            ->columns($this->getColumns())
            ->minifiedAjax()
            ->orderBy(1)
            ->language([
                "paginate" => [
                    "next" => '<i class="ti ti-chevron-right"></i>',
                    "previous" => '<i class="ti ti-chevron-left"></i>'
                ],
                'lengthMenu' => __('_MENU_ entries per page'),
                "searchPlaceholder" => __('Search'),
                'search' => ''
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
                <'dataTable-top row'<'dataTable-title col-lg-3 col-sm-12'>
                <'dataTable-botton table-btn col-lg-6 col-sm-12'B><'dataTable-search tb-search col-lg-3 col-sm-12'f>>
                <'dataTable-container'<'col-sm-12'tr>>
                <'dataTable-bottom row'<'dataTable-dropdown page-dropdown col-lg-2 col-sm-12'l>
                <'col-sm-7'p>>
                ",
                'buttons'   => $buttons,
                "scrollX" => true,
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
                }'
            ])->language([
                'buttons' => [
                    'create' => __('Choose Your Coach'),
                    'print' => __('Print'),
                    'reset' => __('Reset'),
                    'reload' => __('Reload'),
                    'excel' => __('Excel'),
                    'csv' => __('CSV'),
                ]
            ]);
    }

    protected function getColumns()
    {
        $columns = [
            Column::make('No')->title(__('Lesson Number'))->data('DT_RowIndex')->name('DT_RowIndex')->searchable(false)->orderable(false),
            Column::make('lesson_id')->title(__('Lesson'))->searchable(false)->orderable(false),
            Column::make('instructor_id')->title(__('Instructor'))->searchable(false)->orderable(false),
            Column::make('student_id')->title("Student"),
            Column::make('status')->title(__('Payment Status'))->searchable(false)->orderable(false),
            Column::make("due_date")->title(__('Submission Date'))->defaultContent()->searchable(false)->orderable(false),
            Column::make('total_amount')->title(__('Total ($)'))->searchable(false)->orderable(false),
            Column::computed('action')->title(__('Actions'))
                ->exportable(false)
                ->printable(false)
                ->width(60)
                ->addClass('text-center')
                ->width('20%'),
        ];

        if (Auth::user()->type == Role::ROLE_INSTRUCTOR) {
            unset($columns[2]);
        }
        if (Auth::user()->type == Role::ROLE_STUDENT) {
            unset($columns[3]);
        }

        return $columns;
    }

    protected function filename(): string
    {
        return 'Purchases_' . date('YmdHis');
    }
}
