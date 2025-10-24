<?php

namespace App\Http\Controllers\Admin;

use Error;
use Exception;
use DatePeriod;
use DateInterval;
use Carbon\Carbon;
use App\Models\Role;
use App\Models\User;
use App\Models\Slots;
use App\Models\Lesson;
use App\Models\Student;
use App\Models\Purchase;
use App\Actions\SendEmail;
use App\Models\Instructor;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\PackageLesson;
use App\Traits\PurchaseTrait;
use App\Facades\UtilityFacades;
use Illuminate\Support\Facades\DB;
use App\Jobs\SendLessonReminderJob;
use App\Models\InstructorBlockSlot;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Actions\SendPushNotification;
use App\Mail\Admin\SlotCancelledMail;
use App\Mail\Admin\StudentPaymentLink;
use App\Http\Resources\SlotAPIResource;
use Illuminate\Support\Facades\Storage;
use App\DataTables\Admin\LessonDataTable;
use App\Http\Resources\LessonAPIResource;
use Stancl\Tenancy\Database\Models\Domain;
use App\Mail\Admin\SlotBookedByStudentMail;
use Illuminate\Validation\ValidationException;

class LessonController extends Controller
{
    use PurchaseTrait;


    public function index(Request $request)
    {
        // dd("Sd");
        if (Auth::user()->can('manage-lessons')) {
            if ($request->ajax()) {
                $model = new Lesson();
                if (tenant('id') == null) {
                    $lessons = $model->newQuery()
                        ->select(['lessons.*', 'domains.domain'])
                        ->join('domains', 'domains.tenant_id', '=', 'users.tenant_id')
                        ->where('type', 'Admin')
                        ->orderBy('column_order', 'asc')
                        ->get();
                } elseif (Auth::user()->type == Role::ROLE_ADMIN || Auth::user()->type == Role::ROLE_STUDENT) {
                    $lessons = $model->newQuery()
                        ->where('lessons.tenant_id', tenant('id'))
                        ->where('lessons.active_status', true)
                        ->orderBy('column_order', 'asc')
                        ->get();
                } else {
                    $lessons = $model->newQuery()
                        ->where('lessons.active_status', true)
                        ->where('lessons.created_by', Auth::user()->id)
                        ->orderBy('column_order', 'asc')
                        ->get();
                }
                return datatables()
                    ->of($lessons)
                    ->addIndexColumn()
                    ->editColumn('created_at', fn($lesson) => UtilityFacades::date_time_format($lesson->created_at))
                    ->editColumn('lesson_price', fn($lesson) => UtilityFacades::amount_format($lesson->lesson_price))
                    ->editColumn('created_by', function ($lesson) {
                        $imageSrc = $lesson?->user?->dp
                            ? asset('/storage/' . tenant('id') . '/' . $lesson?->user?->dp)
                            : asset('assets/img/logo/logo.png');

                        return '
                    <div class="flex justify-start items-center">
                        <img src="' . $imageSrc . '" width="20" class="rounded-full"/>
                        <span class="px-2">' . e($lesson->user->name ?? 'Unknown') . '</span>
                    </div>';
                    })
                    ->editColumn('type', function ($lesson) {
                        $s = Lesson::TYPE_MAPPING[$lesson->type] ?? ucfirst($lesson->type);

                        if ($lesson->type == Lesson::LESSON_TYPE_INPERSON)
                            // $s .= ' - PL';
                            return '<label class="badge rounded-pill bg-cyan-600 p-2 px-3">' . 'Pre-sets Date Lesson' . '</label>';

                        if ($lesson->type == Lesson::LESSON_TYPE_ONLINE) {
                            return '<label class="badge rounded-pill bg-green-600 p-2 px-3">' . $s . '</label>';
                        }
                        if ($lesson->type == Lesson::LESSON_TYPE_PACKAGE) {
                            return '<label class="badge rounded-pill bg-yellow-600 p-2 px-3">' . $s . '</label>';
                        }
                        return '<label class="badge rounded-pill bg-yellow-600 p-2 px-3">' . $s . '</label>';
                    })
                    ->addColumn('action', fn($lesson) => view('admin.lessons.action', compact('lesson'))->render())
                    ->rawColumns(['action', 'created_by', 'type'])
                    ->make(true);
            }
            return view('admin.lessons.index');
        } else {
            return redirect()->back()->with('failed', __('Permission denied.'));
        }
    }

    public function reorder(Request $request)
    {

        foreach ($request->order as $item) {
            Lesson::where('id', $item['id'])
                ->update(['column_order' => $item['position']]);
        }

        return response()->json(['success' => true]);
    }





    public function create()
    {
        if (Auth::user()->can('create-lessons')) {
            if (Auth::user()->type == 'Admin' || Auth::user()->type == 'Instructor') {
                $roles      = Role::where('name', '!=', 'Super Admin')->where('name', '!=', 'Admin')->pluck('name', 'name');
                $domains    = Domain::pluck('domain', 'domain')->all();
            } else {
                $roles      = Role::where('name', '!=', 'Admin')->where('name', Auth::user()->type)->pluck('name', 'name');
                $domains    = Domain::pluck('domain', 'domain')->all();
            }
            if (request()->get('type') === Lesson::LESSON_TYPE_ONLINE) {
                return view('admin.lessons.create', compact('roles', 'domains'));
            }
            if (request()->get('type') === Lesson::LESSON_TYPE_INPERSON) {
                return view('admin.lessons.inperson', compact('roles', 'domains'));
            }
        } else {
            return redirect()->back()->with('failed', __('Permission denied.'));
        }
    }

    // Method to create a new lesson
    public function store(Request $request)
    {
        if ($request->type === Lesson::LESSON_PAYMENT_ONLINE) {
            $validatedData = $request->validate(
                [
                    'lesson_name'          => 'required|string|max:255',
                    'long_description'   => 'string',
                    'lesson_description' => [
                        'required',
                        'string',
                        function ($attribute, $value, $fail) {
                            // Remove tags & invisible characters
                            $clean = trim(preg_replace('/\x{200B}+/u', '', strip_tags($value)));

                            if ($clean === '') {
                                $fail('The short description is required.');
                            }
                        },
                    ],
                    'lesson_price'         => 'required|numeric',
                    'lesson_quantity'      => 'required|integer',
                    'required_time'        => 'required|integer',
                ],
                [
                    'lesson_description.required' => 'The short description is required.',

                ]
            );
        }
        if ($request->type === Lesson::LESSON_TYPE_INPERSON) {
            $validatedData = $request->validate(
                [
                    'lesson_name'          => 'required|string|max:255',
                    'long_description'      =>    'string',
                    'lesson_description'   => 'required|string',
                    'lesson_price'         => 'required_if:is_package_lesson,0|numeric',
                    'lesson_duration'      => 'required|numeric',
                    'payment_method'       => ['required', 'in:online,cash'],
                    'slots'                => 'array',
                    'max_students'         => 'required|integer|min:1',
                    'is_package_lesson'    => 'string',
                ],
                [
                    'lesson_description.required' => 'The short description is required.',
                ]
            );
            $validatedData['lesson_quantity'] = 1;
            $validatedData['required_time'] = 0;
            !empty($validatedData['is_package_lesson']) && $validatedData['is_package_lesson'] == 1 ? $validatedData['is_package_lesson'] = true : $validatedData['is_package_lesson'] = false;
        }
        // Assuming 'created_by' is the ID of the currently authenticated instructor
        $validatedData['long_description'] = $_POST['long_description'] != "" ? $_POST['long_description'] : NULL;
        $validatedData['lesson_description'] = $_POST['lesson_description'] != "" ? $_POST['lesson_description'] : NULL;
        $validatedData['created_by'] = Auth::user()->id;
        $validatedData['type'] = ($request->is_package_lesson == 1) ? 'package' : $request->type;
        $validatedData['payment_method'] = $request->payment_method ?? Lesson::LESSON_PAYMENT_ONLINE;
        $validatedData['tenant_id'] = Auth::user()->tenant_id;
        $validatedData['lesson_price'] = $request->lesson_price ?? 0;
        $lesson = Lesson::create($validatedData);

        $students = Student::whereHas('pushToken')
            ->with('pushToken')
            ->get()
            ->pluck('pushToken.token')
            ->toArray();

        if ($request->hasFile('logo')) {
            $tenant_id = Auth::user()->tenant_id;
            $lesson_id = $lesson->id;

            $path = "lessons/$lesson_id";
            $file = $request->file('logo');

            // Generate unique filename
            $originalName = $file->getClientOriginalName();
            $fileName = uniqid() . '_' . time() . '_' . $originalName;

            // Store file in storage/app/{tenant_id}/{lesson_id}/
            $filePath = $file->storeAs($path, $fileName, 'local');

            // Save file path in database
            $lesson->logo = $filePath;
            $lesson->save();
        }

        if ($request->is_package_lesson == 1 && !empty($request->package_lesson)) {
            foreach ($request->package_lesson as $packages) {
                PackageLesson::create([
                    'tenant_id' => Auth::user()->tenant_id,
                    'lesson_id' => $lesson->id,
                    'number_of_slot' => $packages['no_of_slot'],
                    'price' => $packages['price'],
                ]);
            }
        }
        if (!empty($students)) {
            $title = "New Lesson Available!";
            $body = Auth::user()->name . " has created a new lesson: " . $lesson->lesson_name;
            SendPushNotification::dispatch($students, $title, $body);
        }

        return redirect()->route('lesson.index', $lesson)->with('success', 'Lesson created successfully.');
    }

    public function update(Request $request, $lessonId)
    {
        // dd($request->all());
        $lesson = Lesson::findOrFail($lessonId);
        $validatedData = $request->validate([
            'lesson_name'          => 'required|string|max:255',
            'long_description'      => 'string',
            // 'lesson_description'   => 'required|string',
            'lesson_description' => [
                'required',
                'string',
                function ($attribute, $value, $fail) {
                    // Remove tags & invisible characters
                    $clean = trim(preg_replace('/\x{200B}+/u', '', strip_tags($value)));

                    if ($clean === '') {
                        $fail('The short description is required.');
                    }
                },
            ],
            'lesson_price'         => 'required_if:is_package_lesson,1|numeric',
            'lesson_quantity'      => 'integer',
            'required_time'        => 'integer',
            'lesson_duration'      => 'numeric',
            'payment_method'       => 'in:online,cash',
            'max_students'         => 'integer|min:1',
        ], [
            'lesson_description.required' => 'The short description is required.',

        ]);

        // Assuming 'created_by' is the ID of the currently authenticated instructor
        $validatedData['created_by'] = Auth::user()->id;
        $validatedData['long_description'] = $_POST['long_description'] != "" ? $_POST['long_description'] : NULL;
        $validatedData['lesson_description'] = $_POST['lesson_description'] != "" ? $_POST['lesson_description'] : NULL;
        if ($lesson->is_package_lesson == 1) {
            $validatedData['lesson_duration'] = $_POST['lesson_duration'] != "" ? $_POST['lesson_duration'] : NULL;
            $validatedData['max_students'] = $_POST['max_students'] != "" ? $_POST['max_students'] : NULL;
        }

        $lesson->update($validatedData);

        if ($request->hasFile('logo')) {
            $tenant_id = Auth::user()->tenant_id;
            $lesson_id = $lesson->id;

            $path = "lessons/$lesson_id";
            $file = $request->file('logo');

            // Generate unique filename
            $originalName = $file->getClientOriginalName();
            $fileName = uniqid() . '_' . time() . '_' . $originalName;

            // Store file in storage/app/{tenant_id}/{lesson_id}/
            $filePath = $file->storeAs($path, $fileName, 'local');

            // Save file path in database
            $lesson->logo = $filePath;
            $lesson->save();
        }

        if ($lesson->is_package_lesson == 1) {
            if (!empty($request->exist_package_lesson)) {
                foreach ($request->exist_package_lesson as $packages) {
                    PackageLesson::where('id', $packages['id'])
                        ->where('lesson_id', $lessonId) // extra safety
                        ->update([
                            'number_of_slot' => $packages['no_of_slot'],
                            'price'          => $packages['price'],
                        ]);
                }
            }
            if (!empty($request->package_lesson)) {
                foreach ($request->package_lesson as $packages) {
                    PackageLesson::create([
                        'tenant_id' => Auth::user()->tenant_id,
                        'lesson_id' => $lessonId,
                        'number_of_slot' => $packages['no_of_slot'],
                        'price' => $packages['price'],
                    ]);
                }
            }
        }
        return redirect()->route('lesson.index', $lesson)->with('success', 'Lesson updated successfully.');
    }

