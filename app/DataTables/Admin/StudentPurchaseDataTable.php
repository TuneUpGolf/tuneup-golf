<?php

namespace App\DataTables\Admin;

use App\Models\Lesson;
use App\Models\Purchase;
use App\Models\Role;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class StudentPurchaseDataTable extends DataTable
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
            // ->addColumn('pill', function ($purchase) {
            //     $s = Lesson::TYPE_MAPPING[$purchase->lesson->type] ?? 'N/A';
            //     $lesson_type = $purchase->lesson->type ?? null;
            //     $badgeClass = $lesson_type == Lesson::LESSON_TYPE_ONLINE ? 'bg-green-600' : 'bg-cyan-500';
            //     return '<label class="badge rounded-pill ' . $badgeClass . ' p-2 px-3">' . e($s) . '</label>';
            // })
            ->addColumn('pill', function ($purchase) {
                $s = Lesson::TYPE_MAPPING[$purchase->lesson->type] ?? 'N/A';
                $lesson_type = $purchase->lesson->type ?? null;
                $badgeStyle = $lesson_type == Lesson::LESSON_TYPE_ONLINE
                    ? 'background-color: #16A34A; color: white; padding: .25rem .75rem; border-radius: 624.9375rem; display: inline-block; font-size: .875rem;'
                    : 'background-color: #cc8217; color: white; padding: .25rem .75rem; border-radius: 624.9375rem; display: inline-block; font-size: .875rem;';
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
            // ->addColumn('status', function ($purchase) {
            //     $s = Purchase::STATUS_MAPPING[$purchase->status] ?? 'Unknown';
            //     $statusClass = $purchase->status == Purchase::STATUS_COMPLETE ? 'bg-green-600' : 'bg-red-600';

            //     return '<label class="badge rounded-pill ' . $statusClass . ' p-2 px-3">' . e($s) . '</label>';
            // })
            ->addColumn('status', function ($purchase) {
                $s = Purchase::STATUS_MAPPING[$purchase->status] ?? 'Unknown';
                // Inline styles for modal compatibility
                $statusStyle = $purchase->status == Purchase::STATUS_COMPLETE
                    ? 'background-color: #16A34A; color: white; padding: .25rem .75rem; border-radius: 624.9375rem; display: inline-block; font-size: .875rem;'
                    : 'background-color: #DC2626; color: white; padding: .25rem .75rem; border-radius: 624.9375rem; display: inline-block; font-size: .875rem;';
                return '<span style="' . $statusStyle . '">' . e($s) . '</span>';
            })
            ->editColumn('due_date', function ($purchase) {
                return Carbon::parse($purchase->created_at)->toFormattedDateString();
            })
            ->addColumn('remaining_slots', function ($purchase) {
                $lesson = $purchase->lesson;
                if (!$lesson) return '0';

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
                return view('admin.purchases.student_action', compact('purchase', 'hasBooking'));
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
                'students.name as student_name', // Get student name
                'lessons.type as lesson_type',
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
        $lessonTypeFilter = "<select id='lessonTypeFilter' class='form-select' style='margin-left:auto; max-width: 12.5rem;'><option value=''>- Lesson Type -</option>";
        foreach (Lesson::SELECT_TYPE_MAPPING as $key => $label) {
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
                'lengthMenu'       => __('_MENU_ entries per page'),
                "searchPlaceholder"=> __('Search'),
                'search'           => ''
            ])
            ->initComplete('function() {
                var table = this;
                var searchInput = $(\'#\' + table.api().table().container().id + \' label input[type="search"]\');
                searchInput.removeClass(\'form-control form-control-sm\').addClass(\'dataTable-input\');

                var select = $(table.api().table().container())
                    .find(".dataTables_length select")
                    .removeClass(\'custom-select custom-select-sm form-control form-control-sm\')
                    .addClass(\'dataTable-selector\');

                $(".dataTable-search").prepend("' . $lessonTypeFilter . '").addClass("d-flex");

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
                "dom" => "
                    <'dataTable-top row'
                        <'dataTable-title col-xl-7 col-lg-3 col-sm-6 d-none d-sm-block'>
                        <'dataTable-search dataTable-search tb-search col-md-5 col-sm-6 col-lg-6 col-xl-5 col-sm-12 d-flex'f>
                    >
                    <'dataTable-container'<'col-sm-12'tr>>
                    <'dataTable-bottom row'
                        <'dataTable-dropdown page-dropdown col-lg-2 col-sm-12'l>
                        <'col-sm-7'p>
                    >
                ",
                "buttons"   => $buttons,
                "scrollX"   => true,
                "responsive" => [
                    "details" => [
                        "display" => "$.fn.dataTable.Responsive.display.modal({
                            header: function () { return 'Lesson Details'; }
                        })",
                        "renderer" => "function (api, rowIdx, columns) {
    var data = columns.map(function(col) {
        switch(col.title) {
            case 'Lesson #':
            case 'Status':
            case 'Remaining Slots': // 👈 remove from auto-render
                return '';
            default:
                return '<tr data-dt-row=\"'+col.rowIndex+'\" data-dt-column=\"'+col.columnIndex+'\">'+
                        '<td>'+col.title+':</td>'+
                        '<td>'+col.data+'</td>'+
                    '</tr>';
        }
    }).join('');

    var rowData = api.row(rowIdx).data();
    var baseSlotUrl = '".route('slot.view')."';

    var lessonDateTime = rowData.lesson_datetime ?? rowData.due_date ?? '-';
    var lessonDateHtml = '<tr><td>Lesson Date/Time:</td><td>'+ lessonDateTime +'</td></tr>';

    // ✅ Show Remaining Slots only for inPerson or package
    var remainingSlotsHtml = '';
    if (rowData.lesson_type === 'inPerson' || rowData.lesson_type === 'package') {
        remainingSlotsHtml = '<tr><td>Remaining Slots:</td><td>'+ (rowData.remaining_slots ?? '-') +'</td></tr>';
    }

    var buttonsHtml =
        '<div class=\"mt-3 text-end\">' +
            '<button type=\"button\" class=\"btn btn-danger btn-sm me-2\" onclick=\"cancelLesson('+rowData.id+')\">Cancel Lesson</button>' +
            '<a href=\"'+ baseSlotUrl + '?lesson_id='+ rowData.lesson_id +'\" class=\"btn btn-primary btn-sm\">Change Lesson Time</a>' +
        '</div>';

    return $('<table class=\"table table-striped table-bordered vertical-table w-100\"/>')
            .append('<tbody>' + data + lessonDateHtml + remainingSlotsHtml + '</tbody>')
            .after(buttonsHtml);
}"
                    ]
                ],
                "rowCallback" => 'function(row) {
                    $("td", row).css({"font-family":"Helvetica", "font-weight":"300"});
                    $(row).addClass("custom-parent-row");
                }',
                "headerCallback" => 'function(thead) {
                    $(thead).find("th").css({
                        "background-color": "rgba(249, 252, 255, 1)",
                        "font-weight": "400",
                        "font": "sans",
                        "border": "none"
                    });
                }',
                "drawCallback" => 'function() {
                    var tooltipTriggerList = [].slice.call(document.querySelectorAll("[data-bs-toggle=tooltip]"));
                    tooltipTriggerList.map(function (el) { return new bootstrap.Tooltip(el); });

                    var popoverTriggerList = [].slice.call(document.querySelectorAll("[data-bs-toggle=popover]"));
                    popoverTriggerList.map(function (el) { return new bootstrap.Popover(el); });

                    var toastElList = [].slice.call(document.querySelectorAll(".toast"));
                    toastElList.map(function (el) { return new bootstrap.Toast(el); });
                }'
            ])
            ->language([
                'buttons' => [
                    'create' => __('Choose Your Coach'),
                    'print'  => __('Print'),
                    'reset'  => __('Reset'),
                    'reload' => __('Reload'),
                    'excel'  => __('Excel'),
                    'csv'    => __('CSV'),
                ]
            ]);
    }

    
    protected function getColumns()
    {
        $columns = [
            Column::make('No')
                ->title(__('Lesson #'))
                ->data('DT_RowIndex')
                ->name('DT_RowIndex')
                ->searchable(false)
                ->orderable(false)
                ->addClass('min-desktop'), // always visible

            Column::make('lesson_name')
                ->title(__('Lesson'))
                ->searchable(true)
                ->addClass('all'), // hide on phones, show on tablet/desktop

            Column::make('pill')
                ->title(__('Type'))
                ->searchable(false)
                ->orderable(false)
                ->addClass('min-tablet'),

            Column::make('deleted')
                ->title(__('Status'))
                ->searchable(false)
                ->orderable(false)
                ->addClass('min-tablet'),

            Column::make('remaining_slots')
                ->title(__('Remaining Slots'))
                ->orderable(false)
                ->searchable(false)
                ->addClass('min-desktop text-center'), // hide on phones & small tablets
        ];

        if (Auth::user()->type == Role::ROLE_INSTRUCTOR) {
            $columns[] = Column::make('student_name')->title(__('Student'))->searchable(true)->addClass('min-tablet');
            $columns[] = Column::make('instructor_name')->title(__('Instructor'))->searchable(true)->addClass('min-tablet');
        } elseif (Auth::user()->type == Role::ROLE_STUDENT) {
            $columns[] = Column::make('instructor_name')->title(__('Instructor'))->searchable(true)->addClass('min-tablet');
        }

        return array_merge($columns, [
            Column::make('status')
                ->title(__('Payment Status'))
                ->addClass('min-tablet'),

            Column::make('due_date')
                ->title(__('Submission Date'))
                ->defaultContent()
                ->orderable(false)
                ->searchable(false)
                ->addClass('all'), // always visible even on mobile

            Column::make('total_amount')
                ->title(__('Total ($)'))
                ->orderable(false)
                ->addClass('min-tablet'),

            Column::computed('action')
                ->title(__('Actions'))
                ->exportable(false)
                ->printable(false)
                ->width(60)
                ->addClass('min-desktop')
                ->width('20%'),
        ]);
    }


    protected function filename(): string
    {
        return 'Purchases_' . date('YmdHis');
    }
}