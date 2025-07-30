<?php

namespace App\Http\Controllers\Admin;

use App\Actions\SendEmail;
use App\Actions\SendPushNotification;
use App\DataTables\Admin\LessonDataTable;
use App\Facades\UtilityFacades;
use App\Http\Controllers\Controller;
use App\Http\Resources\LessonAPIResource;
use App\Http\Resources\SlotAPIResource;
use App\Mail\Admin\StudentPaymentLink;
use App\Mail\Admin\SlotBookedByStudentMail;
use App\Mail\Admin\SlotCancelledMail;
use App\Models\Instructor;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Stancl\Tenancy\Database\Models\Domain;
use App\Models\Lesson;
use App\Models\Purchase;
use App\Models\Slots;
use App\Models\Student;
use App\Traits\PurchaseTrait;
use Carbon\Carbon;
use DateInterval;
use DatePeriod;
use Error;
use Exception;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Str;
use App\Models\PackageLesson;
use Illuminate\Support\Facades\DB;

class LessonController extends Controller
{
    use PurchaseTrait;

    public function index(LessonDataTable $dataTable)
    {

        if (Auth::user()->can('manage-lessons')) {
            return $dataTable->render('admin.lessons.index');
        } else {
            return redirect()->back()->with('failed', __('Permission denied.'));
        }
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
            $validatedData = $request->validate([
                'lesson_name'          => 'required|string|max:255',
                'lesson_description'   => 'required|string',
                'lesson_price'         => 'required|numeric',
                'lesson_quantity'      => 'required|integer',
                'required_time'        => 'required|integer',
            ]);
        }
        if ($request->type === Lesson::LESSON_TYPE_INPERSON) {
            $validatedData = $request->validate([
                'lesson_name'          => 'required|string|max:255',
                'lesson_description'   => 'required|string',
                'lesson_price'         => 'required_if:is_package_lesson,0|numeric',
                'lesson_duration'      => 'required|numeric',
                'payment_method'       => ['required', 'in:online,cash'],
                'slots'                => 'array',
                'max_students'         => 'required|integer|min:1',
                'is_package_lesson'    => 'string',
            ]);
            $validatedData['lesson_quantity'] = 1;
            $validatedData['required_time'] = 0;
            !empty($validatedData['is_package_lesson']) && $validatedData['is_package_lesson'] == 1 ? $validatedData['is_package_lesson'] = true : $validatedData['is_package_lesson'] = false;
        }
        // Assuming 'created_by' is the ID of the currently authenticated instructor
        $validatedData['lesson_description'] = $_POST['lesson_description'];
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
        $lesson = Lesson::findOrFail($lessonId);
        $validatedData = $request->validate([
            'lesson_name'          => 'required|string|max:255',
            'lesson_description'   => 'required|string',
            'lesson_price'         => 'required_if:is_package_lesson,1|numeric',
            'lesson_quantity'      => 'integer',
            'required_time'        => 'integer',
            'lesson_duration'      => 'numeric',
            'payment_method'       => 'in:online,cash',
            'max_students'         => 'integer|min:1',
        ]);

        // Assuming 'created_by' is the ID of the currently authenticated instructor
        $validatedData['created_by'] = Auth::user()->id;
        $validatedData['lesson_description'] = $_POST['lesson_description'];