    public function edit($id)
    {
        if (Auth::user()->can('edit-lessons')) {
            $user   = Lesson::with('packages')->find($id);
            if (Auth::user()->type == 'Admin') {
                $roles      = Role::where('name', '!=', 'Super Admin')->where('name', '!=', 'Admin')->pluck('name', 'name');
                $domains    = Domain::pluck('domain', 'domain')->all();
            } else {
                $roles      = Role::where('name', '!=', 'Admin')->where('name', Auth::user()->type)->pluck('name', 'name');
                $domains    = Domain::pluck('domain', 'domain')->all();
            }
            return view('admin.lessons.edit', compact('user', 'roles', 'domains'));
        } else {
            return redirect()->back()->with('failed', __('Permission denied.'));
        }
    }

    public function availableLessons()
    {
        if (Auth::user()->can('manage-lessons')) {
            return view('admin.lessons.available');
        }
    }

    public function createSlot(Request $request)
    {
        if (Auth::user()->can('create-lessons')) {
            $lesson = Lesson::find($request->get('lesson_id'));
            if ($lesson) {
                return view('admin.lessons.addSlot', compact('lesson'));
            } else {
                $lesson = Lesson::withMax('packages', 'number_of_slot')->whereIn('type', ['package', 'inPerson'])->where('created_by', Auth::user()->id)->where('active_status', true)->get()->toArray();
                return view('admin.lessons.setAvailability', compact('lesson'));
            }
        } else {
            return redirect()->back()->with('failed', __('Permission denied.'));
        }
    }

    public function deleteBlockSlots(Request $request)
    {
        InstructorBlockSlot::find($request->id)->delete();
        return response()->json([
            'status'  => true,
            'message' => 'Slot block deleted successfully',
        ]);
    }

    public function blockSlots1(Request $request)
    {
        $validated = $request->validate([
            'start_time' => 'required|date',
            'end_time'   => 'required|date|after:start_time',
            'reason'     => 'required|string|max:5000',
        ]);

        // 2. Get the authenticated instructor
        $instructor = auth()->user();

        // 3. Store in DB
        $slot = new InstructorBlockSlot();
        $slot->instructor_id = $instructor->id;
        $slot->start_time    = $validated['start_time'];
        $slot->end_time      = $validated['end_time'];
        $slot->description   = $validated['reason'];
        $slot->save();

        // 4. Return success response
        return response()->json([
            'status'  => true,
            'message' => 'Slot blocked successfully',
            'data'    => $slot
        ]);
    }

    public function blockSlots(Request $request)
    {
        $validated = $request->validate([
            'start_time' => 'required|date',
            'end_time'   => 'required|date|after:start_time',
            'reason'     => 'required|string|max:5000',
        ]);

        // Get the authenticated instructor
        $instructor = auth()->user();

        // Convert to Carbon instances for easier comparison
        $blockStart = Carbon::parse($validated['start_time']);
        $blockEnd = Carbon::parse($validated['end_time']);

        // Check for conflicts with existing scheduled slots (slots with students)
        $scheduledConflict = Slots::whereHas('student') // Only check slots that have students (scheduled)
            ->where(function($query) use ($blockStart, $blockEnd) {
                $query->whereBetween('date_time', [$blockStart, $blockEnd->subMinute()])
                    ->orWhere(function($q) use ($blockStart, $blockEnd) {
                        $q->where('date_time', '<', $blockStart)
                            ->whereRaw('DATE_ADD(date_time, INTERVAL (SELECT lesson_duration FROM lessons WHERE lessons.id = slots.lesson_id) * 60 MINUTE) > ?', [$blockStart]);
                    });
            })
            ->exists();

        if ($scheduledConflict) {
            return response()->json([
                'status'  => false,
                'message' => 'Cannot block this time range because there are scheduled lessons during this period.'
            ], 409);
        }

        // Check for conflicts with existing availability slots (slots without students)
        $availabilityConflict = Slots::whereDoesntHave('student') // Only check slots without students (availability)
            ->whereBetween('date_time', [$blockStart, $blockEnd->subMinute()])
            ->exists();

        if ($availabilityConflict) {
            return response()->json([
                'status'  => false,
                'message' => 'Cannot block this time range because there are availability slots during this period. Please remove the availability slots first.'
            ], 409);
        }

        // Check for conflicts with existing blocked slots
        $blockedConflict = InstructorBlockSlot::where('instructor_id', $instructor->id)
            ->where(function($query) use ($blockStart, $blockEnd) {
                $query->whereBetween('start_time', [$blockStart, $blockEnd])
                    ->orWhereBetween('end_time', [$blockStart, $blockEnd])
                    ->orWhere(function($q) use ($blockStart, $blockEnd) {
                        $q->where('start_time', '<=', $blockStart)
                            ->where('end_time', '>=', $blockEnd);
                    });
            })
            ->exists();

        if ($blockedConflict) {
            return response()->json([
                'status'  => false,
                'message' => 'This time range overlaps with an existing blocked slot.'
            ], 409);
        }

        // Store in DB if no conflicts
        $slot = new InstructorBlockSlot();
        $slot->instructor_id = $instructor->id;
        $slot->start_time    = $validated['start_time'];
        $slot->end_time      = $validated['end_time'];
        $slot->description   = $validated['reason'];
        $slot->save();

        // Return success response
        return response()->json([
            'status'  => true,
            'message' => 'Slot blocked successfully',
            'data'    => $slot
        ]);
    }
    public function manageSlots()
    {
        // dd("sd");
        $type = Auth::user()->type;
        if ($type === Role::ROLE_ADMIN) {
            // Handle both single lesson_id and multiple lesson_ids
            $lessonIds = request()->input('lesson_ids', []);
            $singleLessonId = request()->input('lesson_id');
            
            // If no multiple lessons selected, check for single lesson selection
            if (empty($lessonIds) && $singleLessonId && $singleLessonId !== "-1") {
                $lessonIds = [$singleLessonId];
            }
            
            // Get payment method from first selected lesson (or null if none selected)
            $payment_method = !empty($lessonIds) ? Lesson::find($lessonIds[0])?->payment_method : null;
            
            $events = [];
            $resources = [];
            $instructorId = request()->get('instructor_id');

            $slotsQuery = Slots::with(['lesson.user', 'student'])->where('is_active', true);

            // Filter by instructor if selected
            if (!!$instructorId && $instructorId !== "-1") {
                $slotsQuery->whereHas('lesson', function ($query) use ($instructorId) {
                    $query->where('created_by', $instructorId);
                });
            }

            // Filter by lessons if any are selected
            if (!empty($lessonIds)) {
                $slotsQuery->whereHas('lesson', function ($query) use ($lessonIds) {
                    $query->whereIn('id', $lessonIds);
                });
            }

            $slots = $slotsQuery->get();

            $bookedSlots = $slots->filter(function ($slot) {
                return $slot->student->isNotEmpty();
            });

            $bookedTimeRanges = [];
            foreach ($bookedSlots as $bookedSlot) {
                $startDateTime = Carbon::parse($bookedSlot->date_time);
                $duration = $bookedSlot->lesson->lesson_duration;
                $whole = floor($duration);
                $fraction = $duration - $whole;
                $minutes = $fraction * 60;
                $endDateTime = $startDateTime->copy()->addHours($whole)->addMinutes($minutes);

                $bookedTimeRanges[] = [
                    'start' => $startDateTime,
                    'end' => $endDateTime,
                    'slot_id' => $bookedSlot->id
                ];
            }

            $filteredSlots = $slots->filter(function ($slot) use ($bookedTimeRanges) {

                if ($slot->lesson == null) {
                    return false;
                }

                if ($slot->student->isNotEmpty()) {
                    return true;
                }

                $slotStartDateTime = Carbon::parse($slot->date_time);
                $slotDuration = $slot->lesson->lesson_duration;
                $slotWhole = floor($slotDuration);
                $slotFraction = $slotDuration - $slotWhole;
                $slotMinutes = $slotFraction * 60;
                $slotEndDateTime = $slotStartDateTime->copy()->addHours($slotWhole)->addMinutes($slotMinutes);

                foreach ($bookedTimeRanges as $bookedRange) {
                    if ($slotStartDateTime < $bookedRange['end'] && $slotEndDateTime > $bookedRange['start']) {
                        return false;
                    }
                }

                return true;
            });

            $uniqueEvents = [];
            $studentCountArr = [];

            foreach ($filteredSlots as $appointment) {
                if (isset($appointment->lesson->user->id)) {
                    $n = $appointment->lesson->lesson_duration;
                    $startDateTime = Carbon::parse($appointment->date_time);
                    $whole = floor($n);
                    $fraction = $n - $whole;
                    $minutes = $fraction * 60;
                    $endDateTime = $startDateTime->copy()->addHours($whole)->addMinutes($minutes);

                    if (!array_key_exists($appointment->id, $studentCountArr)) {
                        $studentCountArr[$appointment->id] = $appointment->student->count();
                    }
                    $studentCount = $studentCountArr[$appointment->id];
                    $maxStudents = $appointment->lesson->max_students;

                    $isFullyBooked = $studentCount >= $maxStudents;
                    $availableSeats = $maxStudents - $studentCount;

                    $students = $appointment->student;
                    $colors = $appointment->is_completed ? '#41d85f' : ($isFullyBooked ?
                        '#c5b706' : '#0071ce');

                    $startDts = $startDateTime->format('h:i a');
                    $endDts = $endDateTime->format('h:i a');

                    $uniqueEvents[] = [
                        'title' => substr($appointment->lesson->lesson_name, 0, 10) .
                            ' (' . ($appointment->lesson->max_students - $availableSeats) . '/' . $appointment->lesson->max_students . ') ',
                        'extendedProps' => [
                            'details' => $startDts == $endDts ? $startDts : $startDts . ' - ' . $endDts,
                            'location' => $appointment->location,
                        ],
                        'start' => $startDateTime->format('Y-m-d H:i:s'),
                        'end' => $endDateTime->format('Y-m-d H:i:s'),
                        'slot_id' => $appointment->id,
                        'color' => $colors,
                        'is_completed' => $appointment->is_completed,
                        'is_student_assigned' => $students->isNotEmpty(),
                        'student' => $students,
                        'slot' => $appointment,
                        'available_seats' => $availableSeats,
                        'lesson' => $appointment->lesson,
                        'instructor' => $appointment->lesson->user,
                        'resourceId' => $appointment->lesson->user->id,
                        'className' => ($appointment->is_completed ? 'custom-completed-class' : ($isFullyBooked ? 'custom-book-class' : 'custom-available-class')) . ' custom-event-class',
                    ];

                    if (!in_array($appointment->lesson->user->id, $resources)) {
                        $resources[$appointment->lesson->user->id] =
                            ['id' => $appointment->lesson->user->id, 'title' => $appointment->lesson->user->name, 'eventColor' => $colors];
                    }
                }
            }
            $events = array_values($uniqueEvents);
            $resources = array_values($resources);
            // $lesson_id = request()->get('lesson_id');
            $lesson_id = request()->input('lesson_ids', request()->get('lesson_id', []));

            $instructors = User::where('type', Role::ROLE_INSTRUCTOR)->get();
            $students = Student::where('active_status', true)->where('isGuest', false)->get();
            // Block Slots
            // $blockSlots = InstructorBlockSlot::all();
            return view('admin.lessons.manageSlots', compact('events', 'resources', 'lesson_id', 'type', 'payment_method', 'instructors', 'students'));
        }
       if ($type === Role::ROLE_INSTRUCTOR) {
            // Handle both single lesson_id and multiple lesson_ids
            $lessonIds = request()->input('lesson_ids', []);
            $singleLessonId = request()->input('lesson_id');
            
            // If no multiple lessons selected, check for single lesson selection
            if (empty($lessonIds) && $singleLessonId && $singleLessonId !== "-1") {
                $lessonIds = [$singleLessonId];
            }
            
            // Get payment method from first selected lesson (or null if none selected)
            $payment_method = !empty($lessonIds) ? Lesson::find($lessonIds[0])?->payment_method : null;
            
            $events = [];

            $slotsQuery = Slots::with(['lesson.user', 'student'])
                ->whereHas('lesson', function ($query) {
                    $query->where('created_by', Auth::user()->id);
                })
                ->where('is_active', true);

            // Filter by lessons if any are selected
            if (!empty($lessonIds)) {
                $slotsQuery->whereHas('lesson', function ($query) use ($lessonIds) {
                    $query->whereIn('id', $lessonIds);
                });
            }

            $slots = $slotsQuery->get();

            $bookedSlots = $slots->filter(function ($slot) {
                return $slot->student->isNotEmpty();
            });

            $bookedTimeRanges = [];
            foreach ($bookedSlots as $bookedSlot) {
                $startDateTime = Carbon::parse($bookedSlot->date_time);
                $duration = $bookedSlot->lesson->lesson_duration;
                $whole = floor($duration);
                $fraction = $duration - $whole;
                $minutes = $fraction * 60;
                $endDateTime = $startDateTime->copy()->addHours($whole)->addMinutes($minutes);

                $bookedTimeRanges[] = [
                    'start' => $startDateTime,
                    'end' => $endDateTime,
                    'slot_id' => $bookedSlot->id
                ];
            }

            $filteredSlots = $slots->filter(function ($slot) use ($bookedTimeRanges) {
                if ($slot->student->isNotEmpty()) {
                    return true;
                }

                $slotStartDateTime = Carbon::parse($slot->date_time);
                $slotDuration = $slot->lesson->lesson_duration;
                $slotWhole = floor($slotDuration);
                $slotFraction = $slotDuration - $slotWhole;
                $slotMinutes = $slotFraction * 60;
                $slotEndDateTime = $slotStartDateTime->copy()->addHours($slotWhole)->addMinutes($slotMinutes);

                foreach ($bookedTimeRanges as $bookedRange) {
                    if ($slotStartDateTime < $bookedRange['end'] && $slotEndDateTime > $bookedRange['start']) {
                        return false;
                    }
                }

                return true;
            });

            $uniqueEvents = [];
            $studentCountArr = [];
            foreach ($filteredSlots as $appointment) {
                $n = $appointment->lesson->lesson_duration;
                $whole = floor($n);
                $fraction = $n - $whole;
                $intervalString = $whole . ' hours' . ' + ' . $fraction * 60 . ' minutes';
                $startDateTime = Carbon::parse($appointment->date_time);

                $minutes = $fraction * 60;
                $endDateTime = $startDateTime->copy()->addHours($whole)->addMinutes($minutes);

                if (!array_key_exists($appointment->id, $studentCountArr)) {
                    $studentCountArr[$appointment->id] = $appointment->student->count();
                }
                $studentCount = $studentCountArr[$appointment->id];
                $maxStudents = $appointment->lesson->max_students;

                $isFullyBooked = $studentCount >= $maxStudents;
                $availableSeats = $maxStudents - $studentCount;

                $students = $appointment->student;
                $colors = $appointment->is_completed ? '#41d85f' : (($type == Role::ROLE_INSTRUCTOR && $isFullyBooked ||
                    $type == Role::ROLE_STUDENT && $students->contains('id', Auth::user()->id)) ?
                    '#c5b706' : '#0071ce');
                $className = $appointment->is_completed ? 'custom-completed-class' : (($type == Role::ROLE_INSTRUCTOR && $isFullyBooked ||
                    $type == Role::ROLE_STUDENT && $students->contains('id', Auth::user()->id))
                    ? 'custom-book-class' : 'custom-available-class') . ' custom-event-class';

                $startDts = $startDateTime->format('h:i a');
                $endDts = $endDateTime->format('h:i a');

                $uniqueEvents[] = [
                    'title' => substr($appointment->lesson->lesson_name, 0, 10) .
                        ' (' . ($appointment->lesson->max_students - $availableSeats) . '/' . $appointment->lesson->max_students . ') ',
                    'extendedProps' => [
                        'details' => $startDts == $endDts ? $startDts : $startDts . ' - ' . $endDts,
                        'location' => $appointment->location,
                    ],
                    'start' => $startDateTime->format('Y-m-d H:i:s'),
                    'end' => $endDateTime->format('Y-m-d H:i:s'),
                    'slot_id' => $appointment->id,
                    'color' => $colors,
                    'is_completed' => $appointment->is_completed,
                    'is_student_assigned' => $students->isNotEmpty(),
                    'student' => $students,
                    'slot' => $appointment,
                    'available_seats' => $availableSeats,
                    'lesson' => $appointment->lesson,
                    'instructor' => $appointment->lesson->user,
                    'resourceId' => $appointment->lesson->user->id,
                    'className' => $className,
                ];
            }
            $events = array_values($uniqueEvents);
            // $lesson_id = request()->get('lesson_id');
            $lesson_id = request()->input('lesson_ids', request()->get('lesson_id', []));
            $lessons = Lesson::where('created_by', Auth::user()->id)->where('active_status', 1)->where('type', '!=', Lesson::LESSON_TYPE_ONLINE)->get();
            $students = Student::where('active_status', true)->where('isGuest', false)->get();
            // Block Instructor Slots
            $blockSlots = InstructorBlockSlot::where('instructor_id', Auth::user()->id)->get();
            return view('admin.lessons.instructorSlots', compact('events', 'lesson_id', 'type', 'payment_method', 'lessons', 'students', 'blockSlots'));
        } else {
            return redirect()->back()->with('failed', __('Permission denied.'));
        }
    }


