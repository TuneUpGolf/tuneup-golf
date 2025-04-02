<?php

namespace App\Http\Controllers\Admin;

use App\Actions\SendEmail;
use App\Actions\SendPushNotification;
use App\Actions\SendSMS;
use App\DataTables\Admin\LessonDataTable;
use App\Facades\UtilityFacades;
use App\Http\Controllers\Controller;
use App\Http\Resources\LessonAPIResource;
use App\Mail\Admin\StudentPaymentLink;
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

        if ($request->type === Lesson::LESSON_PAYMENT_ONLINE)
            $validatedData = $request->validate([
                'lesson_name'          => 'required|string|max:255',
                'lesson_description'   => 'required|string',
                'lesson_price'         => 'required|numeric',
                'lesson_quantity'      => 'required|integer',
                'required_time'        => 'required|integer',
            ]);
        if ($request->type === Lesson::LESSON_TYPE_INPERSON) {
            $validatedData = $request->validate([
                'lesson_name'          => 'required|string|max:255',
                'lesson_description'   => 'required|string',
                'lesson_price'         => 'required|numeric',
                'lesson_duration'      => 'required|numeric',
                'payment_method'       => ['required', 'in:online,cash,both'],
                'slots'                => 'array',
            ]);
            $validatedData['lesson_quantity'] = 1;
            $validatedData['required_time'] = 0;
        }
        // Assuming 'created_by' is the ID of the currently authenticated instructor
        $validatedData['created_by'] = Auth::user()->id;
        $validatedData['type'] = $request->type;
        $validatedData['payment_method'] = $request->type === Lesson::LESSON_TYPE_INPERSON ? $request->payment_method : Lesson::LESSON_PAYMENT_ONLINE;
        $validatedData['tenant_id'] =
            $lesson = Lesson::create($validatedData);

        return redirect()->route('lesson.index', $lesson)->with('success', 'Lesson created successfully.');
    }

    public function update(Request $request, $lessonId)
    {
        $lesson = Lesson::findOrFail($lessonId);

        $validatedData = $request->validate([
            'lesson_name'          => 'required|string|max:255',
            'lesson_description'   => 'required|string',
            'lesson_price'         => 'required|numeric',
            'lesson_quantity'      => 'integer',
            'required_time'        => 'integer',
            'lesson_duration'      => 'numeric',
            'payment_method'       => 'in:online,cash,both',
        ]);

        // Assuming 'created_by' is the ID of the currently authenticated instructor
        $validatedData['created_by'] = Auth::user()->id;

        $lesson->update($validatedData);

        return redirect()->route('lesson.index', $lesson)->with('success', 'Lesson updated successfully.');
    }

    public function edit($id)
    {
        if (Auth::user()->can('edit-lessons')) {
            $user   = Lesson::find($id);
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
            return view('admin.lessons.addSlot', compact('lesson'));
        } else {
            return redirect()->back()->with('failed', __('Permission denied.'));
        }
    }

    public function manageSlots()
    {
        if (Auth::user()->type === Role::ROLE_ADMIN) {
            $slots = Slots::where('is_active', true)->get();
            $payment_method = Lesson::find(request()->get('lesson_id'))?->payment_method;
            $events = [];
            $instructorId = request()->get('instructor_id');

            if (!!$instructorId && $instructorId !== "-1")
                $slots = Slots::whereHas('lesson', function ($query) use ($instructorId) {
                    $query->where('created_by', $instructorId);
                })->where('is_active', true)->get();

            foreach ($slots as $appointment) {

                $n = $appointment->lesson->lesson_duration;
                $whole = floor($n);
                $fraction = $n - $whole;
                $intervalString = $whole . ' hours' . ' + ' . $fraction * 60 . ' minutes';

                $student = Student::find($appointment->student_id);

                array_push($events, [
                    'title' => $appointment->lesson->lesson_name,
                    'start' => $appointment->date_time,
                    'end' => date("Y-m-d H:i:s", strtotime($appointment->date_time . " +" . $intervalString)),
                    'slot_id' => $appointment->id,
                    'color' => $appointment->is_completed ? '#41d85f' : (!!$appointment->student_id ? '#f7e50a' : '#0071ce'),
                    'is_completed' => $appointment->is_completed,
                    'is_student_assigned' => isset($appointment->student_id),
                    'student' => $appointment->student,
                    'slot' => $appointment,
                    'lesson' => $appointment->lesson,
                    'instructor' => $appointment->lesson->user,
                    'className' => ($appointment->is_completed ? 'custom-completed-class' : (!!$appointment->student_id ? 'custom-book-class' : 'custom-available-class')) . ' custom-event-class',
                ]);
            }

            $lesson_id = request()->get('lesson_id');
            $type = Auth::user()->type;
            $instructors = User::where('type', Role::ROLE_INSTRUCTOR)->get();
            $students = Student::where('active_status', true)->where('isGuest', false)->get();
            return view('admin.lessons.manageSlots', compact('events', 'lesson_id', 'type', 'payment_method', 'instructors', 'students'));
        }
        if (Auth::user()->type === Role::ROLE_INSTRUCTOR) {
            $slots = Slots::whereHas('lesson', function ($query) {
                $query->where('created_by', Auth::user()->id);
            })->where('is_active', true)->get();

            $payment_method = Lesson::find(request()->get('lesson_id'))?->payment_method;
            $events = [];
            $lessonId = request()->get('lesson_id');

            if (!!$lessonId && $lessonId !== "-1")
                $slots = Slots::whereHas('lesson', function ($query) use ($lessonId) {
                    $query->where('id', $lessonId);
                })->where('is_active', true)->get();

            foreach ($slots as $appointment) {

                $n = $appointment->lesson->lesson_duration;
                $whole = floor($n);
                $fraction = $n - $whole;
                $intervalString = $whole . ' hours' . ' + ' . $fraction * 60 . ' minutes';

                array_push($events, [
                    'title' => $appointment->lesson->lesson_name,
                    'start' => $appointment->date_time,
                    'end' => date("Y-m-d H:i:s", strtotime($appointment->date_time . " +" . $intervalString)),
                    'slot_id' => $appointment->id,
                    'color' => $appointment->is_completed ? '#41d85f' : (!!$appointment->student_id ? '#f7e50a' : '#0071ce'),
                    'is_completed' => $appointment->is_completed,
                    'is_student_assigned' => isset($appointment->student_id),
                    'payment_method' => $appointment->lesson->payment_method,
                    'student' => $appointment->student,
                    'slot' => $appointment,
                    'lesson' => $appointment->lesson,
                    'instructor' => $appointment->lesson->user,
                    'className' => ($appointment->is_completed ? 'custom-completed-class' : (!!$appointment->student_id ? 'custom-book-class' : 'custom-available-class')) . ' custom-event-class',
                ]);
            }

            $lesson_id = request()->get('lesson_id');
            $type = Auth::user()->type;
            $lessons = Lesson::where('created_by', Auth::user()->id)->where('type', Lesson::LESSON_TYPE_INPERSON)->get();
            $students = Student::where('active_status', true)->where('isGuest', false)->get();
            return view('admin.lessons.instructorSlots', compact('events', 'lesson_id', 'type', 'payment_method', 'lessons', 'students'));
        } else {
            return redirect()->back()->with('failed', __('Permission denied.'));
        }
    }

    public function viewSlots()
    {
        if (Auth::user()->can('manage-lessons')) {
            $slots = Slots::where('lesson_id', request()->get('lesson_id'))->with('lesson')->get();
            $payment_method = Lesson::find(request()->get('lesson_id'))?->payment_method;
            $events = [];

            foreach ($slots as $appointment) {

                $n = $appointment->lesson->lesson_duration;
                $whole = floor($n);
                $fraction = $n - $whole;
                $intervalString = $whole . ' hours' . ' + ' . $fraction * 60 . ' minutes';

                array_push($events, [
                    'title' => $appointment->location,
                    'start' => $appointment->date_time,
                    'end' => date("Y-m-d H:i:s", strtotime($appointment->date_time . " +" . $intervalString)),
                    'slot_id' => $appointment->id,
                    'color' => $appointment->is_completed ? '#41d85f' : (!!$appointment->student_id ? '#f7e50a' : '#0071ce'),
                    'is_completed' => $appointment->is_completed,
                    'is_student_assigned' => isset($appointment->student_id),
                    'className' => ($appointment->is_completed ? 'custom-completed-class' : (!!$appointment->student_id ? 'custom-book-class' : 'custom-available-class')) . ' custom-event-class',
                ]);
            }

            $lesson_id = request()->get('lesson_id');
            $type = Auth::user()->type;

            return view('admin.lessons.viewSlots', compact('events', 'lesson_id', 'type', 'payment_method'));
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
                    'payment_method'       => ['required', 'in:online,cash,both'],
                    'slots'                => 'array',
                ]);

                $validatedData['created_by'] = Auth::user()->id;
                $validatedData['tenant_id'] = Auth::user()->tenant_id;
                if ($validatedData['type'] === Lesson::LESSON_TYPE_INPERSON) {
                    $validatedData['lesson_quantity'] = 1;
                    $validatedData['required_time'] = 0;
                }
                $lesson = Lesson::create($validatedData);

                if (isset($validatedData['slots']) && $lesson->type == Lesson::LESSON_TYPE_INPERSON) {
                    foreach ($validatedData['slots'] as $slot) {
                        Slots::create(['lesson_id' => $lesson->id, 'date_time' => Carbon::parse($slot)]);
                    }
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
        ]);

        $lesson = Lesson::find(request()->id);

        if (Auth::user()->type == Role::ROLE_INSTRUCTOR && Auth::user()->active_status == 1 && !!$lesson) {
            try {
                if ($lesson->created_by == Auth::user()->id) {
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
            if (!!$lesson && $lesson->type == Lesson::LESSON_TYPE_INPERSON) {
                Slots::create($validatedData);
                if ($request->get('redirect') == 1) {
                    return  redirect()->route('slot.view', ['lesson_id' => $lesson->id])->with('success', __('Slot Successfully Added'));
                }
                return response()->json('Slot successfully created against the lesson');
            } else throw new Exception('InPerson lesson not found for lesson id : ' . $request->lesson_id, 404);
        } catch (\Exception $e) {
            return throw new Exception($e->getMessage(), $e->getCode());
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

            if (!!$lesson && $lesson->type == Lesson::LESSON_TYPE_INPERSON) {

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
                    if ($temp->format('Y-m-d') === $dt->format('Y-m-d') && strtotime($dt->format('H:i')) >= strtotime($validatedData['start_time']) &&  (strtotime($endTime) <= strtotime($validatedData['end_time']))) {
                        $slotData = array(
                            'lesson_id' => $request->get('lesson_id'),
                            'date_time' => $dt->format("Y-m-d H:i:s"),
                            'location'  => $request->get('location')
                        );
                        $slot = Slots::updateOrCreate($slotData);
                        array_push($slots, $slot);
                    }
                }
                if ($request->get('redirect') == 1) {
                    return  redirect()->route('slot.view', ['lesson_id' => $lesson->id])->with('success', __('Slots Successfully Added'));
                }
                return response()->json(['message' => 'Consecutive Slots for the given range are successfully created', 'slots' => $slots]);
            } else throw new Exception('InPerson lesson not found for lesson id : ' . $request->lesson_id, 404);
        } catch (\Exception $e) {
            return throw new Exception($e->getMessage(), $e->getCode());
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

            if ($request->isGuest != "false") {
                $randomPassword                = Str::random(10);
                $userData['name']              = $request->guestName;
                $userData['email']             = $request->guestEmail;
                $userData['uuid']              = Str::uuid();
                $userData['password']          = Hash::make($randomPassword);
                $userData['type']              = Role::ROLE_STUDENT;
                $userData['isGuest']           = true;
                $userData['created_by']        = Auth::user()->id;
                $userData['email_verified_at'] = (UtilityFacades::getsettings('email_verification') == '1') ? null : Carbon::now()->toDateTimeString();
                $userData['phone_verified_at'] = (UtilityFacades::getsettings('phone_verification') == '1') ? null : Carbon::now()->toDateTimeString();
                $userData['phone']             = str_replace(' ', '', $request->guestPhone);
                $user                          = Student::create($userData);
                $user->assignRole(Role::ROLE_STUDENT);
                $slot->student_id = $user->id;
                $slot->save();
            } else {
                $slot['student_id'] = $request->get('student_Id');
                $slot->save();
            }

            $newPurchase = new Purchase([
                'student_id' => $slot?->student_id,
                'instructor_id' => $slot->lesson->created_by,
                'lesson_id' => $slot->lesson_id,
                'slot_id' => $slot->id,
                'coupon_id' => null,
                'tenenat_id' => Auth::user()->tenant_id,
            ]);

            $newPurchase->total_amount = $slot->lesson->lesson_price;
            $newPurchase->status = Purchase::STATUS_INCOMPLETE;
            $newPurchase->lessons_used = 0;
            $newPurchase->save();
            $this->sendBookingNotifications($slot);
            if (request()->redirect == 1) {
                return redirect()->route('slot.view', ['lesson_id' => $slot?->lesson_id])->with('success', 'Slot Successfully Booked.');
            }
        } catch (\Exception $e) {
            return throw new Exception($e->getMessage(), $e->getCode());
        }
    }

    public function bookSlotApi() //Student
    {
        try {
            request()->validate([
                'slot_id'  => 'required',
                'student_id' => 'exists:students,id'
            ]);

            $slot = Slots::find(request()->slot_id);


            if (Auth::user()->type == Role::ROLE_STUDENT && Auth::user()->active_status == 1 && !!$slot) {
                if (!isset($slot->student_id)) {
                    $slot->student_id = Auth::user()->id;
                    $slot->update();
                    $newPurchase = new Purchase([
                        'student_id' => $slot?->student_id,
                        'instructor_id' => $slot->lesson->created_by,
                        'lesson_id' => $slot->lesson_id,
                        'slot_id' => $slot->id,
                        'coupon_id' => null,
                        'tenenat_id' => Auth::user()->tenant_id,
                    ]);
                    $newPurchase->total_amount = $slot->lesson->lesson_price;
                    $newPurchase->status = Purchase::STATUS_INCOMPLETE;
                    $newPurchase->lessons_used = 0;
                    $newPurchase->save();
                    $this->sendBookingNotifications($slot);
                    if (request()->redirect == 1) {
                        return redirect()->route('slot.view', ['lesson_id' => $slot?->lesson_id])->with('success', 'Slot Successfully Booked.');
                    }
                    return response()->json(['message' => 'Slot successfully reserved.', 'slot' => $slot], 200);
                } else
                    return response()->json('slot is already reserved.', 200);
            }
            if (Auth::user()->type == Role::ROLE_INSTRUCTOR &&  Auth::user()->active_status == 1 && !!$slot) {
                if (!isset($slot->student_id)) {
                    $slot->student_id = request()->get('student_id');
                    $slot->update();
                    $newPurchase = new Purchase([
                        'student_id' => $slot->student_id,
                        'instructor_id' => $slot->lesson->created_by,
                        'lesson_id' => $slot->lesson_id,
                        'slot_id' => $slot->id,
                        'coupon_id' => null,
                        'tenenat_id' => Auth::user()->tenant_id,
                    ]);
                    $newPurchase->total_amount = $slot->lesson->lesson_price;
                    $newPurchase->status = Purchase::STATUS_INCOMPLETE;
                    $newPurchase->lessons_used = 0;
                    $newPurchase->save();
                    $this->sendBookingNotifications($slot);
                    if (request()->redirect == 1) {
                        return redirect()->route('slot.view', ['lesson_id' => $slot->lesson_id])->with('success', 'Slot Successfully Booked.');
                    }
                    return response()->json(['message' => 'Slot successfully booked for student.', 'slot' => $slot], 200);
                }
            }

            return response('Unauthorized', 401);
        } catch (\Exception $e) {
            return throw new Exception($e->getMessage());
        }
    }

    function sendBookingNotifications(Slots $slot)
    {
        $slot->load('student');
        $slot->load('lesson');
        $date = Carbon::createFromFormat('Y-m-d H:i:s', $slot?->date_time);
        $messageStudent = __(
            'A slot has been booked for ' . $date->toDayDateTimeString() . ' with ' . $slot?->lesson?->user?->name . ' for the in-person lesson ' . $slot?->lesson?->lesson_name
        );
        $messageInstructor = __('A slot has been booked for ' . $date->toDayDateTimeString() . ' with ' . $slot?->student?->name . ' for the in-person lesson ' . $slot?->lesson?->lesson_name, [
            'name' => $slot->student['name'],
        ]);
        if (isset($slot?->student?->pushToken?->token))
            SendPushNotification::dispatch($slot?->student?->pushToken?->token, 'Slot Booked', $messageStudent);
        if (isset($slot?->lesson?->user?->pushToken?->token))
            SendPushNotification::dispatch($slot?->lesson?->user?->pushToken?->token, 'Slot Booked', $messageInstructor);

        $userPhone = Str::of($slot->student['dial_code'])->append($slot->student['phone'])->value();
        $userPhone = str_replace(array('(', ')'), '', $userPhone);
        $instructorPhone = Str::of($slot->lesson->user['dial_code'])->append($slot->lesson->user['phone'])->value();
        $instructorPhone = str_replace(array('(', ')'), '', $instructorPhone);
        SendSMS::dispatch($userPhone, $messageStudent);
        SendSMS::dispatch($instructorPhone, $messageInstructor);
    }


    function sendCompleteSlotNotifications(Slots $slot)
    {
        $slot->load('student');
        $slot->load('lesson');
        $date = Carbon::createFromFormat('Y-m-d H:i:s', $slot?->date_time);
        $messageStudent = __(
            'Your Slot with ' . $slot?->lesson?->user?->name . 'at' . $date->toDayDateTimeString() . ' for the in-person lesson ' . $slot?->lesson?->lesson_name . 'has been completed.'
        );
        $messageInstructor = __('Your Slot with for the in-person lesson ' . $slot?->lesson?->lesson_name . 'at ' . $date->toDayDateTimeString() . ' with ' . $slot?->student?->name . 'has been completed');
        if (isset($slot?->student?->pushToken?->token))
            SendPushNotification::dispatch($slot?->student?->pushToken?->token, 'Slot Completed', $messageStudent);
        if (isset($slot?->lesson?->user?->pushToken?->token))
            SendPushNotification::dispatch($slot?->lesson?->user?->pushToken?->token, 'Slot Completed', $messageInstructor);

        $userPhone = Str::of($slot->student['dial_code'])->append($slot->student['phone'])->value();
        $userPhone = str_replace(array('(', ')'), '', $userPhone);
        $instructorPhone = Str::of($slot->lesson->user['dial_code'])->append($slot->lesson->user['phone'])->value();
        $instructorPhone = str_replace(array('(', ')'), '', $instructorPhone);
        SendSMS::dispatch($userPhone, $messageStudent);
        SendSMS::dispatch($instructorPhone, $messageInstructor);
    }

    function sendReschuduleSlotNotifications(Slots $slot)
    {
        $slot->load('student');
        $slot->load('lesson');
        $date = Carbon::createFromFormat('Y-m-d H:i:s', $slot?->date_time);
        $messageStudent = __(
            'Your Slot with ' . $slot?->lesson?->user?->name . ' for the in-person lesson ' . $slot?->lesson?->lesson_name . ' has been rescheduled to ' . $date->toDayDateTimeString()
        );
        $messageInstructor = __('Your Slot for the in-person lesson ' . $slot?->lesson?->lesson_name .  ' with ' . $slot?->student?->name . 'has been rescheduled to ' . $date->toDayDateTimeString());
        if (isset($slot?->student?->pushToken?->token))
            SendPushNotification::dispatch($slot?->student?->pushToken?->token, 'Slot Rescheduled', $messageStudent);
        if (isset($slot?->lesson?->user?->pushToken?->token))
            SendPushNotification::dispatch($slot?->lesson?->user?->pushToken?->token, 'Slot Rescheduled', $messageInstructor);

        $userPhone = Str::of($slot->student['dial_code'])->append($slot->student['phone'])->value();
        $userPhone = str_replace(array('(', ')'), '', $userPhone);
        $instructorPhone = Str::of($slot->lesson->user['dial_code'])->append($slot->lesson->user['phone'])->value();
        $instructorPhone = str_replace(array('(', ')'), '', $instructorPhone);
        SendSMS::dispatch($userPhone, $messageStudent);
        SendSMS::dispatch($instructorPhone, $messageInstructor);
    }

    function sendUnreservedNotifications(Slots $slot, Student $student)
    {
        $slot->load('student');
        $slot->load('lesson');
        $messageStudent = __(
            'Your Slot with ' . $slot?->lesson?->user?->name . ' for the in-person lesson ' . $slot?->lesson?->lesson_name . ' has been unreserved.'
        );
        $messageInstructor = __('Your Slot for the in-person lesson ' . $slot?->lesson?->lesson_name .  ' with ' . $student?->name . 'has been unreserved.');
        if (isset($student?->pushToken?->token))
            SendPushNotification::dispatch($slot?->student?->pushToken?->token, 'Slot Rescheduled', $messageStudent);
        if (isset($slot?->lesson?->user?->pushToken?->token))
            SendPushNotification::dispatch($slot?->lesson?->user?->pushToken?->token, 'Slot Rescheduled', $messageInstructor);

        $userPhone = Str::of($student['dial_code'])->append($student['phone'])->value();
        $userPhone = str_replace(array('(', ')'), '', $userPhone);
        $instructorPhone = Str::of($slot->lesson->user['dial_code'])->append($slot->lesson->user['phone'])->value();
        $instructorPhone = str_replace(array('(', ')'), '', $instructorPhone);
        SendSMS::dispatch($userPhone, $messageStudent);
        SendSMS::dispatch($instructorPhone, $messageInstructor);
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

            if ($user->type == Role::ROLE_INSTRUCTOR && $user->id == $slot->lesson->created_by) {
                $purchase = Purchase::where('student_id', $slot->student_id)->where('slot_id', $slot->id)->first();
                if (($slot->lesson->payment_method === Lesson::LESSON_PAYMENT_BOTH && request()->payment_method === Lesson::LESSON_PAYMENT_ONLINE) || $slot->lesson->payment_method === Lesson::LESSON_PAYMENT_ONLINE) {
                    $session =  $this->createSessionForPayment($purchase, false, $slot->id);
                    SendEmail::dispatch($slot->student->email, new StudentPaymentLink($purchase, $session->url));
                    $slot->is_completed = false;
                    $slot->save();

                    if (request()->get('redirect') == 1)
                        return redirect()->back()->with('success', 'Payment link sent to student');

                    return response()->json(['session_link' => $session->link, 'slot' => $slot, 'message' => 'slot successfully marked as completed']);
                }
                $purchase->status = Purchase::STATUS_COMPLETE;
                $purchase->save();
                $slot->is_completed = true;
                $slot->save();
                $this->sendCompleteSlotNotifications($slot);

                if (request()->get('redirect') == 1) {
                    return redirect()->back()->with('success', 'Slot Successfully Completed.');
                }
                return response()->json(['slot' => $slot, 'message' => 'slot successfully marked as completed']);
            } else {
                throw new Exception('UnAuthorized', 404);
            }
        } catch (\Exception $e) {
            return throw new Exception($e->getMessage(), $e->getCode());
        }
    }

    public function getSlots(Request $request)
    {
        try {
            $request->validate([
                'lesson_id' => 'required|integer',
            ]);
            $lesson = Lesson::find($request->lesson_id);
            if (!!$lesson && $lesson->type == Lesson::LESSON_TYPE_INPERSON) {
                $slots = Slots::where('is_active', true)->where('lesson_id', $request->lesson_id)->whereDate('date_time', '>=', Carbon::today())->get();
                return response()->json($slots, 200);
            } else throw new Exception('InPerson lesson not found for lesson id : ' . $request->lesson_id, 404);
        } catch (\Exception $e) {
            return throw new Exception($e->getMessage(), $e->getCode());
        }
    }

    public function getAllSlotsInstructor(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'instructor_id' => 'required|integer',
                'start_date' => 'required|date_format:Y-m-d',
                'end_date' => 'required|date_format:Y-m-d',

            ]);
            $instructor_id = $validatedData['instructor_id'];
            $begin = new Carbon($validatedData['start_date']);
            $end = new Carbon($validatedData['end_date']);

            $slots = Slots::whereHas('lesson', function ($q) use ($instructor_id) {
                $q->where('created_by', $instructor_id);
            })->whereBetween('date_time', [$begin, $end->addDay(1)])->get();
            $slots->load('lesson');
            $slots->load('student');
            return response()->json(['slots' => $slots, 'total' => sizeof($slots)]);
        } catch (\Exception $e) {
            return throw new Exception($e->getMessage(), $e->getCode());
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
            ]);

            $slot = Slots::find($request->slot_id);

            if (!!$slot && (Auth::user()->type === Role::ROLE_INSTRUCTOR && $slot->lesson->created_by === Auth::user()->id) || Auth::user()->type === Role::ROLE_ADMIN) {
                $slot->update($validatedData);

                if ($slot?->cancelled == 1)
                    $slot->update(['is_active' => 0]);

                if ($request->unbook === "1" && isset($slot->student_id)) {
                    $student = $slot->student;
                    $slot->student_id = null;
                    $slot->save();
                }

                $changes = $slot->getChanges();

                if (isset($changes['date_time']) && !!$slot->get('is_active') && isset($slot->student_id))
                    $this->sendReschuduleSlotNotifications($slot);
                if (!!$slot->get('is_active') && !isset($slot->student_id))
                    $this->sendUnreservedNotifications($slot, $student);
                if (request()->redirect == "1") {
                    return redirect()->back()->with('success', 'Slot Successfully Updated');
                }
                return response()->json($slot, 200);
            } else if (!!$slot && $slot->student_id === Auth::user()->id && Auth::user()->type === Role::ROLE_STUDENT) {
                $slot['student_id'] = null;
                $slot->save();
                if (!!$slot->get('is_active') && !isset($slot->student_id))
                    $this->sendUnreservedNotifications($slot, Auth::user());
                if (request()->redirect == 1) {
                    return redirect()->back()->with('success', 'Slot Successfully Updated');
                }
                return response()->json($slot, 200);
            } else throw new Exception('Slot not found or UnAuthorized' . $request->lesson_id, 404);
        } catch (\Exception $e) {
            return throw new Exception($e->getMessage(), $e->getCode());
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
        $lesson->delete();

        return redirect()->route('lesson.index')->with('success', 'Lesson deleted successfully.');
    }
}
