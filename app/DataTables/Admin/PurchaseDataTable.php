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
                $lessonName = e($purchase->lesson_name);
                $truncatedLessonName = strlen($lessonName) > 40 ? substr($lessonName, 0, 40) . '...' : $lessonName;

                $lesson_type = $purchase->lesson->type ?? null;

                $url = $lesson_type == Lesson::LESSON_TYPE_ONLINE ? route('purchase.feedback.index', ['purchase_id' => $purchase->id]) :
                    route('purchase.show', $purchase->id);

                if (Auth::user()->type == 'Instructor') {
                    $lessonLink = '<a href="' . $url . '" class="text-blue-600 hover:underline mr-2" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="' . $lessonName . '">' . $truncatedLessonName . '</a>';
                } else {
                    $lessonLink = '<span class="text-gray-800 mr-2" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="' . $lessonName . '">' . $truncatedLessonName . '</span>';
                }

                return '
                    <div class="flex justify-between items-center">
                        ' . $lessonLink . '
                    </div>';
            })
            ->addColumn('pill', function ($purchase) {
                $s = Lesson::TYPE_MAPPING[$purchase->lesson->type] ?? 'N/A';
                $lesson_type = $purchase->lesson->type ?? null;
                $badgeStyle = $lesson_type == Lesson::LESSON_TYPE_ONLINE
                    ? 'background-color: #16A34A; color: white; padding: 4px 12px; border-radius: 9999px; display: inline-block; font-size: 14px;'
                    : 'background-color: #06B6D4; color: white; padding: 4px 12px; border-radius: 9999px; display: inline-block; font-size: 14px;';
                return '<span style="' . $badgeStyle . '">' . e($s) . '</span>';
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
                $statusStyle = $purchase->status == Purchase::STATUS_COMPLETE
                    ? 'background-color: #16A34A; color: white; padding: 4px 12px; border-radius: 9999px; display: inline-block; font-size: 14px;'
                    : 'background-color: #DC2626; color: white; padding: 4px 12px; border-radius: 9999px; display: inline-block; font-size: 14px;';
                return '<span style="' . $statusStyle . '">' . e($s) . '</span>';
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
            ->rawColumns(['action', 'status', 'student_name', 'instructor_name', 'lesson_name', 'pill', 'remaining_slots']);
    }

    public function query(Purchase $model)
    {
        $user = Auth::user();
        $lessonType = request('lesson_type');
        if (request('lesson_type') === 'pre-set') {
            $query = Purchase::query()
                ->select([
                    'purchases.id',
                    'lessons.lesson_name',
                    'students.name as student_name',
                    'slots.date_time',
                    'slots.location',
                    'purchases.friend_names',
                    'purchases.type'
                ])
                ->join('students', 'purchases.student_id', '=', 'students.id')
                ->leftJoin('slots', 'slots.lesson_id', '=', 'purchases.lesson_id')
                ->leftJoin('lessons', 'lessons.id', '=', 'purchases.lesson_id')
                ->where('purchases.type', 'inPerson')
                ->orderBy('slots.date_time', 'asc');

            return datatables()->of($query)->make(true);
        }

        $query = $model->newQuery()
            ->select([
                'purchases.*',
                'lessons.lesson_name as lesson_name',
                'instructors.name as instructor_name',
                'students.name as student_name'
            ])
            ->join('lessons', 'purchases.lesson_id', '=', 'lessons.id')
            ->join('users as instructors', 'purchases.instructor_id', '=', 'instructors.id')
            ->join('students as students', 'purchases.student_id', '=', 'students.id')
            ->orderBy('purchases.created_at', 'desc');

        if (request()->has('lesson_type') && request('lesson_type')) {
            $query->where(function ($q) {
                $q->where('lessons.type', request('lesson_type'));
            });
        }

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

        $buttons = [];

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
                        var selectedValue = $(this).val();
                        if (selectedValue === "pre-set") {
                            $("#preSetModal").modal("show");
                            table.api().ajax.reload(function(json) {
                                delete json.data.lesson_type;
                            });
                        } else {
                            table.api().ajax.reload();
                        }
                    });

                    $("#purchases-table").DataTable().on("preXhr.dt", function(e, settings, data) {
                        var selectedValue = $("#lessonTypeFilter").val();
                        if (selectedValue !== "pre-set") {
                            data.lesson_type = selectedValue;
                        } else {
                            data.lesson_type = "";
                        }
                    });
                }')
            ->parameters([
                "columnDefs" => [
                    ["responsivePriority" => 1, "targets" => 1],
                    ["responsivePriority" => 2, "targets" => 3],
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
                        "display" => "$.fn.dataTable.Responsive.display.childRow",
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
                        $("td", row).css("font-family", "Helvetica");
                        $("td", row).css("font-weight", "300");
                    }',
                "drawCallback" => 'function( settings ) {
                        var tooltipTriggerList = [].slice.call(
                            document.querySelectorAll("[data-bs-toggle=tooltip]")
                        );
                        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                            return new bootstrap.Tooltip(tooltipTriggerEl, {
                                delay: { show: 100, hide: 200 },
                                trigger: "hover"
                            });
                        });
                        var popoverTriggerList = [].slice.call(
                            document.querySelectorAll("[data-bs-toggle=popover]")
                        );
                        var popoverList = popoverTriggerList.map(function (popoverTriggerEl) {
                            return new bootstrap.Popover(popoverTriggerEl);
                        });
                        var toastElList = [].slice.call(
                            document.querySelectorAll(".toast")
                        );
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
        if (request('lesson_type') === 'pre-set') {
            return [
                Column::make('student_name')->title(__('Student')),
                Column::make('date_time')->title(__('Date & Time')),
                Column::make('location')->title(__('Location')),
                Column::make('friend_names')->title(__('Friend Names')),
                Column::make('type')->title(__('Type')),
            ];
        }
        $columns = [
            Column::make('id')->title(__('Lesson #'))->searchable(true)->orderable(true)->width('80px'),
            Column::make('lesson_name')->title(__('Lesson Title'))->searchable(true)->width('250px'),
            Column::make('pill')->title(__('Type'))->searchable(false)->orderable(false),
            Column::make('remaining_slots')->title(__('Remaining Slots'))->orderable(false)->searchable(false)->addClass('text-center'),
        ];

        if (Auth::user()->type == Role::ROLE_INSTRUCTOR) {
            $columns[] = Column::make('student_name')->title(__('Student'))->searchable(true);
            $columns[] = Column::make('instructor_name')->title(__('Instructor'))->searchable(true);
        } elseif (Auth::user()->type == Role::ROLE_STUDENT) {
            $columns[] = Column::make('instructor_name')->title(__('Instructor'))->searchable(true);
        }

        return array_merge($columns, [
            Column::make('status')->title(__('Payment Status')),
            Column::make('due_date')->title(__('Submission Date'))->defaultContent()->orderable(false)->searchable(false),
            Column::make('total_amount')->title(__('Total ($)'))->orderable(false),
            Column::computed('action')->title(__('Actions'))
                ->exportable(false)
                ->printable(false)
                ->addClass('text-center')
                ->width('80px'),
        ]);
    }

    protected function filename(): string
    {
        return 'Purchases_' . date('YmdHis');
    }
}
