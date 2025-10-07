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
                $s = Lesson::TYPE_MAPPING[$purchase->lesson->type] ?? ucfirst($purchase->lesson->type ?? 'N/A');
                $lesson_type = $purchase->lesson->type ?? null;

                if ($lesson_type == Lesson::LESSON_TYPE_INPERSON) {
                    return '<label class="badge rounded-pill bg-cyan-600 p-2 px-3">Pre-sets Date Lesson</label>';
                }

                if ($lesson_type == Lesson::LESSON_TYPE_ONLINE) {
                    return '<label class="badge rounded-pill bg-green-600 p-2 px-3">' . e($s) . '</label>';
                }

                if ($lesson_type == Lesson::LESSON_TYPE_PACKAGE) {
                    return '<label class="badge rounded-pill bg-yellow-600 p-2 px-3">' . e($s) . '</label>';
                }

                return '<label class="badge rounded-pill bg-yellow-600 p-2 px-3">' . e($s) . '</label>';
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



    //   public function html()
    //     {
    //         $lessonTypeFilter = "<select id='lessonTypeFilter' class='form-select' style='margin-left:auto; max-width: 12.5rem;'><option value=''>- Lesson Type -</option>";
    //         foreach (Lesson::SELECT_TYPE_MAPPING as $key => $label) {
    //             $lessonTypeFilter .= "<option value='" . $key . "'>" . $label . "</option>";
    //         }
    //         $lessonTypeFilter .= "</select>";

    //         $buttons = [
    //             // ['extend' => 'reset', 'className' => 'btn btn-light-danger me-1'],
    //             // ['extend' => 'reload', 'className' => 'btn btn-light-warning'],
    //         ];

    //         if (Auth::user()->type == Role::ROLE_INSTRUCTOR) {
    //             unset($buttons[0]);
    //         }

    //         return $this->builder()
    //             ->setTableId('purchases-table')
    //             ->addTableClass('display responsive nowrap')
    //             ->columns($this->getColumns())
    //             ->minifiedAjax()
    //             ->orderBy(1)
    //             ->language([
    //                 "paginate" => [
    //                     "next" => '<i class="ti ti-chevron-right"></i>',
    //                     "previous" => '<i class="ti ti-chevron-left"></i>'
    //                 ],
    //                 'lengthMenu' => __('_MENU_ entries per page'),
    //                 "searchPlaceholder" => __('Search'),
    //                 'search' => ''
    //             ])
    //             ->initComplete('function() {
    //                 var table = this;
    //                 var searchInput = $(\'#\' + table.api().table().container().id + \' label input[type="search"]\');
    //                 searchInput.removeClass(\'form-control form-control-sm\').addClass(\'dataTable-input\');

    //                 var select = $(table.api().table().container())
    //                     .find(".dataTables_length select")
    //                     .removeClass(\'custom-select custom-select-sm form-control form-control-sm\')
    //                     .addClass(\'dataTable-selector\');

    //                 $(".dataTable-search").prepend("' . $lessonTypeFilter . '").addClass("d-flex");

    //                 $("#lessonTypeFilter").on("change", function() {
    //                     table.api().ajax.reload();
    //                 });

    //                 $("#purchases-table").DataTable().on("preXhr.dt", function(e, settings, data) {
    //                     data.lesson_type = $("#lessonTypeFilter").val();
    //                 });
    //             }')
    //             ->parameters([
    //                 "columnDefs" => [
    //                     ["responsivePriority" => 1, "targets" => 1],
    //                     ["responsivePriority" => 2, "targets" => 4],
    //                 ],
    //                 "dom" => "
    //                     <'dataTable-top row'
    //                         <'dataTable-title col-xl-7 col-lg-3 col-sm-6 d-none d-sm-block'>
    //                         <'dataTable-search dataTable-search tb-search col-md-5 col-sm-6 col-lg-6 col-xl-5 col-sm-12 d-flex'f>
    //                     >
    //                     <'dataTable-container'<'col-sm-12'tr>>
    //                     <'dataTable-bottom row'
    //                         <'dataTable-dropdown page-dropdown col-lg-2 col-sm-12'l>
    //                         <'col-sm-7'p>
    //                     >
    //                 ",
    //                 "buttons" => $buttons,
    //                 "scrollX" => true,
    //                 "responsive" => [
    //                     "details" => [
    //                         "display" => "$.fn.dataTable.Responsive.display.childRow",
    //                         "renderer" => "function (api, rowIdx, columns) {
    //                             console.log('Renderer called for rowIdx:', rowIdx); 

    //                             var data = columns.map(function(col) {
    //                                 switch(col.title) {
    //                                     case 'Lesson #':
    //                                     case 'Status':
    //                                     case 'Remaining Slots':
    //                                         return '';
    //                                     default:
    //                                         return '<tr><td style=\"font-weight: bold; padding: 5px;\">' + col.title + ':</td><td style=\"padding: 5px;\">' + (col.data || '-') + '</td></tr>';
    //                                 }
    //                             }).join('');

    //                             var rowData = api.row(rowIdx).data();
    //                             console.log('rowData:', rowData); 
    //                             console.log('lesson_type:', rowData.lesson_type);

    //                             var lessonDateTime = rowData.lesson_datetime ?? rowData.due_date ?? '-';
    //                             var lessonDateHtml = '<tr><td style=\"font-weight: bold; padding: 5px;\">Lesson Date/Time:</td><td style=\"padding: 5px;\">' + lessonDateTime + '</td></tr>';

    //                             var remainingSlotsHtml = '';
    //                             if (rowData.remaining_slots && rowData.remaining_slots !== '-') {
    //                                 remainingSlotsHtml = '<tr><td style=\"font-weight: bold; padding: 5px;\">Remaining Slots:</td><td style=\"padding: 5px;\">' + rowData.remaining_slots + '</td></tr>';
    //                             }

    //                             var textareaHtml = '';
    //                             var buttonsHtml = '';
    //                             if (rowData.lesson_type && rowData.lesson_type !== 'online') {
    //                             textareaHtml = '<div style=\"margin-top: 10px; margin-bottom: 10px;\">' +
    //                                 '<textarea id=\"lessonNotes\" name=\"notes\" class=\"form-control lesson-notes\" rows=\"4\" placeholder=\"Enter your notes here...\" style=\"width: 100%; resize: vertical;\"></textarea>' +
    //                                 '</div>';
    //                             var baseSlotUrl = '" . route('slot.view') . "';
    //                             buttonsHtml = '<div class=\"mt-3 text-end\">' +
    //                                 '<button type=\"button\" class=\"btn btn-warning btn-sm me-2\" onclick=\"cancelLesson('+rowData.lesson_id+')\">Cancel Lesson</button>' +
    //                                 '<a href=\"'+baseSlotUrl+'?lesson_id='+rowData.lesson_id+'\" class=\"btn btn-success btn-sm\">Change Lesson Time</a>' +
    //                             '</div>';
    //                         } else {
    //                             console.log('Not showing buttons, lesson_type:', rowData.lesson_type); 
    //                         }

    //                             var content = '<div style=\"padding: 0px;\"><table style=\"width: 100%; border-collapse: collapse;\">' + data + lessonDateHtml + remainingSlotsHtml + '</table>' + textareaHtml + buttonsHtml + '</div>';

    //                             Swal.fire({
    //                                 title: 'Lesson Details',
    //                                 html: content,
    //                                 showCloseButton: true,
    //                                 showConfirmButton: false,
    //                                 customClass: {
    //                                     container: 'swal2-responsive-container',
    //                                     popup: 'swal2-responsive-popup'
    //                                 }
    //                             });

    //                             return false; // Prevent default child row rendering
    //                         }"
    //                     ]
    //                 ],
    //                 "rowCallback" => 'function(row) {
    //                     $("td", row).css({"font-family":"Helvetica", "font-weight":"300"});
    //                     $(row).addClass("custom-parent-row");
    //                 }',
    //                 "headerCallback" => 'function(thead) {
    //                     $(thead).find("th").css({
    //                         "background-color": "rgba(249, 252, 255, 1)",
    //                         "font-weight": "400",
    //                         "font": "sans",
    //                         "border": "none"
    //                     });
    //                 }',
    //                 "drawCallback" => 'function() {
    //                     var tooltipTriggerList = [].slice.call(document.querySelectorAll("[data-bs-toggle=tooltip]"));
    //                     tooltipTriggerList.map(function (el) { return new bootstrap.Tooltip(el); });

    //                     var popoverTriggerList = [].slice.call(document.querySelectorAll("[data-bs-toggle=popover]"));
    //                     popoverTriggerList.map(function (el) { return new bootstrap.Popover(el); });

    //                     var toastElList = [].slice.call(document.querySelectorAll(".toast"));
    //                     toastElList.map(function (el) { return new bootstrap.Toast(el); });
    //                 }'
    //             ])
    //             ->language([
    //                 'buttons' => [
    //                     'create' => __('Choose Your Coach'),
    //                     'print' => __('Print'),
    //                     'reset' => __('Reset'),
    //                     'reload' => __('Reload'),
    //                 'excel' => __('Excel'),
    //                 'csv' => __('CSV'),
    //             ]
    //         ]);
    //     }

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
                'lengthMenu' => __('_MENU_ entries per page'),
                "searchPlaceholder" => __('Search'),
                'search' => ''
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
                "buttons" => $buttons,
                "scrollX" => true,
                "responsive" => [
                    "details" => [
                        "display" => "$.fn.dataTable.Responsive.display.childRow",
                        "renderer" => "function (api, rowIdx, columns) {
                        console.log('Renderer called for rowIdx:', rowIdx); 

                        var data = columns.map(function(col) {
                            switch(col.title) {
                                case 'Lesson #':
                                case 'Status':
                                case 'Remaining Slots':
                                    return '';
                                default:
                                    return '<tr><td style=\"font-weight: bold; padding: 5px;\">' + col.title + ':</td><td style=\"padding: 5px;\">' + (col.data || '-') + '</td></tr>';
                            }
                        }).join('');

                        var rowData = api.row(rowIdx).data();
                        console.log('rowData:', rowData); 
                        console.log('lesson_type:', rowData.lesson_type);

                        var lessonDateTime = rowData.lesson_datetime ?? rowData.due_date ?? '-';
                        var lessonDateHtml = '<tr><td style=\"font-weight: bold; padding: 5px;\">Lesson Date/Time:</td><td style=\"padding: 5px;\">' + lessonDateTime + '</td></tr>';

                        var remainingSlotsHtml = '';
                        if (rowData.remaining_slots && rowData.remaining_slots !== '-') {
                            remainingSlotsHtml = '<tr><td style=\"font-weight: bold; padding: 5px;\">Remaining Slots:</td><td style=\"padding: 5px;\">' + rowData.remaining_slots + '</td></tr>';
                        }

                        var textareaHtml = '';
                        var buttonsHtml = '';
                        var currentNotes = ''; // Variable to store textarea value
                        if (rowData.lesson_type && rowData.lesson_type !== 'online' && rowData.deleted =='Active') {
                            textareaHtml = '<div style=\"margin-top: 10px; margin-bottom: 10px;\">' +
                                '<textarea id=\"lessonNotes-' + rowIdx + '\" class=\"form-control lesson-notes\" rows=\"4\" placeholder=\"Enter your notes here...\" style=\"width: 100%; resize: vertical;\"></textarea>' +
                                '</div>';
                            var baseSlotUrl = '" . route('slot.view') . "';
                            buttonsHtml = '<div class=\"mt-3 text-end\">' +
                                '<button type=\"button\" class=\"btn btn-warning btn-sm me-2\" onclick=\"cancelLesson('+rowData.lesson_id+', '+rowIdx+', this, \'' + rowIdx + '\')\">Cancel Lesson</button>' +
                                '<a href=\"'+baseSlotUrl+'?lesson_id='+rowData.lesson_id+'\" class=\"btn btn-success btn-sm\">Change Lesson Time</a>' +
                            '</div>';
                        } else {
                            console.log('Not showing buttons, lesson_type:', rowData.lesson_type); 
                        }

                        var content = '<div style=\"padding: 0px;\"><table style=\"width: 100%; border-collapse: collapse;\">' + data + lessonDateHtml + remainingSlotsHtml + '</table>' + textareaHtml + buttonsHtml + '</div>';

                        Swal.fire({
                            title: 'Lesson Details',
                            html: content,
                            showCloseButton: true,
                            showConfirmButton: false,
                            customClass: {
                                container: 'swal2-responsive-container',
                                popup: 'swal2-responsive-popup'
                            },
                            didOpen: function() {
                                console.log('SweetAlert2 popup opened for rowIdx:', rowIdx);
                                // Capture notes after popup is open (user can enter text)
                                // Notes will be captured in cancelLesson onclick
                            }
                        });

                        return false; // Prevent default child row rendering
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