    public function viewSlots()
    {
        if (Auth::user()->can('manage-lessons')) {
            $lesson = Lesson::with('slots')->findOrFail(request()->get('lesson_id'));
            $slotDates = $lesson->slots->pluck('date_time')->map(function ($dt) {
                return \Carbon\Carbon::parse($dt)->format('Y-m-d');
            })->toArray();
            $slots = Slots::where('lesson_id', request()->get('lesson_id'))->with('lesson')->get();
            $allSlots = Slots::where('is_active', true)->get();
            $events = [];
            $authUser = Auth::user();
            $type = $authUser->type;

            if ($type == Role::ROLE_STUDENT) {
                $slots = $slots->filter(function ($slot) use ($authUser) {
                    return $slot->availableSeats() > 0 || $slot->student->contains('id', $authUser->id);
                });
            }

            // Get booked slots with their time ranges from all slots
            $bookedSlots = $allSlots->filter(function ($slot) {
                return $slot->student->isNotEmpty();
            });

            // Create time ranges for booked slots
            $bookedTimeRanges = [];
            foreach ($bookedSlots as $bookedSlot) {
                $startDateTime = Carbon::parse($bookedSlot->date_time);
                $duration = !is_null($bookedSlot->lesson) ? $bookedSlot->lesson->lesson_duration : null;
                $whole = floor($duration);
                $fraction = $duration - $whole;
                $minutes = $fraction * 60;
                $endDateTime = $startDateTime->copy()->addHours($whole)->addMinutes($minutes);

                $bookedTimeRanges[] = [
                    'start' => $startDateTime,
                    'end' => $endDateTime,
                    'slot_id' => $bookedSlot->id
                ];
            }

            $filteredSlots = $slots->filter(function ($slot) use ($bookedTimeRanges) {
                // Always include slots that have students (booked slots)
                if ($slot->student->isNotEmpty()) {
                    return true;
                }

                // Check if this slot's time range overlaps with any booked time range
                $slotStartDateTime = Carbon::parse($slot->date_time);
                $slotDuration = $slot->lesson->lesson_duration;
                $slotWhole = floor($slotDuration);
                $slotFraction = $slotDuration - $slotWhole;
                $slotMinutes = $slotFraction * 60;
                $slotEndDateTime = $slotStartDateTime->copy()->addHours($slotWhole)->addMinutes($slotMinutes);

                foreach ($bookedTimeRanges as $bookedRange) {
                    // Check if there's any overlap between the time ranges
                    if ($slotStartDateTime < $bookedRange['end'] && $slotEndDateTime > $bookedRange['start']) {
                        return false; // Exclude this slot as it overlaps with a booked time range
                    }
                }

                return true; // Include this slot as it doesn't overlap with any booked time range
            });

            foreach ($filteredSlots as $appointment) {

                $n = $appointment->lesson->lesson_duration;
                $whole = floor($n);
                $fraction = $n - $whole;
                $intervalString = $whole . ' hours' . ' + ' . $fraction * 60 . ' minutes';

                $students = $appointment->student;

                $colors =  $appointment->is_completed ? '#41d85f' : (($type == Role::ROLE_INSTRUCTOR && $appointment->isFullyBooked() ||
                    $type == Role::ROLE_STUDENT && $students->contains('id', Auth::user()->id)) ?
                    '#c5b706' : '#0071ce');
                $className = $appointment->is_completed ? 'custom-completed-class' : (($type == Role::ROLE_INSTRUCTOR && $appointment->isFullyBooked() ||
                    $type == Role::ROLE_STUDENT && $students->contains('id', Auth::user()->id))
                    ? 'custom-book-class' : 'custom-available-class') . ' custom-event-class';

                array_push($events, [
                    'title' => $appointment->lesson->lesson_name . ' (' . $appointment->lesson->max_students - $appointment->availableSeats() . '/' . $appointment->lesson->max_students . ')',
                    'start' => $appointment->date_time,
                    'end' => date("Y-m-d H:i:s", strtotime($appointment->date_time . " +" . $intervalString)),
                    'slot_id' => $appointment->id,
                    'color' => $colors,
                    'is_completed' => $appointment->is_completed,
                    'is_student_assigned' => $students->isNotEmpty(),
                    'student' => $students,
                    'slot' => $appointment,
                    'isFullyBooked' => $appointment->isFullyBooked(),
                    'available_seats' => $appointment->availableSeats(),
                    'instructor' => $appointment->lesson->user,
                    'className' => $className,
                ]);
            }
            $lesson_id = request()->get('lesson_id');
            $authId = Auth::user()->id;
            $students = Student::where('active_status', true)->where('isGuest', false)->get();
            return view('admin.lessons.viewSlots', compact('events', 'lesson_id', 'type', 'authId', 'students', 'lesson', 'slotDates'));
        }
    }

    //API ENDPOINT METHODS

