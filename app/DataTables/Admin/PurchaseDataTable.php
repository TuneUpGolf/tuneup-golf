<?php

namespace App\DataTables\Admin;

use App\Models\Lesson;
use App\Models\Purchase;
use App\Models\Role;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Log;

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
                \Log::info('Filtering lesson_name with keyword: ' . $keyword);
                $query->whereRaw('lessons.lesson_name LIKE ?', ['%' . $keyword . '%']);
                \Log::info('Lesson name filter query: ' . $query->toSql(), $query->getBindings());
            })
            ->filterColumn('instructor_name', function ($query, $keyword) {
                $query->whereRaw('instructors.name LIKE ?', ['%' . $keyword . '%']);
            })
            ->filterColumn('student_name', function ($query, $keyword) {
                $query->whereRaw('students.name LIKE ?', ['%' . $keyword . '%']);
            })
            ->editColumn('instructor_name', function ($purchase) {
                if ($purchase->lesson->type === Lesson::LESSON_TYPE_INPERSON) {
                    return '<span class="text-gray-400">--</span>';
                }
                $imageSrc = $purchase?->instructor?->dp
                    ? asset('/storage/' . tenant('id') . '/' . $purchase?->instructor?->dp)
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

                $url = $lesson_type == Lesson::LESSON_TYPE_ONLINE
                    ? route('purchase.feedback.index', ['purchase_id' => $purchase->id])
                    : route('purchase.show', $purchase->id);

                if (Auth::user()->type == 'Instructor') {
                    $lessonLink = '<a href="' . $url . '" class="text-blue-600 hover:underline mr-2" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="' . $lessonName . '">' . $truncatedLessonName . '</a>';
                } else {
                    $lessonLink = '<span class="text-gray-800 mr-2" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="' . $lessonName . '">' . $truncatedLessonName . '</span>';
                }

                return '<div class="flex justify-between items-center">' . $lessonLink . '</div>';
            })
            ->addColumn('pill', function ($purchase) {
                $s = Lesson::TYPE_MAPPING[$purchase->lesson->type] ?? 'N/A';
                $lesson_type = $purchase->lesson->type ?? null;
                $badgeStyle = $lesson_type == Lesson::LESSON_TYPE_ONLINE
                    ? 'background-color:#16A34A;color:white;padding:4px 12px;border-radius:9999px;display:inline-block;font-size:14px;'
                    : 'background-color:#cc8217;color:white;padding:4px 12px;border-radius:9999px;display:inline-block;font-size:14px;';
                return '<span style="' . $badgeStyle . '">' . e($s) . '</span>';
            })
            ->editColumn('student_name', function ($purchase) {
                if ($purchase->lesson->type === Lesson::LESSON_TYPE_INPERSON) {
                    return '<span class="text-gray-400">--</span>';
                }
                if (request('lesson_type') === 'inPerson') {
                    return '<span class="text-gray-600">Multiple Students</span>';
                }
                $student = \App\Models\Student::find((int)$purchase->student_id);
                $imageSrc = $purchase?->student?->dp
                    ? asset('/storage/' . tenant('id') . '/' . $student?->dp)
                    : asset('assets/img/logo/logo.png');
                return '
                    <div class="flex justify-start items-center">
                        <img src="' . $imageSrc . '" width="20" class="rounded-full"/>
                        <span class="px-0">' . e($purchase->student_name) . '</span>
                    </div>';
            })
            ->addColumn('status', function ($purchase) {
                if ($purchase->lesson->type === Lesson::LESSON_TYPE_INPERSON) {
                    return '<span class="text-gray-400">--</span>';
                }
                $s = Purchase::STATUS_MAPPING[$purchase->status] ?? 'Unknown';
                $statusStyle = $purchase->status == Purchase::STATUS_COMPLETE
                    ? 'background-color:#16A34A;color:white;padding:4px 12px;border-radius:9999px;display:inline-block;font-size:14px;'
                    : 'background-color:#DC2626;color:white;padding:4px 12px;border-radius:9999px;display:inline-block;font-size:14px;';
                return '<span style="' . $statusStyle . '">' . e($s) . '</span>';
            })
            ->editColumn('due_date', function ($purchase) {
                return Carbon::parse($purchase->created_at)->toFormattedDateString();
            })
            ->addColumn('remaining_slots', function ($purchase) {
                $lesson = $purchase->lesson;
                if (!$lesson) {
                    return '-';
                }

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
            ->rawColumns([
                'action',
                'status',
                'student_name',
                'instructor_name',
                'lesson_name',
                'pill',
                'remaining_slots'
            ]);
    }

    // public function query(Purchase $model)
    // {
    //     $user = Auth::user();

    //     try {
    //         $query = $model->with('student')->newQuery()
    //             ->leftJoin('lessons', 'purchases.lesson_id', '=', 'lessons.id')
    //             ->leftJoin('users as instructors', 'purchases.instructor_id', '=', 'instructors.id')
    //             ->leftJoin('students as students', 'purchases.student_id', '=', 'students.id');

    //         $lessonType = request('lesson_type');
    //         $lessonNameFilter = request('lesson_name_filter');

    //         // Define base subqueries
    //         $inPersonQuery = $model->newQuery()
    //             ->leftJoin('lessons', 'purchases.lesson_id', '=', 'lessons.id')
    //             ->leftJoin('users as instructors', 'purchases.instructor_id', '=', 'instructors.id')
    //             ->leftJoin('students', 'purchases.student_id', '=', 'students.id')
    //             ->where('purchases.type', Lesson::LESSON_TYPE_INPERSON);
    //         $onlinePackageQuery = $model->newQuery()
    //             ->leftJoin('lessons', 'purchases.lesson_id', '=', 'lessons.id')
    //             ->leftJoin('users as instructors', 'purchases.instructor_id', '=', 'instructors.id')
    //             ->leftJoin('students', 'purchases.student_id', '=', 'students.id')
    //             ->whereIn('purchases.type', ['online', 'package']);

    //         // Apply lesson_name filter
    //         if ($lessonNameFilter) {
    //             $inPersonQuery->whereRaw('lessons.lesson_name LIKE ?', ['%' . $lessonNameFilter . '%']);
    //             $onlinePackageQuery->whereRaw('lessons.lesson_name LIKE ?', ['%' . $lessonNameFilter . '%']);
    //             \Log::info('Applied lesson_name_filter: ' . $lessonNameFilter);
    //         }

    //         // Apply role-based filters
    //         if ($user->type === Role::ROLE_STUDENT) {
    //             $inPersonQuery->whereRaw('purchases.student_id = ?', [$user->id]);
    //             $onlinePackageQuery->whereRaw('purchases.student_id = ?', [$user->id]);
    //             $query->whereRaw('purchases.student_id = ?', [$user->id]);
    //             \Log::info('Applied student filter for user ID: ' . $user->id);
    //         }

    //         if ($user->type === Role::ROLE_INSTRUCTOR) {
    //             $inPersonQuery->whereRaw('purchases.instructor_id = ?', [$user->id]);
    //             $onlinePackageQuery->whereRaw('purchases.instructor_id = ?', [$user->id]);
    //             $query->whereRaw('purchases.instructor_id = ?', [$user->id]);
    //             \Log::info('Applied instructor filter for user ID: ' . $user->id);
    //         }

    //         if ($user->type === Role::ROLE_ADMIN) {
    //             $adminFilter = function ($q) {
    //                 $q->whereRaw(
    //                     '(EXISTS (SELECT 1 FROM lessons l WHERE l.id = purchases.lesson_id AND (l.is_package_lesson = ? OR l.type = ?) AND purchases.status = ?)) OR ' .
    //                     '(EXISTS (SELECT 1 FROM lessons l WHERE l.id = purchases.lesson_id AND l.type = ? AND l.is_package_lesson = ?) AND purchases.status IN (?, ?))',
    //                     [true, 'online', 'complete', 'inPerson', false, 'complete', 'incomplete']
    //                 );
    //             };
    //             $inPersonQuery->where($adminFilter);
    //             $onlinePackageQuery->where($adminFilter);
    //             $query->where($adminFilter);
    //             \Log::info('Applied admin filter');
    //         }

    //         // Log subqueries
    //         \Log::info('inPersonQuery: ' . $inPersonQuery->toSql(), $inPersonQuery->getBindings());
    //         \Log::info('onlinePackageQuery: ' . $onlinePackageQuery->toSql(), $onlinePackageQuery->getBindings());

    //         // Build final query based on lesson_type
    //         if ($lessonType === Lesson::LESSON_TYPE_INPERSON) {
    //             $query->selectRaw('
    //                     purchases.lesson_id,
    //                     purchases.instructor_id,
    //                     MAX(purchases.id)            AS id,
    //                     MAX(purchases.created_at)    AS created_at,
    //                     MAX(purchases.total_amount)  AS total_amount,
    //                     MAX(purchases.type)          AS purchase_type,
    //                     lessons.lesson_name          AS lesson_name,
    //                     instructors.name             AS instructor_name
    //                 ')
    //                 ->where('lessons.type', Lesson::LESSON_TYPE_INPERSON)
    //                 ->groupBy(
    //                     'purchases.lesson_id',
    //                     'purchases.instructor_id',
    //                     'lessons.lesson_name',
    //                     'instructors.name'
    //                 )
    //                 ->orderByDesc('created_at');
    //         } elseif ($lessonType === Lesson::LESSON_TYPE_ONLINE) {
    //             $query->select([
    //                     'purchases.*',
    //                     'purchases.type as purchase_type',
    //                     'lessons.lesson_name as lesson_name',
    //                     'instructors.name as instructor_name',
    //                     'students.name as student_name',
    //                 ])
    //                 ->where('lessons.type', Lesson::LESSON_TYPE_ONLINE)
    //                 ->orderByDesc('purchases.created_at');
    //         } elseif ($lessonType === Lesson::LESSON_TYPE_PACKAGE) {
    //             $query->select([
    //                     'purchases.*',
    //                     'purchases.type as purchase_type',
    //                     'lessons.lesson_name as lesson_name',
    //                     'instructors.name as instructor_name',
    //                     'students.name as student_name',
    //                 ])
    //                 ->where('lessons.type', Lesson::LESSON_TYPE_PACKAGE)
    //                 ->orderByDesc('purchases.created_at');
    //         } elseif ($lessonType === null) {
    //             $inPersonQuery->selectRaw('
    //                 purchases.lesson_id,
    //                 purchases.instructor_id,
    //                 MAX(purchases.id) AS id,
    //                 MAX(purchases.created_at) AS created_at,
    //                 MAX(purchases.total_amount) AS total_amount,
    //                 ANY_VALUE(purchases.student_id) AS student_id,
    //                 ANY_VALUE(students.name) AS student_name,
    //                 MAX(purchases.type) AS purchase_type,
    //                 MAX(purchases.status) AS status,
    //                 lessons.lesson_name AS lesson_name,
    //                 instructors.name AS instructor_name
    //             ')
    //             ->groupBy(
    //                 'purchases.lesson_id',
    //                 'purchases.instructor_id',
    //                 'lessons.lesson_name',
    //                 'instructors.name'
    //             );

    //             $onlinePackageQuery->selectRaw('
    //                 purchases.lesson_id,
    //                 purchases.instructor_id,
    //                 purchases.id,
    //                 purchases.created_at,
    //                 purchases.total_amount,
    //                 purchases.student_id,
    //                 students.name AS student_name,
    //                 purchases.type AS purchase_type,
    //                 purchases.status,
    //                 lessons.lesson_name,
    //                 instructors.name AS instructor_name
    //             ');

    //             $query = $inPersonQuery->unionAll($onlinePackageQuery)
    //                 ->orderByDesc('created_at');
    //         } else {
    //             $inPersonQuery->selectRaw('
    //                 purchases.lesson_id,
    //                 purchases.instructor_id,
    //                 MAX(purchases.id) AS id,
    //                 MAX(purchases.created_at) AS created_at,
    //                 MAX(purchases.total_amount) AS total_amount,
    //                 ANY_VALUE(purchases.student_id) AS student_id,
    //                 ANY_VALUE(students.name) AS student_name,
    //                 MAX(purchases.type) AS purchase_type,
    //                 MAX(purchases.status) AS status,
    //                 lessons.lesson_name AS lesson_name,
    //                 instructors.name AS instructor_name
    //             ')
    //             ->groupBy(
    //                 'purchases.lesson_id',
    //                 'purchases.instructor_id',
    //                 'lessons.lesson_name',
    //                 'instructors.name'
    //             );

    //             $onlinePackageQuery->selectRaw('
    //                 purchases.lesson_id,
    //                 purchases.instructor_id,
    //                 purchases.id,
    //                 purchases.created_at,
    //                 purchases.total_amount,
    //                 purchases.student_id,
    //                 students.name AS student_name,
    //                 purchases.type AS purchase_type,
    //                 purchases.status,
    //                 lessons.lesson_name,
    //                 instructors.name AS instructor_name
    //             ');

    //             $query = $inPersonQuery->unionAll($onlinePackageQuery)
    //                 ->orderByDesc('created_at');
    //         }

    //         \Log::info('Final query: ' . $query->toSql(), $query->getBindings());

    //         return $query;
    //     } catch (QueryException $e) {
    //         \Log::error('Query error: ' . $e->getMessage(), [
    //             'sql' => $query->toSql(),
    //             'bindings' => $query->getBindings(),
    //             'inPersonQuery' => $inPersonQuery->toSql(),
    //             'inPersonBindings' => $inPersonQuery->getBindings(),
    //             'onlinePackageQuery' => $onlinePackageQuery->toSql(),
    //             'onlinePackageBindings' => $onlinePackageQuery->getBindings(),
    //         ]);
    //         throw $e;
    //     }
    // }
    public function query(Purchase $model)
    {
        $user = Auth::user();

        try {
            $query = $model->with('student')->newQuery()
                ->leftJoin('lessons', 'purchases.lesson_id', '=', 'lessons.id')
                ->leftJoin('users as instructors', 'purchases.instructor_id', '=', 'instructors.id')
                ->leftJoin('students as students', 'purchases.student_id', '=', 'students.id');

            $lessonType = request('lesson_type');
            $lessonNameFilter = request('lesson_name_filter');

            // Apply lesson_name filter to the main query
            if ($lessonNameFilter) {
                $query->whereRaw('lessons.lesson_name LIKE ?', ['%' . $lessonNameFilter . '%']);
                Log::info('Applied lesson_name_filter to main query: ' . $lessonNameFilter);
            }

            // Apply role-based filters
            if ($user->type === Role::ROLE_STUDENT) {
                $query->whereRaw('purchases.student_id = ?', [$user->id]);
                Log::info('Applied student filter for user ID: ' . $user->id);
            }

            if ($user->type === Role::ROLE_INSTRUCTOR) {
                $query->whereRaw('purchases.instructor_id = ?', [$user->id]);
                Log::info('Applied instructor filter for user ID: ' . $user->id);
            }

            if ($user->type === Role::ROLE_ADMIN) {
                $query->whereRaw(
                    '(EXISTS (SELECT 1 FROM lessons l WHERE l.id = purchases.lesson_id AND (l.is_package_lesson = ? OR l.type = ?) AND purchases.status = ?)) OR ' .
                    '(EXISTS (SELECT 1 FROM lessons l WHERE l.id = purchases.lesson_id AND l.type = ? AND l.is_package_lesson = ?) AND purchases.status IN (?, ?))',
                    [true, 'online', 'complete', 'inPerson', false, 'complete', 'incomplete']
                );
                Log::info('Applied admin filter');
            }

            // Build query based on lesson_type
            if ($lessonType === Lesson::LESSON_TYPE_INPERSON) {
                $query->selectRaw('
                        purchases.lesson_id,
                        purchases.instructor_id,
                        MAX(purchases.id) AS id,
                        MAX(purchases.created_at) AS created_at,
                        MAX(purchases.total_amount) AS total_amount,
                        MAX(purchases.type) AS purchase_type,
                        lessons.lesson_name AS lesson_name,
                        instructors.name AS instructor_name
                    ')
                    ->where('lessons.type', Lesson::LESSON_TYPE_INPERSON)
                    ->groupBy(
                        'purchases.lesson_id',
                        'purchases.instructor_id',
                        'lessons.lesson_name',
                        'instructors.name'
                    )
                    ->orderByDesc('created_at');
            } elseif ($lessonType === Lesson::LESSON_TYPE_ONLINE) {
                $query->select([
                        'purchases.*',
                        'purchases.type as purchase_type',
                        'lessons.lesson_name as lesson_name',
                        'instructors.name as instructor_name',
                        'students.name as student_name',
                    ])
                    ->where('lessons.type', Lesson::LESSON_TYPE_ONLINE)
                    ->orderByDesc('purchases.created_at');
            } elseif ($lessonType === Lesson::LESSON_TYPE_PACKAGE) {
                $query->select([
                        'purchases.*',
                        'purchases.type as purchase_type',
                        'lessons.lesson_name as lesson_name',
                        'instructors.name as instructor_name',
                        'students.name as student_name',
                    ])
                    ->where('lessons.type', Lesson::LESSON_TYPE_PACKAGE)
                    ->orderByDesc('purchases.created_at');
            } else {
                // For no lesson_type or invalid lesson_type, use union of inPerson and online/package
                $inPersonQuery = $model->newQuery()
                    ->leftJoin('lessons', 'purchases.lesson_id', '=', 'lessons.id')
                    ->leftJoin('users as instructors', 'purchases.instructor_id', '=', 'instructors.id')
                    ->leftJoin('students', 'purchases.student_id', '=', 'students.id')
                    ->where('purchases.type', Lesson::LESSON_TYPE_INPERSON);

                $onlinePackageQuery = $model->newQuery()
                    ->leftJoin('lessons', 'purchases.lesson_id', '=', 'lessons.id')
                    ->leftJoin('users as instructors', 'purchases.instructor_id', '=', 'instructors.id')
                    ->leftJoin('students', 'purchases.student_id', '=', 'students.id')
                    ->whereIn('purchases.type', ['online', 'package']);

                // Apply lesson_name filter to subqueries
                if ($lessonNameFilter) {
                    $inPersonQuery->whereRaw('lessons.lesson_name LIKE ?', ['%' . $lessonNameFilter . '%']);
                    $onlinePackageQuery->whereRaw('lessons.lesson_name LIKE ?', ['%' . $lessonNameFilter . '%']);
                    Log::info('Applied lesson_name_filter to subqueries: ' . $lessonNameFilter);
                }

                // Apply role-based filters to subqueries
                if ($user->type === Role::ROLE_STUDENT) {
                    $inPersonQuery->whereRaw('purchases.student_id = ?', [$user->id]);
                    $onlinePackageQuery->whereRaw('purchases.student_id = ?', [$user->id]);
                }

                if ($user->type === Role::ROLE_INSTRUCTOR) {
                    $inPersonQuery->whereRaw('purchases.instructor_id = ?', [$user->id]);
                    $onlinePackageQuery->whereRaw('purchases.instructor_id = ?', [$user->id]);
                }

                if ($user->type === Role::ROLE_ADMIN) {
                    $adminFilter = function ($q) {
                        $q->whereRaw(
                            '(EXISTS (SELECT 1 FROM lessons l WHERE l.id = purchases.lesson_id AND (l.is_package_lesson = ? OR l.type = ?) AND purchases.status = ?)) OR ' .
                            '(EXISTS (SELECT 1 FROM lessons l WHERE l.id = purchases.lesson_id AND l.type = ? AND l.is_package_lesson = ?) AND purchases.status IN (?, ?))',
                            [true, 'online', 'complete', 'inPerson', false, 'complete', 'incomplete']
                        );
                    };
                    $inPersonQuery->where($adminFilter);
                    $onlinePackageQuery->where($adminFilter);
                }

                $inPersonQuery->selectRaw('
                    purchases.lesson_id,
                    purchases.instructor_id,
                    MAX(purchases.id) AS id,
                    MAX(purchases.created_at) AS created_at,
                    MAX(purchases.total_amount) AS total_amount,
                    ANY_VALUE(purchases.student_id) AS student_id,
                    ANY_VALUE(students.name) AS student_name,
                    MAX(purchases.type) AS purchase_type,
                    MAX(purchases.status) AS status,
                    lessons.lesson_name AS lesson_name,
                    instructors.name AS instructor_name
                ')
                ->groupBy(
                    'purchases.lesson_id',
                    'purchases.instructor_id',
                    'lessons.lesson_name',
                    'instructors.name'
                );

                $onlinePackageQuery->selectRaw('
                    purchases.lesson_id,
                    purchases.instructor_id,
                    purchases.id,
                    purchases.created_at,
                    purchases.total_amount,
                    purchases.student_id,
                    students.name AS student_name,
                    purchases.type AS purchase_type,
                    purchases.status,
                    lessons.lesson_name,
                    instructors.name AS instructor_name
                ');

                $query = $inPersonQuery->unionAll($onlinePackageQuery)
                    ->orderByDesc('created_at');
            }

            Log::info('Final query: ' . $query->toSql(), $query->getBindings());

            return $query;
        } catch (QueryException $e) {
            Log::error('Query error: ' . $e->getMessage(), [
                'sql' => $query->toSql(),
                'bindings' => $query->getBindings(),
                'inPersonQuery' => isset($inPersonQuery) ? $inPersonQuery->toSql() : 'N/A',
                'inPersonBindings' => isset($inPersonQuery) ? $inPersonQuery->getBindings() : [],
                'onlinePackageQuery' => isset($onlinePackageQuery) ? $onlinePackageQuery->toSql() : 'N/A',
                'onlinePackageBindings' => isset($onlinePackageQuery) ? $onlinePackageQuery->getBindings() : [],
            ]);
            throw $e;
        }
    }

    public function html()
    {
        $lessonTypeFilter = "<select id='lessonTypeFilter' class='form-select' style='margin-left:auto;max-width:300px;'><option value='null'>- Lesson Type -</option>";
        foreach (Lesson::SELECT_TYPE_MAPPING as $key => $label) {
            $selected = request('lesson_type') === $key ? 'selected' : '';
            $lessonTypeFilter .= "<option value='" . $key . "' " . $selected . ">" . $label . "</option>";
        }
        $lessonTypeFilter .= "</select>";

        $lessonNameFilter = "<input type='text' id='lessonNameFilter' class='form-control' style='max-width:300px;margin-right:10px;margin-left:10px;' placeholder='Filter by Lesson Name'>";

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
                var api = this.api();
                var searchInput = $("#" + api.table().container().id + " label input[type=search]");
                searchInput.removeClass("form-control form-control-sm").addClass("dataTable-input");
                $(api.table().container()).find(".dataTables_length select")
                    .removeClass("custom-select custom-select-sm form-control form-control-sm")
                    .addClass("dataTable-selector");

                $(".dataTable-search").prepend("' . $lessonNameFilter . '");
                $(".dataTable-search").prepend("' . $lessonTypeFilter . '");
                $(".dataTable-search").addClass("d-flex");

                var studentCol = api.column("student_name:name");
                var instructorCol = api.column("instructor_name:name");
                var statusCol = api.column("status:name");
                var remainingSlotsCol = api.column("remaining_slots:name");

                var initialLessonType = "' . addslashes(request('lesson_type', '')) . '" || $("#lessonTypeFilter").val();
                if (initialLessonType === "inPerson") {
                    studentCol.visible(false);
                    instructorCol.visible(false);
                    statusCol.visible(false);
                    remainingSlotsCol.visible(false);
                } else if (initialLessonType === "package") {
                    studentCol.visible(true);
                    instructorCol.visible(true);
                    statusCol.visible(true);
                    remainingSlotsCol.visible(true);
                } else {
                    studentCol.visible(true);
                    instructorCol.visible(true);
                    statusCol.visible(true);
                    remainingSlotsCol.visible(false);
                }

                $("#lessonTypeFilter").on("change", function() {
                    var val = $(this).val();
                    if (val === "inPerson") {
                        studentCol.visible(false);
                        instructorCol.visible(false);
                        statusCol.visible(false);
                        remainingSlotsCol.visible(false);
                    } else if (val === "package") {
                        studentCol.visible(true);
                        instructorCol.visible(true);
                        statusCol.visible(true);
                        remainingSlotsCol.visible(true);
                    } else {
                        studentCol.visible(true);
                        instructorCol.visible(true);
                        statusCol.visible(true);
                        remainingSlotsCol.visible(false);
                    }
                    api.ajax.reload();
                });

                $("#lessonNameFilter").on("input", function() {
                    api.ajax.reload();
                });

                $("#purchases-table").DataTable().on("preXhr.dt", function(e, settings, data) {
                    var lessonType = $("#lessonTypeFilter").val();
                    var lessonName = $("#lessonNameFilter").val();
                    data.lesson_type = lessonType !== "inPerson" ? lessonType : "inPerson";
                    data.lesson_name_filter = lessonName;
                });
            }')
            ->parameters([
                'columnDefs' => [
                    ['responsivePriority' => 1, 'targets' => 1],
                    ['responsivePriority' => 2, 'targets' => 3],
                ],
                'dom' => <<<'DOM'
            <'dataTable-top row'
                <'dataTable-title col-xl-7 col-lg-3 col-sm-6 d-none d-sm-block'>
                <'dataTable-search dataTable-search tb-search col-md-5 col-sm-6 col-lg-6 col-xl-5 col-sm-12 d-flex'f>>
            <'dataTable-container'<'col-sm-12'tr>>
            <'dataTable-bottom row'
                <'dataTable-dropdown page-dropdown col-lg-2 col-sm-12'l>
                <'col-sm-7'p>>
            DOM
            ,
            'buttons' => $buttons,
            'scrollX' => true,
            'responsive' => [
                'scrollX' => false,
                'details' => [
                    'display' => '$.fn.dataTable.Responsive.display.childRow',
                    'renderer' => <<<'JS'
                    function (api, rowIdx, columns) {
                        var data = $('<table/>').addClass('vertical-table');
                        $.each(columns, function (i, col) {
                            data.append('<tr><td><strong>' + col.title + '</strong></td><td>' + col.data + '</td></tr>');
                        });
                        return data;
                    }
                    JS
                ]
            ],
            'rowCallback' => <<<'JS'
            function(row){
                $('td', row).css({'font-family':'Helvetica','font-weight':'300'});
            }
            JS,
            'drawCallback' => <<<'JS'
            function(settings){
                var tooltipTriggerList = [].slice.call(document.querySelectorAll("[data-bs-toggle=tooltip]"));
                tooltipTriggerList.map(function(el){
                    return new bootstrap.Tooltip(el, { delay: { show: 100, hide: 200 }, trigger: "hover" });
                });
            }
            JS
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
            Column::make('id')->title(__('Lesson #'))->searchable(true)->orderable(true)->width('80px'),
            Column::make('lesson_name')->title(__('Lesson Title'))->searchable(true)->width('250px'),
            Column::make('pill')->title(__('Type'))->searchable(false)->orderable(false),
            Column::make('remaining_slots')
                ->title(__('Remaining Slots'))
                ->orderable(false)
                ->searchable(false)
                ->addClass('text-center'),
        ];

        $lessonType = request('lesson_type');

        if ($lessonType !== 'inPerson') {
            if (Auth::user()->type == Role::ROLE_INSTRUCTOR) {
                $columns[] = Column::make('student_name')
                    ->name('student_name')
                    ->title(__('Student'))
                    ->searchable(true);
                $columns[] = Column::make('instructor_name')
                    ->name('instructor_name')
                    ->title(__('Instructor'))
                    ->searchable(true);
            } elseif (Auth::user()->type == Role::ROLE_STUDENT) {
                $columns[] = Column::make('instructor_name')
                    ->name('instructor_name')
                    ->title(__('Instructor'))
                    ->searchable(true);
            }

            $columns[] = Column::make('status')
                ->name('status')
                ->title(__('Payment Status'));
        }

        return array_merge($columns, [
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