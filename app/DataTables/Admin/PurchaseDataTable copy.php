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
    protected $tab;

    public function __construct($tab = null)
    {
        $this->tab = $tab;
    }

    public function dataTable($query)
    {
        return datatables()
            ->eloquent($query)
            ->smart(false)
            ->addIndexColumn()
            ->filterColumn('lesson_name', function ($query, $keyword) {
                $query->orWhere('lessons.lesson_name', 'like', "%{$keyword}%");
            })
            ->filterColumn('instructor_name', function ($query, $keyword) {
                $query->orWhere('instructors.name', 'like', "%{$keyword}%");
            })
            ->filterColumn('student_name', function ($query, $keyword) {
                $query->orWhere('students.name', 'like', "%{$keyword}%");
            })
            ->editColumn('instructor_name', function ($purchase) {
                $imageSrc = $purchase->instructor->dp
                    ? asset('/storage' . '/' . tenant('id') . '/' . $purchase->instructor->dp)
                    : asset('assets/img/logo/logo.png');

                return '
                    <div class="flex justify-start items-center">
                        <img src="' . $imageSrc . '" width="20" class="rounded-full"/>
                        <span class="px-0">' . e($purchase->instructor_name) . '</span>
                    </div>';
            })
            ->editColumn('lesson_name', function ($purchase) {
                $lessonName           = e($purchase->lesson_name);
                $truncatedLessonName  = strlen($lessonName) > 20 ? substr($lessonName, 0, 20) . '...' : $lessonName;

                $lesson_type = $purchase->lesson->type ?? null;

                $url = $lesson_type == Lesson::LESSON_TYPE_ONLINE ? route('purchase.feedback.index', ['purchase_id' => $purchase->id]) :
                    route('purchase.show', $purchase->id);

                // Check user role
                if (Auth::user()->type == 'Instructor') {
                    $lessonLink = '<a href="' . $url . '" class="text-blue-600 hover:underline mr-2" title="' . $lessonName . '">' . $truncatedLessonName . '</a>';
                } else {
                    $lessonLink = '<span class="text-gray-800 mr-2" title="' . $lessonName . '">' . $truncatedLessonName . '</span>';
                }

                return '
                <div class="flex justify-between items-center">
                    ' . $lessonLink . '
                </div>';
            })
            ->addColumn('deleted', function ($purchase) {
                return ! $purchase->lesson->active_status ? ' <span class="text-gray-500 italic"> Deleted</span>' : 'Active';
            })
            ->addColumn('pill', function ($purchase) {
                $s = Lesson::TYPE_MAPPING[$purchase->lesson->type] ?? 'N/A';
                $lesson_type = $purchase->lesson->type ?? null;
                $badgeClass = $lesson_type == Lesson::LESSON_TYPE_ONLINE ? 'bg-green-600' : 'bg-cyan-500';
                return '<label class="badge rounded-pill ' . $badgeClass . ' p-2 px-3">' . e($s) . '</label>';
            })
            ->editColumn('student_name', function ($purchase) {
                $imageSrc = $purchase->student->dp
                    ? asset('/storage' . '/' . tenant('id') . '/' . $purchase->student->dp)
                    : asset('assets/img/logo/logo.png');

                return '
                    <div class="flex justify-start items-center">
                        <img src="' . $imageSrc . '" width="20" class="rounded-full"/>
                        <span class="px-0">' . e($purchase->student_name) . '</span>
                    </div>';
            })
            ->addColumn('status', function ($purchase) {
                $s = Purchase::STATUS_MAPPING[$purchase->status] ?? 'Unknown';
                $statusClass = $purchase->status == Purchase::STATUS_COMPLETE ? 'bg-green-600' : 'bg-red-600';

                return '<label class="badge rounded-pill ' . $statusClass . ' p-2 px-3">' . e($s) . '</label>';
            })
            ->editColumn('due_date', function ($purchase) {
                return Carbon::parse($purchase->created_at)->toFormattedDateString();
            })
            ->addColumn('remaining_slots', function ($purchase) {
                $lesson = $purchase->lesson;
                if (!$lesson) return '-';

                if ($lesson->type == Lesson::LESSON_TYPE_PACKAGE) {
                    $used = \App\Models\StudentSlot::whereHas('slot', function ($query) use ($lesson) {
                        $query->where('lesson_id', $lesson->id)->where('is_completed', 1);
                    })->count();
                    $total = $purchase->purchased_slot ?? 0;

                    $used = $used > $total ? $total : $used;

                    return "{$used}/{$total}";
                }
                return '-';
            })
            ->addColumn('action', function ($purchase) {
                $hasBooking = \App\Models\Slots::where('lesson_id', $purchase->lesson_id)
                    ->whereHas('student')
                    ->exists();
                return view('admin.purchases.action', compact('purchase', 'hasBooking'));
            })
            ->rawColumns(['action', 'status', 'student_name', 'instructor_name', 'lesson_name', 'pill', 'deleted', 'remaining_slots']);
    }


    public function query(Purchase $model)
    {
        $user = Auth::user();

        $query = $model->newQuery()
            ->select([
                'purchases.*',  // Select all purchase fields
                'lessons.lesson_name as lesson_name',  // Get lesson name
                'instructors.name as instructor_name', // Get instructor name
                'students.name as student_name' // Get student name
            ])
            ->join('lessons', 'purchases.lesson_id', '=', 'lessons.id')
            ->join('users as instructors', 'purchases.instructor_id', '=', 'instructors.id')
            ->join('students as students', 'purchases.student_id', '=', 'students.id')
            ->orderBy('purchases.created_at', 'desc'); // Order by creation date in descending order

        // Filter by lesson type if provided
        if (request()->has('lesson_type') && request('lesson_type')) {
            $query->where(function ($q) {
                $q->where('lessons.type', request('lesson_type'));
            });
        }

        // Filter query based on user role
        if ($user->type == Role::ROLE_STUDENT) {
            $query->where('purchases.student_id', $user->id);
        }

        if ($user->type == Role::ROLE_ADMIN) {
            $query->where(function ($q) {
                $q->whereHas('lesson', function ($subQuery) {
                    $subQuery->where('is_package_lesson', true)
                        ->orWhere('type', 'online');
                })->where('status', 'complete')
                    ->orWhere(function ($subQ) {
                        $subQ->whereHas('lesson', function ($lessonQ) {
                            $lessonQ->where('type', 'inPerson')
                                ->where('is_package_lesson', false);
                        })->whereIn('status', ['complete', 'incomplete']);
                    });
            });
        }

        if ($user->type == Role::ROLE_INSTRUCTOR) {
            $query->where('purchases.instructor_id', $user->id);
        }

        return $query;
    }




    public function html()
    {
        $lessonTypeFilter = "<select id='lessonTypeFilter' class='form-select' style='margin-left:auto; max-width: 200px;'><option value=''>- Lesson Type -</option>";
        foreach (Lesson::TYPE_MAPPING as $key => $label) {
            $lessonTypeFilter .= "<option value='" . $key . "'>" . $label . "</option>";
        }
        $lessonTypeFilter .= "</select>";

        $buttons = [
            // ['extend' => 'reset', 'className' => 'btn btn-light-danger me-1'],
            // ['extend' => 'reload', 'className' => 'btn btn-light-warning'],

        ];

        if (Auth::user()->type == Role::ROLE_INSTRUCTOR) {
            unset($buttons[0]);
        }

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
                "searchPlaceholder" => __('Search'),
                'search' => ''
            ])
            ->initComplete('function() {
                var table = this;
                var searchInput = $(\'#\'+table.api().table().container().id+\' label input[type="search"]\');
                searchInput.removeClass(\'form-control form-control-sm\');
                searchInput.addClass(\'dataTable-input\');
                var select = $(table.api().table().container()).find(".dataTables_length select").removeClass(\'custom-select custom-select-sm form-control form-control-sm\').addClass(\'dataTable-selector\');
                
                $(".dataTable-search").prepend("' . $lessonTypeFilter . '");
                $(".dataTable-search").addClass("d-flex");

                $("#lessonTypeFilter").on("change", function() {
                    table.api().ajax.reload();
                });

                $("#purchases-table").DataTable().on("preXhr.dt", function(e, settings, data) {
                    data.lesson_type = $("#lessonTypeFilter").val();
                });
            }')
            ->parameters([
                "columnDefs" => [
                    ["responsivePriority" => 1, "targets" => 1],
                    ["responsivePriority" => 2, "targets" => 4],
                ],
                "dom" =>  "
                <'dataTable-top row'<'dataTable-title col-xl-7 col-lg-3 col-sm-6 d-none d-sm-block'>
                <'dataTable-search dataTable-search tb-search col-md-5 col-sm-6 col-lg-6 col-xl-5 col-sm-12 d-flex'f>>
                <'dataTable-container'<'col-sm-12'tr>>
                <'dataTable-bottom row'<'dataTable-dropdown page-dropdown col-lg-2 col-sm-12'l>
                <'col-sm-7'p>>
                ",
                'buttons'   => $buttons,
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
            Column::make('lesson_name')->title(__('Lesson'))->searchable(true),
            Column::make('pill')->title('')->searchable(false)->orderable(false),
            Column::make('deleted')->title('')->searchable(false)->orderable(false),
            Column::make('remaining_slots')->title(__('Remaining Slots'))->orderable(false)->searchable(false)->addClass('text-center'),
        ];
        if (Auth::user()->type == Role::ROLE_INSTRUCTOR) {
            $columns[] = Column::make('student_name')->title("Student")->searchable(true);
            $columns[] = Column::make('instructor_name')->title(__('Instructor'))->searchable(true);
        } elseif (Auth::user()->type == Role::ROLE_STUDENT) {
            $columns[] = Column::make('instructor_name')->title(__('Instructor'))->searchable(true);
        }
        return array_merge($columns, [
            Column::make('status')->title(__('Payment Status')),
            Column::make("due_date")->title(__('Submission Date'))->defaultContent()->orderable(false)->searchable(false),
            Column::make('total_amount')->title(__('Total ($)'))->orderable(false),
            Column::computed('action')->title(__('Actions'))
                ->exportable(false)
                ->printable(false)
                ->width(60)
                ->addClass('text-center')
                ->width('20%'),
        ]);
    }

    protected function filename(): string
    {
        return 'Purchases_' . date('YmdHis');
    }
}