    public function addLessonApi()
    {

        if (Auth::user()->type == Role::ROLE_INSTRUCTOR && Auth::user()->active_status == 1) {
            try {

                $validatedData = request()->validate([
                    'lesson_name'          => 'required|string|max:255',
                    'lesson_description'   => 'required|string',
                    'lesson_price'         => 'required|numeric',
                    'lesson_quantity'      => 'integer',
                    'required_time'        => 'integer',
                    'lesson_duration'      => 'numeric|between:0,99.99',
                    'type'                 => ['required', 'in:online,inPerson'],
                    'payment_method'       => ['required', 'in:online,cash'],
                    'slots'                => 'array',
                    'max_students'         => 'integer|min:1',
                    'is_package_lesson'    => 'boolean',
                ]);

                $validatedData['created_by'] = Auth::user()->id;
                $validatedData['tenant_id'] = Auth::user()->tenant_id;

                if ($validatedData['type'] === Lesson::LESSON_TYPE_INPERSON) {
                    $validatedData['lesson_quantity'] = 1;
                    $validatedData['required_time'] = 0;

                    if (empty($validatedData['max_students'])) {
                        $validatedData['max_students'] = 1;
                    }

                    if (!empty($validatedData['is_package_lesson'])) {
                        $validatedData['payment_method'] = Lesson::LESSON_PAYMENT_ONLINE;
                    }
                }
                $lesson = Lesson::create($validatedData);

                if (isset($validatedData['slots']) && $lesson->type == Lesson::LESSON_TYPE_INPERSON) {
                    foreach ($validatedData['slots'] as $slot) {
                        Slots::create(['lesson_id' => $lesson->id, 'date_time' => Carbon::parse($slot)]);
                    }
                }
                $students = Student::whereHas('pushToken')
                    ->with('pushToken')
                    ->get()
                    ->pluck('pushToken.token')
                    ->toArray();

                if (!empty($students)) {
                    $title = "New Lesson Available!";
                    $body = Auth::user()->name . " has created a new lesson: " . $lesson->lesson_name;
                    SendPushNotification::dispatch($students, $title, $body);
                }
            } catch (\Exception $e) {
                return throw new Exception($e->getMessage());
            }
        } else {
            return response('UnAuthorized', 401);
        }

        return response(new LessonAPIResource($lesson), 200);
    }

    public function updateLessonApi()
    {
        $validatedData = request()->validate([
            'id'                   => 'required',
            'lesson_name'          => 'string|max:255',
            'lesson_description'   => 'string',
            'lesson_price'         => 'numeric',
            'lesson_quantity'      => 'integer',
            'lesson_duration'      => 'numeric|between:0,99.99',
            'required_time'        => 'integer',
            'detailed_description' => 'string',
            'max_students'         => 'integer|min:1',
        ]);

        if (Auth::user()->type == Role::ROLE_INSTRUCTOR && Auth::user()->active_status == 1) {
            try {
                $lesson = Lesson::find(request()->id);
                if ($lesson->created_by == Auth::user()->id) {
                    if (!empty($validatedData['is_package_lesson'])) {
                        $validatedData['payment_mehod'] = Lesson::LESSON_PAYMENT_ONLINE;
                    }
                    $lesson->update($validatedData);
                }
            } catch (\Exception $e) {
                return throw new Exception($e->getMessage());
            }
        } else {
            return response('Unauthorized', 401);
        }

        return response(new LessonAPIResource($lesson), 200);
    }

    public function deleteLessonApi()
    {
        request()->validate([
            'id' => 'required'
        ]);
        $lesson = Lesson::find(request()->id);
        if ((Auth::user()->type == Role::ROLE_INSTRUCTOR || Auth::user()->type == Role::ROLE_ADMIN) && Auth::user()->active_status == 1 && !!$lesson) {
            try {
                if ($lesson->created_by == Auth::user()->id || Auth::user()->type == Role::ROLE_ADMIN)
                    $lesson->delete();
            } catch (\Exception $e) {
                return throw new Exception($e->getMessage());
            }
        } else {
            return response('Unauthorized', 401);
        }

        return response('Sucessfully deleted permenantly', 200);
    }