        $lesson->update($validatedData);
        if ($lesson->is_package_lesson == 1 && !empty($request->package_lesson)) {
            foreach ($request->package_lesson as $packages) {
                PackageLesson::create([
                    'tenant_id' => Auth::user()->tenant_id,
                    'lesson_id' => $lessonId,
                    'number_of_slot' => $packages['no_of_slot'],
                    'price' => $packages['price'],
                ]);
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

    public function manageSlots()
    {
        $allSlots = Slots::where('is_active', true)->get();
        if (Auth::user()->type === Role::ROLE_ADMIN) {
            $payment_method = Lesson::find(request()->get('lesson_id'))?->payment_method;
            $events = [];
            $resources = [];
            $instructorId = request()->get('instructor_id');

            if (!!$instructorId && $instructorId !== "-1")
                $slots = Slots::whereHas('lesson', function ($query) use ($instructorId) {
                    $query->where('created_by', $instructorId);
                })->where('is_active', true)->get();

            $type = Auth::user()->type;
            $uniqueEvents = [];
            foreach ($allSlots as $appointment) {
                if (isset($appointment->lesson->user->id)) {
                    $n = $appointment->lesson->lesson_duration;
                    $startDateTime = Carbon::parse($appointment->date_time);
                    $whole = floor($n);
                    $fraction = $n - $whole;
                    $minutes = $fraction * 60;
                    $endDateTime = $startDateTime->copy()->addHours($whole)->addMinutes($minutes);

                    $students = $appointment->student;
                    $colors =  $appointment->is_completed ? '#41d85f' : ($appointment->isFullyBooked() ?
                        '#c5b706' : '#0071ce');

                    $startDts = $startDateTime->format('h:i a');
                    $endDts = $endDateTime->format('h:i a');

                    $event = [
                        'title' => substr($appointment->lesson->lesson_name, 0, 10) .
                            ' (' . ($appointment->lesson->max_students - $appointment->availableSeats()) . '/' . $appointment->lesson->max_students . ') ',
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
                        'available_seats' => $appointment->availableSeats(),
                        'lesson' => $appointment->lesson,
                        'instructor' => $appointment->lesson->user,
                        'resourceId' => $appointment->lesson->user->id,
                        'className' => ($appointment->is_completed ? 'custom-completed-class' : ($appointment->isFullyBooked() ? 'custom-book-class' : 'custom-available-class')) . ' custom-event-class',
                    ];

                    if (!in_array($appointment->lesson->user->id, $resources)) {
                        $resources[$appointment->lesson->user->id] =
                            ['id' => $appointment->lesson->user->id, 'title' => $appointment->lesson->user->name, 'eventColor' => $colors];
                    }
                }
            }
            $events = array_values($uniqueEvents);
            $resources = array_values($resources);
            $lesson_id = request()->get('lesson_id');
            $instructors = User::where('type', Role::ROLE_INSTRUCTOR)->get();
            $students = Student::where('active_status', true)->where('isGuest', false)->get();
            return view('admin.lessons.manageSlots', compact('events', 'resources', 'lesson_id', 'type', 'payment_method', 'instructors', 'students'));
        }
        if (Auth::user()->type === Role::ROLE_INSTRUCTOR) {
            $slots = Slots::whereHas('lesson', function ($query) {
                $query->where('created_by', Auth::user()->id);
            })->where('is_active', true)->get();

            $payment_method = Lesson::find(request()->get('lesson_id'))?->payment_method;
            $events = [];
            $lessonId = request()->get('lesson_id');
            $type = Auth::user()->type;

            if (!!$lessonId && $lessonId !== "-1")
                $slots = Slots::whereHas('lesson', function ($query) use ($lessonId) {
                    $query->where('id', $lessonId);
                })->where('is_active', true)->get();

            $bookedDateTimes = $allSlots->filter(function ($slot) {
                return $slot->student->isNotEmpty();
            })->pluck('date_time')->unique();

            $filteredSlots = $slots->filter(function ($slot) use ($bookedDateTimes) {
                if ($slot->student->isNotEmpty()) {
                    return true;
                }
                return !$bookedDateTimes->contains($slot->date_time);
            });

            $uniqueEvents = [];
            foreach ($filteredSlots as $appointment) {
                $n = $appointment->lesson->lesson_duration;
                $whole = floor($n);
                $fraction = $n - $whole;
                $intervalString = $whole . ' hours' . ' + ' . $fraction * 60 . ' minutes';
                $startDateTime = Carbon::parse($appointment->date_time);

                $minutes = $fraction * 60;
                $endDateTime = $startDateTime->copy()->addHours($whole)->addMinutes($minutes);

                $students = $appointment->student;
                $colors =  $appointment->is_completed ? '#41d85f' : ($appointment->isFullyBooked() ?
                    '#c5b706' : '#0071ce');

                $startDts = $startDateTime->format('h:i a');
                $endDts = $endDateTime->format('h:i a');

                $students = $appointment->student;

                $colors =  $appointment->is_completed ? '#41d85f' : (($type == Role::ROLE_INSTRUCTOR && $appointment->isFullyBooked() ||
                    $type == Role::ROLE_STUDENT && $students->contains('id', Auth::user()->id)) ?
                    '#c5b706' : '#0071ce');
                $className = $appointment->is_completed ? 'custom-completed-class' : (($type == Role::ROLE_INSTRUCTOR && $appointment->isFullyBooked() ||
                    $type == Role::ROLE_STUDENT && $students->contains('id', Auth::user()->id))
                    ? 'custom-book-class' : 'custom-available-class') . ' custom-event-class';

                $uniqueEvents[] = [
                    'title' => substr($appointment->lesson->lesson_name, 0, 10) .
                        ' (' . ($appointment->lesson->max_students - $appointment->availableSeats()) . '/' . $appointment->lesson->max_students . ') ',
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
                    'available_seats' => $appointment->availableSeats(),
                    'lesson' => $appointment->lesson,
                    'instructor' => $appointment->lesson->user,
                    'resourceId' => $appointment->lesson->user->id,
                    'className' => ($appointment->is_completed ? 'custom-completed-class' : ($appointment->isFullyBooked() ? 'custom-book-class' : 'custom-available-class')) . ' custom-event-class',
                ];
            }
            $events = array_values($uniqueEvents);
            $lesson_id = request()->get('lesson_id');
            $lessons = Lesson::where('created_by', Auth::user()->id)->where('active_status', 1)->where('type', Lesson::LESSON_TYPE_INPERSON)->get();
            $students = Student::where('active_status', true)->where('isGuest', false)->get();
            return view('admin.lessons.instructorSlots', compact('events', 'lesson_id', 'type', 'payment_method', 'lessons', 'students'));
        } else {
            return redirect()->back()->with('failed', __('Permission denied.'));
        }
    }

    public function viewSlots()
    {
        if (Auth::user()->can('manage-lessons')) {
            $lesson = Lesson::findOrFail(request()->get('lesson_id'));
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

            $bookedDateTimes = $allSlots->filter(function ($slot) {
                return $slot->student->isNotEmpty();
            })->pluck('date_time')->unique();

            $filteredSlots = $slots->filter(function ($slot) use ($bookedDateTimes) {
                if ($slot->student->isNotEmpty()) {
                    return true;
                }
                return !$bookedDateTimes->contains($slot->date_time);
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
            return view('admin.lessons.viewSlots', compact('events', 'lesson_id', 'type', 'authId', 'students', 'lesson'));
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
                    date('h:i A', strtotime($slot->date_time))
                ));
            }

            if (request()->redirect == 1) {
                return redirect()->route('slot.view', ['lesson_id' => $slot?->lesson_id])
                    ->with('success', 'Slot Successfully Booked.');
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
            } elseif ($slot->lesson->payment_method == Lesson::LESSON_PAYMENT_CASH) {
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

        return request()->redirect == 1
            ? redirect()->route('slot.view', ['lesson_id' => $slot->lesson_id])->with('success', 'Purchase Successful.')
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
                'slot_id'      => 'required|integer',
                'date_time'    => 'date',
                'location'     => 'string',
                'is_completed' => 'boolean',
                'is_active'    => 'boolean',
                'cancelled'    => 'boolean',
                'unbook'       => 'boolean',
                'student_ids'  => 'array',
                'student_ids.*' => 'integer|exists:students,id',
            ]);

            $slot = Slots::find($request->slot_id);
            if (!$slot) {
                throw new Exception('Slot not found', 404);
            }

            $user = Auth::user();
            $isInstructorOrAdmin = ($user->type === Role::ROLE_INSTRUCTOR && $slot->lesson->created_by === $user->id) || $user->type === Role::ROLE_ADMIN;

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
                                $slot->lesson->lesson_name
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
                    $slot->lesson->lesson_name
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
        $lesson = Lesson::findOrFail($lessonId);
        $lesson->update(['active_status' => false]);

        return redirect()->route('lesson.index')->with('success', 'Lesson disabled successfully!');
    }
    public function addAvailabilitySlots(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'lesson_id' => 'required|array',
                'start_date' => 'required',
                'start_time' => 'required|date_format:H:i',
                'end_time' => 'required|date_format:H:i',
                'location'  => 'required|string|max:255',
            ]);

            $conflictErrors = [];
            $slotsToCreate = [];
            $slots = [];

            $lessons = Lesson::whereIn('id', $validatedData['lesson_id'])->get();
            $selectedDates = explode(",", $request->start_date);
            foreach ($lessons as $lesson) {
                foreach ($selectedDates as $date) {

                    $slotStart = Carbon::createFromFormat('Y-m-d H:i', $date . ' ' . $validatedData['start_time']);
                    $slotEnd   = Carbon::createFromFormat('Y-m-d H:i', $date . ' ' . $validatedData['end_time']);
                    $totalMinutes = $slotStart->diffInMinutes($slotEnd);

                    $lessonMinutes = $lesson->lesson_duration * 60;

                    $maxSlots = floor($totalMinutes / $lessonMinutes);

                    $currentSlotStart = $slotStart->copy();

                    // Check if any slot overlaps with this time range
                    for ($i = 0; $i < $maxSlots; $i++) {
                        $currentSlotEnd = $currentSlotStart->copy()->addMinutes($lessonMinutes)->subMinute(); // to allow adjacent slots

                        $conflict = Slots::where('lesson_id', $lesson->id)
                            ->whereBetween('date_time', [$currentSlotStart, $currentSlotEnd])
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
}