    public function addSlot(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'lesson_id' => 'required|integer',
                'date_time' => 'required|date',
                'location'  => 'required|string|max:255',
            ]);

            $lesson = Lesson::find($request->get('lesson_id'));

            if (!$lesson || $lesson->type !== Lesson::LESSON_TYPE_INPERSON) {
                return $request->get('redirect') == 1
                    ? redirect()->back()->with('error', 'In-Person lesson not found for lesson id: ' . $request->lesson_id)
                    : response()->json(['error' => 'In-Person lesson not found for lesson id: ' . $request->lesson_id], 422);
            }

            // If lesson is a package and has purchases, don't allow new slots
            if ($lesson->is_package_lesson && $lesson->purchases()->where('status', Purchase::STATUS_COMPLETE)->exists()) {
                return $request->get('redirect') == 1
                    ? redirect()->back()->with('error', 'Cannot add new slots as purchases already exist for this package lesson.')
                    : response()->json(['error' => 'Cannot add new slots as purchases already exist for this package lesson.'], 422);
            }

            Slots::create($validatedData);

            return $request->get('redirect') == 1
                ? redirect()->route('slot.view', ['lesson_id' => $lesson->id])->with('success', __('Slot Successfully Added'))
                : response()->json(['message' => 'Slot successfully created against the lesson.'], 200);
        } catch (\Exception $e) {
            return $request->get('redirect') == 1
                ? redirect()->back()->with('error', $e->getMessage())
                : response()->json(['error' => $e->getMessage()], 422);
        }
    }



    public function addConsectuiveSlots(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'lesson_id' => 'required|integer',
                'start_date' => 'required|date_format:Y-m-d',
                'end_date' => 'required|date_format:Y-m-d',
                'start_time' => 'required|date_format:H:i',
                'end_time' => 'required|date_format:H:i',
                'location'  => 'required|string|max:255',
            ]);

            $lesson = Lesson::find($request->get('lesson_id'));

            // if (!$lesson || $lesson->type != Lesson::LESSON_TYPE_INPERSON) {
            if (!$lesson) {
                return $request->get('redirect') == 1
                    ? redirect()->back()->with('error', 'In-Person lesson not found.')
                    : response()->json(['error' => 'In-Person lesson not found.'], 404);
            }
            // Prevent adding slots if it's a package lesson with completed purchases
            if ($lesson->is_package_lesson && $lesson->purchases()->where('status', Purchase::STATUS_COMPLETE)->exists()) {
                return $request->get('redirect') == 1
                    ? redirect()->back()->with('error', 'Cannot add slots. This package lesson already has completed purchases.')
                    : response()->json(['error' => 'Cannot add slots. This package lesson already has completed purchases.'], 422);
            }

            $begin = new Carbon($validatedData['start_date'] . ' ' . $validatedData['start_time']);
            $end = new Carbon($validatedData['end_date'] . ' ' . $validatedData['end_time']);
            $n = $lesson->lesson_duration;
            $whole = floor($n);
            $fraction = $n - $whole;
            $intervalString = $whole . ' hours' . ' + ' . $fraction * 60 . ' minutes';
            $interval = DateInterval::createFromDateString($intervalString);
            $period = new DatePeriod($begin, $interval, $end);
            $minutes = $n * 60;

            $slots = [];

            foreach ($period as $dt) {
                $temp = clone $dt;
                $endTime = $temp->add(new DateInterval('PT' . $minutes . 'M'))->format('H:i');
                if ($temp->format('Y-m-d') === $dt->format('Y-m-d') && strtotime($dt->format('H:i')) >= strtotime($validatedData['start_time']) && (strtotime($endTime) <= strtotime($validatedData['end_time']))) {
                    $slotData = [
                        'lesson_id' => $request->get('lesson_id'),
                        'date_time' => $dt->format("Y-m-d H:i:s"),
                        'location'  => $request->get('location')
                    ];
                    $slot = Slots::updateOrCreate($slotData);
                    array_push($slots, $slot);
                }
            }

            // Send push notifications for new lessons
            $students = Student::whereHas('pushToken')
                ->with('pushToken')
                ->get()
                ->pluck('pushToken.token')
                ->toArray();

            if (!empty($students)) {
                $title = "New Lessons Available!";
                $body  = "{$lesson->user->name} has created new lesson opportunities: {$lesson->lesson_name}. Check now!";
                SendPushNotification::dispatch($students, $title, $body);
            }

            // Return based on redirect parameter
            return $request->get('redirect') == 1
                ? redirect()->route('slot.view', ['lesson_id' => $lesson->id])->with('success', 'Slots Successfully Added')
                : response()->json([
                    'message' => 'Consecutive Slots for the given range are successfully created',
                    'slots' => $slots
                ]);
        } catch (\Exception $e) {
            return $request->get('redirect') == 1
                ? redirect()->back()->with('error', $e->getMessage())
                : response()->json(['error' => $e->getMessage()], $e->getCode() ?: 500);
        }
    }


    public function bookAdminSlot(Request $request)
    {
        try {
            // dd($request->all());
            $request->validate([
                'isGuest' => 'required',
                'slot_id' => 'required',
            ]);

            $slot = Slots::where('id', $request->slot_id)->first();
            $studentIds = [];

            if ($request->isGuest != "false") {
                // Check if a guest with the same email already exists
                $existingGuest = Student::where('email', $request->guestEmail)->first();

                if ($existingGuest) {
                    // Guest already exists, use existing student ID
                    $studentIds[] = $existingGuest->id;
                } else {
                    // Create a new guest
                    $randomPassword = Str::random(10);
                    $userData = [
                        'name'              => $request->guestName,
                        'email'             => $request->guestEmail,
                        'uuid'              => Str::uuid(),
                        'password'          => Hash::make($randomPassword),
                        'type'              => Role::ROLE_STUDENT,
                        'isGuest'           => true,
                        'created_by'        => Auth::user()->id,
                        'email_verified_at' => (UtilityFacades::getsettings('email_verification') == '1') ? null : Carbon::now()->toDateTimeString(),
                        'phone_verified_at' => (UtilityFacades::getsettings('phone_verification') == '1') ? null : Carbon::now()->toDateTimeString(),
                        'phone'             => str_replace(' ', '', $request->guestPhone),
                    ];

                    $user = Student::create($userData);
                    $user->assignRole(Role::ROLE_STUDENT);
                    $studentIds[] = $user->id;
                }
            } else {
                // If not a guest, use provided student IDs
                $studentIds = $request->get('student_Ids', []);
            }

            $alreadyBookedStudents = $slot->student()->pluck('students.id')->toArray();
            $newStudentIds = array_diff($studentIds, $alreadyBookedStudents);

            if (!empty($newStudentIds)) {
                $slot->student()->attach($newStudentIds);

                foreach ($newStudentIds as $studentId) {
                    Purchase::create([
                        'student_id'    => $studentId,
                        'instructor_id' => $slot->lesson->created_by,
                        'lesson_id'     => $slot->lesson_id,
                        'slot_id'       => $slot->id,
                        'coupon_id'     => null,
                        'tenenat_id'    => Auth::user()->tenant_id,
                        'total_amount'  => $slot->lesson->lesson_price,
                        'status'        => Purchase::STATUS_INCOMPLETE,
                        'lessons_used'  => 0,
                        'purchased_slot' => 1
                    ]);

                    // Send notification for each new student
                    $this->sendSlotNotification(
                        $slot,
                        'Slot Booked',
                        'A slot has been booked for :date with :instructor for the in-person lesson :lesson.',
                        'A slot has been booked for :date with student ID ' . $studentId . ' for the in-person lesson :lesson.'
                    );
                }
            }

            $studentEmails = Student::select('email')
                ->whereIn('id', $studentIds)
                ->pluck('email');


            if (!$studentEmails->isEmpty()) {
                SendEmail::dispatch($studentEmails->toArray(), new SlotBookedByStudentMail(
                    Auth::user()->name,
                    date('Y-m-d', strtotime($slot->date_time)),
                    date('h:i A', strtotime($slot->date_time)),
                    $request->notes,
                ));
            }

            if (request()->redirect == 1) {
                return response()->json(['message' => 'Slot Successfully Booked.'], 200);
                // return redirect()->route('slot.view', ['lesson_id' => $slot?->lesson_id])
                //     ->with('success', 'Slot Successfully Booked.');
            }
        } catch (\Exception $e) {
            throw new Exception($e->getMessage(), $e->getCode());
        }
    }


    public function bookSlotApi()
    {

        try {
            $friendNames = request()->input('friend_names');
            if (is_string($friendNames)) {
                request()->merge(['friend_names' => json_decode(request()->friend_names, true) ?? []]);
            }

            request()->validate([
                'slot_id'  => 'required|exists:slots,id',
                'student_ids'  => 'array',
                'student_ids.*' => 'integer|exists:students,id',
                'friend_names' => 'array',
                'friend_names.*' => 'string|max:255',
            ]);

            // Convert JSON string to array (just in case)

            $slot = Slots::with('lesson', 'student')->findOrFail(request()->slot_id);
            if (Auth::user()->type == Role::ROLE_STUDENT && Auth::user()->active_status == 1 && !!$slot) {
                return $this->handleStudentBookingAPI($slot, request());
            }

            if (Auth::user()->type == Role::ROLE_INSTRUCTOR && Auth::user()->active_status == 1 && !!$slot && $slot->lesson->created_by == Auth::user()->id) {
                if ($slot->lesson->is_package_lesson) {
                    return request()->redirect == 1 ? redirect()->back()->with('errors', 'Instructors cannot book package lesson slots') :
                        response()->json(['error' => 'Instructors cannot book package lesson slots.'], 422);
                }
                return $this->handleInstructorBookingAPI($slot);
            }

            return response('Unauthorized', 401);
        } catch (\Exception $e) {
            report($e);
            throw new Exception($e->getMessage());
        }
    }

    private function handleStudentBookingAPI($slot, $request)
    {
        $bookingStudent = Auth::user();
        $bookingStudentId = $bookingStudent->id;
        $otherSlotBooked = false;

        $friendNames = request()->friend_names ?? [];
        if (!is_array($friendNames)) {
            $friendNames = array_filter(explode(',', $friendNames));
        }
        $totalNewBookings = count($friendNames) + 1;
        $checkPackageBooking = Purchase::where([
            'student_id' => $bookingStudentId,
            'type' => $slot->lesson->type,
            'lesson_id' => $slot->lesson_id
        ])->first();


        // $lesson = $slot->lesson;
        // if ($lesson->is_package_lesson && $checkPackageBooking) {
        //     $used = \App\Models\StudentSlot::where('student_id', auth()->user()->id)
        //         ->whereHas('slot', function ($query) use ($lesson) {
        //             $query->where('lesson_id', $lesson->id);
        //         })->count();
        //     $total = $checkPackageBooking->purchased_slot ?? 0;

        //     if ($used == $total) {
        //         return request()->redirect == 1 ? redirect()->back()->with('warning', "Max slots ($total) reached for this package lesson.") :
        //             response()->json(['message' => "Max slots ($total) reached for this package lesson."], 422);
        //     }
        // }


        if (!$bookingStudent->slots->pluck('date_time')->isEmpty()) {
            $otherSlotBooked = in_array($slot->date_time, $bookingStudent->slots->pluck('date_time')->toArray());
        }

        if (!empty($checkPackageBooking)) {

            if ($otherSlotBooked) {
                return request()->redirect == 1 ? redirect()->route('slot.view', ['lesson_id' => $slot->lesson_id])->with('success', 'Purchase Successful.') :
                    response()->json(['message' => 'You have already scheduled another lesson for this date and time.'], 422);
            }

            if (request()->redirect == 0) {
                // Attach main student to the slot
                $slot->student()->attach($bookingStudentId, [
                    'isFriend' => false,
                    'friend_name' => null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                // Attach friends to the slot
                if (!empty($friendNames)) {
                    foreach ($friendNames as $friendName) {
                        $slot->student()->attach($bookingStudentId, [
                            'isFriend' => true,
                            'friend_name' => $friendName,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                }

                // Send booking notifications
                $this->sendSlotNotification(
                    $slot,
                    'Slot Booked',
                    'A slot has been booked for :date with :instructor for the in-person lesson :lesson.',
                    'A slot has been booked for :date with :student for the in-person lesson :lesson.'
                );

                SendEmail::dispatch($slot->lesson->user->email, new SlotBookedByStudentMail(
                    $bookingStudent->name,
                    date('Y-m-d', strtotime($slot->date_time)),
                    date('h:i A', strtotime($slot->date_time)),
                    $request->notes
                ));

                // Reminder Scheduling
                $instructor = $slot->lesson->user;
                $reminderMinutes = $instructor->reminder_minutes_before ?? 0;

                if ($reminderMinutes > 0) {
                    $lessonStart = \Carbon\Carbon::parse($slot->date_time);
                    $sendTime = $lessonStart->copy()->subMinutes($reminderMinutes);

                    SendLessonReminderJob::dispatch($slot)->delay($sendTime);
                }
            }
        } else {
            if ($slot->student()->count() + $totalNewBookings > $slot->lesson->max_students) {
                return request()->redirect == 1
                    ? redirect()->back()->with('error', 'Sorry, the number of booked slots exceeds the limit.')
                    : response()->json(['error' => 'Sorry, the number of booked slots exceeds the limit.'], 422);
            }

            if ($slot->student()->where('students.id', $bookingStudentId)->exists()) {
                return request()->redirect == 1
                    ? redirect()->back()->with('error', 'You have already booked this slot')
                    : response()->json(['error' => 'You have already booked this slot.'], 422);
            }
            $lessonPrice = !empty($slot->lesson->type == 'package') ? $request->package_price : $slot->lesson->lesson_price;

            if ($slot->lesson->type == 'package') {
                $purchasedSlot =  PackageLesson::where(['price' => $lessonPrice, 'lesson_id' => $slot->lesson_id])->first();
            }
            // Calculate total price for student and friends
            $totalAmount = $lessonPrice * $totalNewBookings;

            // Create purchase entry
            $newPurchase = new Purchase([
                'student_id' => $bookingStudentId,
                'instructor_id' => $slot->lesson->created_by,
                'lesson_id' => $slot->lesson_id,
                'type' => $slot->lesson->type,
                'slot_id' => $slot->id,
                'coupon_id' => null,
                'tenant_id' => Auth::user()->tenant_id,
                'total_amount' => $totalAmount,
                'purchased_slot' => $purchasedSlot->number_of_slot ?? 1,
                'status' => Purchase::STATUS_INCOMPLETE,
                'lessons_used' => 0,
                'friend_names' => !empty($friendNames) ? json_encode($friendNames) : null, // Store friends' names
            ]);
            $newPurchase->save();

            if ($slot->lesson->payment_method == Lesson::LESSON_PAYMENT_ONLINE) {
                request()->merge(['purchase_id' => $newPurchase->id]);
                request()->setMethod('POST');
                return request()->redirect == 1 ? $this->confirmPurchaseWithRedirect(request(), false) :
                    $this->confirmPurchaseWithRedirect(request(), true);
            } elseif ($slot->lesson->payment_method == Lesson::LESSON_PAYMENT_CASH && $slot->lesson->is_package_lesson == 0) {
                $slots = $slot->lesson->slots; // Fetch all slots of the lesson
                foreach ($slots as $lessonSlot) {
                    // Attach student to all slots
                    $lessonSlot->student()->attach($newPurchase->student_id, [
                        'isFriend' => false,
                        'friend_name' => null,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    // Attach friends if any were included in the purchase
                    $friendNames = json_decode($newPurchase->friend_names, true) ?? [];
                    foreach ($friendNames as $friendName) {
                        $lessonSlot->student()->attach($newPurchase->student_id, [
                            'isFriend' => true,
                            'friend_name' => $friendName,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                }

                // Send booking notifications
                $this->sendSlotNotification(
                    $slot,
                    'Slot Booked',
                    'A slot has been booked for :date with :instructor for the in-person lesson :lesson.',
                    'A slot has been booked for :date with :student for the in-person lesson :lesson.'
                );

                SendEmail::dispatch($slot->lesson->user->email, new SlotBookedByStudentMail(
                    $bookingStudent->name,
                    date('Y-m-d', strtotime($slot->date_time)),
                    date('h:i A', strtotime($slot->date_time))
                ));
            }
        }

        return request()->redirect == 1
            ? redirect()->route(
                $slot->lesson->is_package_lesson == 0 ? 'home' : 'slot.view',
                ['lesson_id' => $slot->lesson_id]
            )->with('success', 'Purchase Successful.')
            : response()->json([
                'message' => 'Slot successfully reserved.',
                'slot' => new SlotAPIResource($slot),
                'friend_names' => $friendNames
            ], 200);
    }


    private function handleInstructorBookingAPI($slot)
    {
        $studentIds = request()->input('student_ids', []);

        if (empty($studentIds)) {
            return response()->json(['error' => 'At least one student ID is required for instructor booking.'], 422);
        }

        $alreadyBookedStudents = $slot->student()->whereIn('students.id', $studentIds)->pluck('students.id')->toArray();
        $studentsToBook = array_diff($studentIds, $alreadyBookedStudents);

        if (empty($studentsToBook)) {
            return response()->json(['error' => 'All selected students have already booked this slot.'], 422);
        }

        if ($slot->student()->count() + count($studentsToBook) > $slot->lesson->max_students) {
            throw new \Exception('Sorry, the number of booked slots exceeds the limit.');
        }

        foreach ($studentsToBook as $studentId) {
            $slot->student()->attach($studentId);
            Purchase::create([
                'student_id' => $studentId,
                'instructor_id' => $slot->lesson->created_by,
                'lesson_id' => $slot->lesson_id,
                'slot_id' => $slot->id,
                'coupon_id' => null,
                'tenant_id' => Auth::user()->tenant_id,
                'total_amount' => $slot->lesson->lesson_price,
                'status' => Purchase::STATUS_INCOMPLETE,
                'lessons_used' => 0,
            ]);
        }

        $this->sendSlotNotification(
            $slot,
            'Slot Booked',
            'A slot has been booked for :date with :instructor for the in-person lesson :lesson.',
            'A slot has been booked for :date with :student for the in-person lesson :lesson.'
        );

        return request()->redirect == 1
            ? redirect()->route('slot.view', ['lesson_id' => $slot->lesson_id])->with('success', 'Slot Successfully Booked.')
            : response()->json(['message' => 'Slot successfully booked for students.', 'slot' => new SlotAPIResource($slot)], 200);
    }

    public function completeSlot()
    {
        try {
            request()->validate([
                'slot_id'  => 'required',
                'payment_method' => 'required',
            ]);

            $slot = Slots::find(request()->slot_id);
            $user = Auth::user();

            if ($user->type !== Role::ROLE_INSTRUCTOR && $user->id !== $slot->lesson->created_by) {
                return request()->get('redirect') == 1
                    ? redirect()->back()->with('error', 'Unauthorized')
                    : response()->json(['error' => 'Unauthorized'], 403);
            }


            $students = $slot->student;
            if ($slot->lesson->payment_method != Lesson::LESSON_PAYMENT_CASH) {
                $hasIncompletePurchases = Purchase::where('slot_id', $slot->id)
                    ->where('status', '!=', Purchase::STATUS_COMPLETE)
                    ->exists();

                if ($hasIncompletePurchases) {

                    return request()->get('redirect') == 1
                        ? redirect()->back()->with('error', 'Cannot complete this slot until all payments are completed.')
                        : response()->json(['error' => 'Cannot complete this slot until all payments are completed.'], 422);
                }
            }
            $slot->is_completed = true;
            $slot->save();

            if (
                request()->payment_method === Lesson::LESSON_PAYMENT_CASH ||
                $slot->lesson->payment_method === Lesson::LESSON_PAYMENT_CASH ||
                $students->isEmpty()
            ) {
                $this->sendSlotNotification(
                    $slot,
                    'Slot Completed',
                    'Your Slot with :instructor for the in-person lesson :lesson at :date has been completed.',
                    'Your Slot for the in-person lesson :lesson at :date has been completed.'
                );
            }

            foreach ($students as $student) {
                if ((bool)$student->pivot->isFriend)
                    continue;

                $purchase = Purchase::where('student_id', $student->id)
                    ->where('slot_id', $slot->id)
                    ->first();

                if (!$purchase)
                    continue;

                if ((request()->payment_method === Lesson::LESSON_PAYMENT_ONLINE)
                    || $slot->lesson->payment_method === Lesson::LESSON_PAYMENT_ONLINE
                ) {
                    $session = $this->createSessionForPayment($purchase, false, $slot->id);
                    $response = redirect()->route('slot.manage', ['lesson_id' => $purchase->lesson_id]);
                    $sessionUrl = $response->getTargetUrl();
                    SendEmail::dispatch($student->email, new StudentPaymentLink($purchase, $sessionUrl));
                } else {
                    $purchase->status = Purchase::STATUS_COMPLETE;
                    $purchase->isFeedbackComplete = true;
                    $purchase->save();
                }
            }

            return request()->get('redirect') == 1
                ? redirect()->back()->with('success', 'Slot Successfully Completed.')
                : response()->json(['message' => 'Slot successfully marked as completed', 'slot' => new SlotAPIResource($slot)]);

            if ((request()->payment_method === Lesson::LESSON_PAYMENT_ONLINE)
                || $slot->lesson->payment_method === Lesson::LESSON_PAYMENT_ONLINE
            ) {
                if (request()->get('redirect') == 1)
                    return redirect()->back()->with('success', 'Checkout link sent to all booked students via email, slot will complete once all payments are complete.');
                return response()->json(['message' => 'Checkout link sent to all booked students via email, slot will complete once all payments are complete.']);
            }
            if (request()->get('redirect') == 1)
                return redirect()->back()->with('success', 'Slot Successfully Completed.');
            return response()->json(['message' => 'Slot successfully marked as completed', 'slot' => new SlotAPIResource($slot),]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], $e->getCode() ?: 400);
        }
    }

    public function getSlots(Request $request)
    {
        try {
            $request->validate([
                'lesson_id' => 'required|integer',
            ]);

            $lesson = Lesson::find($request->lesson_id);

            if (!$lesson || $lesson->type !== Lesson::LESSON_TYPE_INPERSON) {
                throw new Exception('InPerson lesson not found for lesson id : ' . $request->lesson_id, 404);
            }

            $now = Carbon::now();

            $slots = Slots::where('is_active', true)
                ->where('lesson_id', $request->lesson_id)
                ->where(function ($query) use ($now) {
                    $query->whereDate('date_time', '>=', $now->toDateString())  // Future slots including today
                        ->orWhereHas('student'); // Include past slots that are booked
                })
                ->orderBy('date_time') // Order by date_time
                ->get();

            return response()->json(SlotAPIResource::collection($slots), 200);
        } catch (\Exception $e) {
            throw new Exception($e->getMessage(), $e->getCode());
        }
    }

    public function getAllSlotsInstructor(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'instructor_id' => 'required|integer|exists:users,id',
                'start_date' => 'required|date_format:Y-m-d',
                'end_date' => 'required|date_format:Y-m-d',
                'type' => 'nullable|in:online,inPerson,package_lesson', // Added package_lesson
            ]);

            $instructor_id = $validatedData['instructor_id'];
            $begin = new Carbon($validatedData['start_date']);
            $end = (new Carbon($validatedData['end_date']))->endOfDay();
            $today = Carbon::today();

            $slots = Slots::whereHas('lesson', function ($q) use ($instructor_id, $validatedData) {
                $q->where('created_by', $instructor_id);
                if (!empty($validatedData['type'])) {
                    if ($validatedData['type'] === 'package_lesson') {
                        $q->where('type', 'inPerson')->where('is_package_lesson', true);
                    } else {
                        $q->where('type', $validatedData['type']);
                    }
                }
            })
                ->whereBetween('date_time', [$begin, $end])
                ->where(function ($query) use ($today) {
                    $query->whereDate('date_time', '>=', $today)
                        ->orWhere(function ($q) use ($today) {
                            $q->whereDate('date_time', '<', $today)
                                ->whereHas('student');
                        });
                })
                ->orderBy('date_time', 'asc')
                ->get();

            $slots->load('lesson', 'student');

            return response()->json([
                'slots' => SlotAPIResource::collection($slots),
                'total' => $slots->count()
            ]);
        } catch (\Exception $e) {
            throw new Exception($e->getMessage(), $e->getCode());
        }
    }



    public function updateSlot(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'slot_id'      => 'integer',
                'date_time'    => 'date|nullable',
                'location'     => 'string',
                'is_completed' => 'boolean',
                'is_active'    => 'boolean',
                'cancelled'    => 'boolean',
                'unbook'       => 'boolean',
                'student_ids'  => 'array',
                'student_ids.*' => 'integer|exists:students,id',
                'notes'         => 'string',
                'lessonId'      => 'nullable|exists:lessons,id'
            ]);
            $user = Auth::user();
            $slot = Slots::find($request->slot_id);
            if (array_key_exists('lessonId', $validatedData)) {
                $slots = Slots::where('lesson_id', $validatedData['lessonId'])->get();
                foreach ($slots as $slot) {
                    if ($slot->student->contains($user->id)) {
                        $slot->student()->detach($user->id);
                        Purchase::where('slot_id', $slot->id)->where('student_id', $user->id)->delete();
                        $this->sendSlotNotification(
                            $slot,
                            'Slot Unreserved',
                            null,
                            "{$user->name}, has cancelled the lesson on :date."
                        );

                        SendEmail::dispatch($slot->lesson->user->email, new SlotCancelledMail(
                            $user->name,
                            date('Y-m-d', strtotime($slot->date_time)),
                            date('h:i A', strtotime($slot->date_time)),
                            $slot->lesson->lesson_name,
                            $request->notes,
                        ));

                        if ($request->redirect == "1") {
                            return redirect()->back()->with('success', 'Slot Successfully Updated');
                        }
                        return response()->json(new SlotAPIResource($slot), 200);
                    }
                }
            }
            $isInstructorOrAdmin = ($user->type === Role::ROLE_INSTRUCTOR && $slot->lesson->created_by === $user->id) || $user->type === Role::ROLE_ADMIN;
            if (!$slot) {
                throw new Exception('Slot not found', 404);
            }


            if ($isInstructorOrAdmin) {
                $slot->update($validatedData);
                if ($slot->cancelled) {
                    $slot->update(['is_active' => false]);
                    $slot->update(['cancelled' => true]);
                    $this->sendSlotNotification(
                        $slot,
                        'Slot Cancelled',
                        'Your Slot with :instructor for the in-person lesson :lesson scheduled on :date has been cancelled.',
                        'Your Slot for the in-person lesson :lesson scheduled on :date has been cancelled.'
                    );
                }

                if ($request->unbook == '1'  && $request->filled('student_ids')) {
                    $unbookedStudents = $slot->student()->whereIn('students.id', $request->student_ids)->get();
                    $slot->student()->detach($request->student_ids);
                    foreach ($unbookedStudents as $student) {
                        Purchase::where('slot_id', $slot->id)->where('student_id', $student->id)->delete();
                        if (!$student->pivot->isFriend) {
                            $this->sendSlotNotification(
                                $slot,
                                'Slot Unbooked',
                                ':name has cancelled your lesson.',
                                null, // No instructor notification needed
                                $student // Send notification only to this student
                            );

                            SendEmail::dispatch($student->email, new SlotCancelledMail(
                                $user->name,
                                date('Y-m-d', strtotime($slot->date_time)),
                                date('h:i A', strtotime($slot->date_time)),
                                $slot->lesson->lesson_name,
                                $request->notes,
                            ));
                        }
                    }
                }

                $changes = $slot->getChanges();
                $hasStudents = $slot->student()->exists();

                // Send Reschedule Notification
                if (isset($changes['date_time']) && $slot->is_active && $hasStudents) {
                    $this->sendSlotNotification(
                        $slot,
                        'Slot Rescheduled',
                        'Your Slot with :instructor for the in-person lesson :lesson has been rescheduled to :date.',
                        'Your Slot for the in-person lesson :lesson has been rescheduled to :date.'
                    );
                }

                if ($request->redirect == "1") {
                    return redirect()->back()->with('success', 'Slot Successfully Updated');
                }
                return response()->json(new SlotAPIResource($slot), 200);
            }

            // // If the user is a student and is unbooking themselves
            if ($slot->student->contains($user->id)) {
                $slot->student()->detach($user->id);
                Purchase::where('slot_id', $slot->id)->where('student_id', $user->id)->delete();
                $this->sendSlotNotification(
                    $slot,
                    'Slot Unreserved',
                    null,
                    "{$user->name}, has cancelled the lesson on :date."
                );

                SendEmail::dispatch($slot->lesson->user->email, new SlotCancelledMail(
                    $user->name,
                    date('Y-m-d', strtotime($slot->date_time)),
                    date('h:i A', strtotime($slot->date_time)),
                    $slot->lesson->lesson_name,
                    $request->notes,
                ));

                if ($request->redirect == "1") {
                    return redirect()->back()->with('success', 'Slot Successfully Updated');
                }
                return response()->json(new SlotAPIResource($slot), 200);
            }

            throw new Exception('Unauthorized to update this slot', 403);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], $e->getCode() ?: 400);
        }
    }

    public function deleteSlot(Request $request)
    {
        try {
            if (Slots::find($request->id)->delete()) {
                return response()->json(['message' => 'Slot deleted'], 200);
            }
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], $e->getCode() ?: 400);
        }
    }

    public function bulkDelete(Request $request)
    {
        try {
            $ids = $request->input('ids', []);

            $purchases = Purchase::whereIn('slot_id', $ids)->get();
            if (!$purchases->isEmpty()) {
                return response()->json(
                    ['error' => 'Some slots are booked or completed and cannot be deleted.'],
                    400 // <-- send proper HTTP error
                );
            }

            Slots::whereIn('id', $ids)->delete();

            return response()->json(['message' => 'Selected slots deleted successfully.']);
        } catch (\Exception $e) {
            return response()->json(
                ['error' => $e->getMessage()],
                $e->getCode() ?: 400
            );
        }
    }

    public function getAllByInstructor(Request $request)
    {
        $request->validate([
            'instructor_id' => 'required',
        ]);
        $instructor = User::where('type', Role::ROLE_INSTRUCTOR)->find($request?->instructor_id);
        if ($instructor && Auth::user->can('manage-lessons')) {
            try {
                return Lesson::where('created_by', $instructor?->id);
            } catch (\Exception $e) {
                return redirect()->back()->with('errors', $e->getMessage());
            };
        } else {
            return throw new ValidationException(['No Instrucotr with the given ID']);
        }
    }

    public function getAll()
    {
        // dd("dd");
        try {
            if (Auth::user()->can('manage-lessons')) {
                $lessons = Lesson::where('active_status', true)->orderBy(request()->get('sortKey', 'updated_at'), request()->get('sortOrder', 'desc'));
                return LessonAPIResource::collection($lessons->paginate(request()->get('perPage')));
            } else {
                return response()->json(['error' => 'Unauthorized', 401]);
            }
        } catch (Error $e) {
            return throw new Exception($e->getMessage());
        }
    }

    public function getInstructorAll()
    {
        request()->validate([
            'id' => 'required',
        ]);
        try {
            if (Auth::user()->can('manage-lessons')) {
                return LessonAPIResource::collection(Lesson::where('created_by', request()?->id)->orderBy(request()->get('sortKey', 'updated_at'), request()->get('sortOrder', 'desc'))->get());
            } else {
                return response()->json(['error' => 'Unauthorized', 401]);
            }
        } catch (Error $e) {
            return throw new Exception($e->getMessage());
        }
    }

    // Other CRUD methods (edit, update, delete, etc.) go here
    public function showByInstructor($instructorId)
    {
        $instructor = Instructor::findOrFail($instructorId);
        $lessons = $instructor->lessons;
        return view('lessons.instructor_lessons', compact('lessons')); // Assuming you have a view named 'lessons.instructor_lessons'
    }
    public function destroy($lessonId)
    {
        $lesson = Lesson::with(['slots' => function ($query) {
            $query->where('is_active', true);
        }])
            ->findOrFail($lessonId);
        if (count($lesson->slots) > 0) {
            foreach ($lesson->slots as $slot) {
                $slot->update(['is_active' => false]);
            }
        }
        $lesson->update(['active_status' => false]);

        return redirect()->route('lesson.index')->with('success', 'Lesson disabled successfully!');
    }

    public function availabilityModal(Request $request)
    {
        // dd("ddd");
        if (Auth::user()->can('create-lessons')) {
                    $lesson = Lesson::find($request->get('lesson_id'));
                    if ($lesson) {
                        return view('admin.lessons.addSlot', compact('lesson'));
                    } else {
                        $lesson = Lesson::withMax('packages', 'number_of_slot')->whereIn('type', ['package', 'inPerson'])->where('created_by', Auth::user()->id)->where('active_status', true)->get()->toArray();
                        return view('admin.lessons.set-availability-modal', compact('lesson'));
                    }
        } else {
            return redirect()->back()->with('failed', __('Permission denied.'));
        }        
        
    }

    public function addAvailabilitySlotsfriday(Request $request)
    {
        try {
            // dd($request->all());
            $validatedData = $request->validate([
                'lesson_id' => 'required|array',
                'start_date' => 'required',
                'start_time' => 'required|array',
                'start_time.*' => 'required|date_format:H:i',
                'end_time' => 'required|array',
                'end_time.*' => 'required|date_format:H:i',
                'location'  => 'required|string|max:255',
            ]);

            $conflictErrors = [];
            $slotsToCreate = [];
            $slots = [];

            $lessons = Lesson::whereIn('id', $validatedData['lesson_id'])->get();
            $selectedDates = explode(",", $request->start_date);
            $startTime = $validatedData['start_time'];
            $endTime = $validatedData['end_time'];

            // Get current tenant ID
            $tenantId = Auth::user()->tenant_id;
            // dd($tenantId);
            foreach ($lessons as $lesson) {
                foreach ($selectedDates as $date) {

                    foreach ($startTime as $key => $startTimeVal) {

                        $slotStart = Carbon::createFromFormat('Y-m-d H:i', $date . ' ' . $startTimeVal);
                        $slotEnd   = Carbon::createFromFormat('Y-m-d H:i', $date . ' ' . $endTime[$key]);
                        $totalMinutes = $slotStart->diffInMinutes($slotEnd);

                        $lessonMinutes = $lesson->lesson_duration * 60;

                        $maxSlots = floor($totalMinutes / $lessonMinutes);

                        $currentSlotStart = $slotStart->copy();

                        // Check if any slot overlaps with this time range
                        for ($i = 0; $i < $maxSlots; $i++) {
                            $currentSlotEnd = $currentSlotStart->copy()->addMinutes($lessonMinutes)->subMinute(); // to allow adjacent slots

                            // $conflict = Slots::where('lesson_id', $lesson->id)
                            //     ->whereBetween('date_time', [$currentSlotStart, $currentSlotEnd])
                            //     ->exists();

                            $conflict = Slots::join('lessons', 'slots.lesson_id', '=', 'lessons.id')
                            ->where('slots.tenant_id', $tenantId)
                            ->where(function($query) use ($currentSlotStart, $currentSlotEnd) {
                                $query->whereBetween('slots.date_time', [$currentSlotStart, $currentSlotEnd->subMinute()]);
                                    // ->orWhere(function($q) use ($currentSlotStart) {
                                    //     $q->where('slots.date_time', '<', $currentSlotStart)
                                    //         ->whereRaw('DATE_ADD(slots.date_time, INTERVAL (lessons.lesson_duration * 60) MINUTE) > ?', [$currentSlotStart]);
                                    // });
                            })
                            ->exists();

                            if ($conflict) {
                            
                                $conflictErrors[] = "Slot conflict for lesson '{$lesson->lesson_name}' on {$date} at {$currentSlotStart->format('H:i')}.";
                            } else {
                                $slotsToCreate[] = [
                                    'lesson_id' => $lesson->id,
                                    'date_time' => $currentSlotStart->copy(),
                                    'location'  => $request->location
                                ];
                            }

                            $currentSlotStart->addMinutes($lessonMinutes);
                        }
                    }
                }
            }

            if (!empty($conflictErrors)) {
                return back()->withErrors(['conflicts' => $conflictErrors])->withInput();
            }

            // ✅ No conflicts — create all slots inside a DB transaction
            DB::transaction(function () use ($slotsToCreate) {
                foreach ($slotsToCreate as $slot) {
                    Slots::create($slot);
                }
            });

            // Send push notifications for new lessons
            $students = Student::whereHas('pushToken')
                ->with('pushToken')
                ->get()
                ->pluck('pushToken.token')
                ->toArray();

            if (!empty($students)) {
                foreach ($lessons as $lesson) {
                    $title = "New Lessons Available!";
                    $body  = "{$lesson->user->name} has created new lesson opportunities: {$lesson->lesson_name}. Check now!";
                    SendPushNotification::dispatch($students, $title, $body);
                }
            }

            // Return based on redirect parameter
            return $request->get('redirect') == 1
                ? redirect()->route('lesson.index')->with('success', 'Slots Successfully Added')
                : response()->json([
                    'message' => 'Consecutive Slots for the given range are successfully created',
                    'slots' => $slotsToCreate
                ]);
        } catch (\Exception $e) {
            return $request->get('redirect') == 1
                ? redirect()->back()->with('error', $e->getMessage())
                : response()->json(['error' => $e->getMessage()], $e->getCode() ?: 500);
        }
    }
    public function addAvailabilitySlots(Request $request)
{
    try {
        // dd($request->all());
        $validatedData = $request->validate([
            'lesson_id' => 'required|array',
            'start_date' => 'required',
            'start_time' => 'required|array',
            'start_time.*' => 'required|date_format:H:i',
            'end_time' => 'required|array',
            'end_time.*' => 'required|date_format:H:i',
            'location'  => 'required|string|max:255',
        ]);

        $conflictErrors = [];
        $slotsToCreate = [];
        $slots = [];

        $lessons = Lesson::whereIn('id', $validatedData['lesson_id'])->get();
        $selectedDates = explode(",", $request->start_date);
        $startTime = $validatedData['start_time'];
        $endTime = $validatedData['end_time'];

        // Get current tenant ID
        $tenantId = Auth::user()->tenant_id;
        
        foreach ($lessons as $lesson) {
            foreach ($selectedDates as $date) {

                foreach ($startTime as $key => $startTimeVal) {

                    $slotStart = Carbon::createFromFormat('Y-m-d H:i', $date . ' ' . $startTimeVal);
                    $slotEnd   = Carbon::createFromFormat('Y-m-d H:i', $date . ' ' . $endTime[$key]);
                    $totalMinutes = $slotStart->diffInMinutes($slotEnd);

                    $lessonMinutes = $lesson->lesson_duration * 60;

                    $maxSlots = floor($totalMinutes / $lessonMinutes);

                    $currentSlotStart = $slotStart->copy();

                    // Check if any slot overlaps with this time range
                    for ($i = 0; $i < $maxSlots; $i++) {
                        $currentSlotEnd = $currentSlotStart->copy()->addMinutes($lessonMinutes)->subMinute(); // to allow adjacent slots

                        $conflict = Slots::join('lessons', 'slots.lesson_id', '=', 'lessons.id')
                            ->where('slots.tenant_id', $tenantId)
                            ->where('slots.is_active', 1)
                            ->where(function($query) use ($currentSlotStart, $currentSlotEnd) {
                                $query->whereBetween('slots.date_time', [$currentSlotStart, $currentSlotEnd->subMinute()]);
                            })
                            ->exists();

                            dd($conflict);

                        if ($conflict) {
                            $conflictErrors[] = "Slot conflict for lesson '{$lesson->lesson_name}' on {$date} at {$currentSlotStart->format('H:i')}.";
                        } else {
                            $slotsToCreate[] = [
                                'lesson_id' => $lesson->id,
                                'date_time' => $currentSlotStart->copy(),
                                'location'  => $request->location,
                                'tenant_id' => $tenantId, // ADD THIS LINE - FIXED!
                                'is_active' => true // Also add this if needed
                            ];
                        }

                        $currentSlotStart->addMinutes($lessonMinutes);
                    }
                }
            }
        }

        if (!empty($conflictErrors)) {
            return back()->withErrors(['conflicts' => $conflictErrors])->withInput();
        }

        // ✅ No conflicts — create all slots inside a DB transaction
        DB::transaction(function () use ($slotsToCreate) {
            foreach ($slotsToCreate as $slot) {
                Slots::create($slot);
            }
        });

        // Send push notifications for new lessons
        $students = Student::whereHas('pushToken')
            ->with('pushToken')
            ->get()
            ->pluck('PushToken.token')
            ->toArray();

        if (!empty($students)) {
            foreach ($lessons as $lesson) {
                $title = "New Lessons Available!";
                $body  = "{$lesson->user->name} has created new lesson opportunities: {$lesson->lesson_name}. Check now!";
                SendPushNotification::dispatch($students, $title, $body);
            }
        }

        // Return based on redirect parameter
        return $request->get('redirect') == 1
            ? redirect()->route('lesson.index')->with('success', 'Slots Successfully Added')
            : response()->json([
                'message' => 'Consecutive Slots for the given range are successfully created',
                'slots' => $slotsToCreate
            ]);
    } catch (\Exception $e) {
        return $request->get('redirect') == 1
            ? redirect()->back()->with('error', $e->getMessage())
            : response()->json(['error' => $e->getMessage()], $e->getCode() ?: 500);
    }
}

    public function showScheduleLessonModal(Request $request)
    {
        $user = Auth::user();
        // dd($user);
        $lessons = Lesson::where('created_by', $user->id)->whereIn('type',['package','inPerson'])->where('active_status', true)->get();
        $students = Student::get(); // Adjust based on your user structure
        
        return view('admin.lessons.schedule-lesson-modal', compact('lessons', 'students'));
    }

    public function scheduleLesson1(Request $request) //for local
    {
        // Validate the request
        $request->validate([
            'lesson_id' => 'required|exists:lessons,id',
            'student_id' => 'sometimes|required|array',
            'lesson_date' => 'required|date',
            'start_time' => 'required',
            'end_time' => 'required',
            'location' => 'required|string',
            'note' => 'nullable|string'
        ]);

        try {
            // STEP 1: Create multiple availability slots
            $lesson = Lesson::find($request->lesson_id);
            // Get current tenant ID
            $tenantId = Auth::user()->tenant_id;
            // Calculate time frames
            $slotStart = Carbon::createFromFormat('Y-m-d H:i', $request->lesson_date . ' ' . $request->start_time);
            $slotEnd = Carbon::createFromFormat('Y-m-d H:i', $request->lesson_date . ' ' . $request->end_time);
            $totalMinutes = $slotStart->diffInMinutes($slotEnd);

            // Get lesson duration in minutes (assuming lesson_duration is in hours)
            $lessonMinutes = $lesson->lesson_duration * 60;

            // Calculate how many slots we can create
            $maxSlots = floor($totalMinutes / $lessonMinutes);

            $currentSlotStart = $slotStart->copy();
            $createdSlots = [];

            // Create multiple slots within the time range
            for ($i = 0; $i < $maxSlots; $i++) {
                $currentSlotEnd = $currentSlotStart->copy()->addMinutes($lessonMinutes);
                
                // Check for slot conflicts for this specific slot
                // $conflict = Slots::where('lesson_id', $request->lesson_id)
                //     ->whereBetween('date_time', [$currentSlotStart, $currentSlotEnd])
                //     ->exists();
                $conflict = Slots::join('lessons', 'slots.lesson_id', '=', 'lessons.id')
                ->where('slots.tenant_id', $tenantId)
                ->where(function($query) use ($currentSlotStart, $currentSlotEnd) {
                    $query->whereBetween('slots.date_time', [$currentSlotStart, $currentSlotEnd->subMinute()]);
                        // ->orWhere(function($q) use ($currentSlotStart) {
                        //     $q->where('slots.date_time', '<', $currentSlotStart)
                        //         ->whereRaw('DATE_ADD(slots.date_time, INTERVAL (lessons.lesson_duration * 60) MINUTE) > ?', [$currentSlotStart]);
                        // });
                })
                ->exists();

                if (!$conflict) {
                    // Create the slot
                    $slot = Slots::create([
                        'lesson_id' => $request->lesson_id,
                        'date_time' => $currentSlotStart,
                        'location' => $request->location,
                        'is_active' => true
                    ]);
                    $createdSlots[] = $slot;
                }

                // Move to next slot time
                $currentSlotStart->addMinutes($lessonMinutes);
            }

            if (empty($createdSlots)) {
                return response()->json([
                    'success' => false,
                    'message' => 'No available slots could be created due to conflicts.'
                ], 409);
            }

            // STEP 2: Book ALL created slots for selected student(s)
            $studentIds = $request->student_id ?? [];
            
            if (empty($studentIds)) {
                return response()->json([
                    'success' => false,
                    'message' => 'No students selected for booking.'
                ], 400);
            }

            $bookedSlotIds = [];
            
            foreach ($createdSlots as $slot) {
                // Attach selected student(s) to this slot (works for single or multiple students)
                $slot->student()->sync($studentIds);

                // Create purchase records for each selected student for this slot
                foreach ($studentIds as $studentId) {
                    Purchase::create([
                        'student_id'    => $studentId,
                        'instructor_id' => $lesson->created_by,
                        'lesson_id'     => $lesson->id,
                        'slot_id'       => $slot->id,
                        'coupon_id'     => null,
                        'tenenat_id'    => Auth::user()->tenant_id,
                        'total_amount'  => $lesson->lesson_price,
                        'status'        => Purchase::STATUS_INCOMPLETE,
                        'lessons_used'  => 0,
                        'purchased_slot' => 1
                    ]);

                    // Send notification for each student
                    // $this->sendSlotNotification(
                    //     $slot,
                    //     'Slot Booked',
                    //     'A slot has been booked for :date with :instructor for the in-person lesson :lesson.',
                    //     'A slot has been booked for :date with student ID ' . $studentId . ' for the in-person lesson :lesson.'
                    // );
                }

                $bookedSlotIds[] = $slot->id;
            }

            // Send emails to students
            // $studentEmails = Student::select('email')
            //     ->whereIn('id', $studentIds)
            //     ->pluck('email');

            // if (!$studentEmails->isEmpty()) {
            //     // Use the first booked slot for email details
            //     $firstSlot = $createdSlots[0];
            //     SendEmail::dispatch($studentEmails->toArray(), new SlotBookedByStudentMail(
            //         Auth::user()->name,
            //         date('Y-m-d', strtotime($firstSlot->date_time)),
            //         date('h:i A', strtotime($firstSlot->date_time)),
            //         $request->note ?? '',
            //     ));
            // }

            // Send push notifications for new lesson availability
            // $studentsWithTokens = Student::whereHas('pushToken')
            //     ->with('pushToken')
            //     ->get()
            //     ->pluck('pushToken.token')
            //     ->toArray();

            // if (!empty($studentsWithTokens)) {
            //     $title = "New Lesson Scheduled!";
            //     $body  = "{$lesson->user->name} has scheduled a new lesson: {$lesson->lesson_name}. Check now!";
            //     SendPushNotification::dispatch($studentsWithTokens, $title, $body);
            // }

            return response()->json([
                'success' => true,
                'message' => 'Lesson scheduled and booked successfully. Created ' . count($createdSlots) . ' slot(s) and booked ' . count($studentIds) . ' student(s) to all slots.',
                'slot_ids' => $bookedSlotIds,
                'total_slots_created' => count($createdSlots),
                'students_booked' => count($studentIds)
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error scheduling lesson: ' . $e->getMessage()
            ], 500);
        }
    }

    public function scheduleLesson(Request $request) //for live
    {
        // Validate the request
        $request->validate([
            'lesson_id' => 'required|exists:lessons,id',
            'student_id' => 'sometimes|required|array',
            'lesson_date' => 'required|date',
            'start_time' => 'required',
            'end_time' => 'required',
            'location' => 'required|string',
            'note' => 'nullable|string'
        ]);

        try {
            // STEP 1: Create multiple availability slots
            $lesson = Lesson::find($request->lesson_id);
            
            // Calculate time frames
            $slotStart = Carbon::createFromFormat('Y-m-d H:i', $request->lesson_date . ' ' . $request->start_time);
            $slotEnd = Carbon::createFromFormat('Y-m-d H:i', $request->lesson_date . ' ' . $request->end_time);
            $totalMinutes = $slotStart->diffInMinutes($slotEnd);

            // Get lesson duration in minutes (assuming lesson_duration is in hours)
            $lessonMinutes = $lesson->lesson_duration * 60;

            // Calculate how many slots we can create
            $maxSlots = floor($totalMinutes / $lessonMinutes);

            $currentSlotStart = $slotStart->copy();
            $createdSlots = [];

            // Create multiple slots within the time range
            for ($i = 0; $i < $maxSlots; $i++) {
                $currentSlotEnd = $currentSlotStart->copy()->addMinutes($lessonMinutes);
                
                // Check for slot conflicts for this specific slot
                // $conflict = Slots::where('lesson_id', $request->lesson_id)
                //     ->whereBetween('date_time', [$currentSlotStart, $currentSlotEnd])
                //     ->exists();
                $conflict = Slots::join('lessons', 'slots.lesson_id', '=', 'lessons.id')
                ->where(function($query) use ($currentSlotStart, $currentSlotEnd) {
                    $query->whereBetween('slots.date_time', [$currentSlotStart, $currentSlotEnd->subMinute()]);
                        // ->orWhere(function($q) use ($currentSlotStart) {
                        //     $q->where('slots.date_time', '<', $currentSlotStart)
                        //         ->whereRaw('DATE_ADD(slots.date_time, INTERVAL (lessons.lesson_duration * 60) MINUTE) > ?', [$currentSlotStart]);
                        // });
                })
                ->exists();

                if (!$conflict) {
                    // Create the slot
                    $slot = Slots::create([
                        'lesson_id' => $request->lesson_id,
                        'date_time' => $currentSlotStart,
                        'location' => $request->location,
                        'is_active' => true
                    ]);
                    $createdSlots[] = $slot;
                }

                // Move to next slot time
                $currentSlotStart->addMinutes($lessonMinutes);
            }

            if (empty($createdSlots)) {
                return response()->json([
                    'success' => false,
                    'message' => 'No available slots could be created due to conflicts.'
                ], 409);
            }

            // STEP 2: Book ALL created slots for selected student(s)
            $studentIds = $request->student_id ?? [];
            
            if (empty($studentIds)) {
                return response()->json([
                    'success' => false,
                    'message' => 'No students selected for booking.'
                ], 400);
            }

            $bookedSlotIds = [];
            
            foreach ($createdSlots as $slot) {
                // Attach selected student(s) to this slot (works for single or multiple students)
                $slot->student()->sync($studentIds);

                // Create purchase records for each selected student for this slot
                foreach ($studentIds as $studentId) {
                    Purchase::create([
                        'student_id'    => $studentId,
                        'instructor_id' => $lesson->created_by,
                        'lesson_id'     => $lesson->id,
                        'slot_id'       => $slot->id,
                        'coupon_id'     => null,
                        'tenenat_id'    => Auth::user()->tenant_id,
                        'total_amount'  => $lesson->lesson_price,
                        'status'        => Purchase::STATUS_INCOMPLETE,
                        'lessons_used'  => 0,
                        'purchased_slot' => 1
                    ]);

                    // Send notification for each student
                    $this->sendSlotNotification(
                        $slot,
                        'Slot Booked',
                        'A slot has been booked for :date with :instructor for the in-person lesson :lesson.',
                        'A slot has been booked for :date with student ID ' . $studentId . ' for the in-person lesson :lesson.'
                    );
                }

                $bookedSlotIds[] = $slot->id;
            }

            // Send emails to students
            $studentEmails = Student::select('email')
                ->whereIn('id', $studentIds)
                ->pluck('email');

            if (!$studentEmails->isEmpty()) {
                // Use the first booked slot for email details
                $firstSlot = $createdSlots[0];
                SendEmail::dispatch($studentEmails->toArray(), new SlotBookedByStudentMail(
                    Auth::user()->name,
                    date('Y-m-d', strtotime($firstSlot->date_time)),
                    date('h:i A', strtotime($firstSlot->date_time)),
                    $request->note ?? '',
                ));
            }

            // Send push notifications for new lesson availability
            $studentsWithTokens = Student::whereHas('pushToken')
                ->with('pushToken')
                ->get()
                ->pluck('pushToken.token')
                ->toArray();

            if (!empty($studentsWithTokens)) {
                $title = "New Lesson Scheduled!";
                $body  = "{$lesson->user->name} has scheduled a new lesson: {$lesson->lesson_name}. Check now!";
                SendPushNotification::dispatch($studentsWithTokens, $title, $body);
            }

            return response()->json([
                'success' => true,
                'message' => 'Lesson scheduled and booked successfully. Created ' . count($createdSlots) . ' slot(s) and booked ' . count($studentIds) . ' student(s) to all slots.',
                'slot_ids' => $bookedSlotIds,
                'total_slots_created' => count($createdSlots),
                'students_booked' => count($studentIds)
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error scheduling lesson: ' . $e->getMessage()
            ], 500);
        }
    }
}
