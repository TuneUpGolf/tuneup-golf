<?php

namespace App\Http\Controllers\Admin;

use DatePeriod;
use Carbon\Carbon;
use Stripe\Stripe;
use App\Models\Plan;
use App\Models\Role;
use App\Models\User;
use App\Models\Event;
use App\Models\Posts;
use App\Models\Slots;
use App\Models\Lesson;
use App\Models\Student;
use App\Facades\Utility;
use App\Models\Purchase;
use Carbon\CarbonInterval;
use Illuminate\Http\Request;
use Stripe\Checkout\Session;
use App\Models\AlbumCategory;
use App\Models\SupportTicket;
use App\Services\ChatService;
use App\Facades\UtilityFacades;
use App\Models\DocumentGenrator;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Providers\AuthServiceProvider;
use App\DataTables\Admin\SalesDataTable;
use App\DataTables\Admin\PurchaseDataTable;
use App\DataTables\Admin\StudentPurchaseDataTable;

class HomeController extends Controller
{
    protected $chatService;
    protected $utility;
    public function __construct(ChatService $chatService, Utility $utility)
    {
        $this->chatService = $chatService;
        $this->utility = $utility;
    }

    public function landingPage()
    {
        $plans  = tenancy()->central(function ($tenant) {
            return Plan::where('active_status', 1)->get();
        });
        return view('welcome', compact('plans'));
    }
    public function index(Request $request)
    {

        $user = Auth::user();
        $userType = $user->type;
        $tenantId = tenant('id');
        $tab = $request->get('view');

        if ($userType == Role::ROLE_STUDENT) {

            $user = Student::find($user->id);
            if ($purchase = Purchase::find($request->query('purchase_id'))) {
                Stripe::setApiKey(config('services.stripe.secret'));
                $session = Session::retrieve($purchase->session_id);
                if ($session->payment_status == "paid") {
                    $purchase->status = Purchase::STATUS_COMPLETE;
                    $purchase->save();
                }
            }

            $token = false;

            $instructorId = $user->plan->instructor_id ?? $user->chat_enabled_by;

            $instructor = User::find($instructorId);
            if ($tab == 'chat' && $instructor) {
                $students = $this->utility->ensureChatUserId($user, $this->chatService);
                $instructor = $this->utility->ensureChatUserId($instructor, $this->chatService);
                $this->utility->ensureGroup($students, $instructor, $this->chatService);
                $token = $this->chatService->getChatToken($user->chat_user_id);
            }

            $chatEnabled = $this->utility->chatEnabled($user);
            $plans = Plan::with('instructor')->whereHas('instructor')->get();

            $tab = !empty($tab) ? $tab : 'in-person';

            $dataTable = $tab == 'my-lessons' ? new StudentPurchaseDataTable($tab) : false;

            $instructor_id = $request->input('instructor_id', null);
            // $tenant_instructors = User::with('lessons')
            //     ->instructors()
            //     ->get()
            //     ->filter(fn($instructor) => $instructor->lessons->isNotEmpty());
            $inPerson_instructors = User::with('lessons')
                ->instructors()
                ->whereHas('lessons', function ($q) {
                    $q->where('type', 'inPerson')
                        ->orWhere('type', 'package');
                })
                ->get();

            $online_instructors = User::with('lessons')
                ->instructors()
                ->whereHas('lessons', function ($q) {
                    $q->where('type', 'online');
                })
                ->get();



            $album_instructors = User::instructors()

                ->get();



            $album_categories = AlbumCategory::query();

            if ($request->has('instructor_id') && !empty($request->instructor_id)) {
                $album_categories->where('instructor_id', $request->instructor_id);
            }

            $album_categories = $album_categories->get();




            // dd($online_instructors, $inPerson_instructors[0]->lessons);
            return $dataTable ? $dataTable->render('admin.dashboard.tab-view', compact('tab', 'dataTable')) :
                view('admin.dashboard.tab-view', compact('tab', 'dataTable', 'chatEnabled', 'token', 'instructor', 'plans', 'inPerson_instructors', 'online_instructors', 'instructor_id', 'album_instructors', 'album_categories'));
        }

        $user = User::find($user->id);

        // Common Queries
        $paymentTypes = UtilityFacades::getpaymenttypes();
        $documents = DocumentGenrator::where('tenant_id', $tenantId)->count();
        $documentsDatas = DocumentGenrator::where('tenant_id', $tenantId)->latest()->take(5)->get();
        $posts = Posts::latest()->take(6)->get();
        $events = Event::latest()->take(5)->get();
        $supports = tenancy()->central(fn($tenant) => SupportTicket::where('tenant_id', $tenant->id)->latest()->take(7)->get());

        // Fetch Plan Expiration
        $planExpiredDate = $userType == AuthServiceProvider::ADMIN_TYPE
            ? tenancy()->central(fn($tenant) => User::where('email', $user->email)->first()?->plan_expired_date)
            : User::where('email', $user->email)->first()?->plan_expired_date;

        // Fetch Instructor Count
        $instructor = User::where('tenant_id', $tenantId)->where('type', Role::ROLE_INSTRUCTOR)->count();
        $students = Student::where('tenant_id', $tenantId)->where('active_status', true)->where('isGuest', false)->count();

        // Fetch Lessons Count
        $lessons = ($userType == "Admin")
            ? Lesson::where('tenant_id', $tenantId)->count()
            : Lesson::where('tenant_id', $tenantId)->where('created_by', $user->id)->count();

        // Fetch Earnings
        $earning = ($userType === Role::ROLE_INSTRUCTOR)
            ? Purchase::where('instructor_id', $user->id)->where('status', 'complete')->sum('total_amount')
            : Purchase::where('status', 'complete')->sum('total_amount');

        // Fetch Instructor Statistics for Admins (Without Student Count)
        $instructorStats = [];

        $instructorStats = User::where('tenant_id', $tenantId);
        if ($userType == "Admin") {
            $instructorStats = $instructorStats->where('type', Role::ROLE_INSTRUCTOR);
        } elseif ($userType == "Instructor") {
            $instructorStats = $instructorStats->where('id', $user->id);
        }
        $instructorStats = $instructorStats->withCount([
            'lessons as lesson_count',
            'purchase as completed_online_lessons' => fn($query) => $query->where('status', Purchase::STATUS_COMPLETE)->where('isFeedbackComplete', true)->whereHas('lesson', fn($q) => $q->where('type', Lesson::LESSON_TYPE_ONLINE)),
            'purchase as completed_inperson_lessons' => fn($query) => $query->where('status', Purchase::STATUS_COMPLETE)->where('isFeedbackComplete', true)->whereHas('lesson', fn($q) => $q->where('type', Lesson::LESSON_TYPE_INPERSON)),
            'purchase as pending_online_lessons' => fn($query) => $query->where('status', Purchase::STATUS_COMPLETE)->where('isFeedbackComplete', false)->whereHas('lesson', fn($q) => $q->where('type', Lesson::LESSON_TYPE_ONLINE)),
            'purchase as pending_inperson_lessons' => fn($query) => $query->where('isFeedbackComplete', false)->whereHas('lesson', fn($q) => $q->where('type', Lesson::LESSON_TYPE_INPERSON)),
        ])->get();


        [$purchaseComplete, $purchaseInprogress] = $this->fetchPurchaseStats($user, Lesson::LESSON_TYPE_ONLINE);
        [$inPersonCompleted, $inPersonPending] = $this->fetchPurchaseStats($user, Lesson::LESSON_TYPE_INPERSON);

        return view('admin.dashboard.home', compact(
            'user',
            'userType',
            'instructor',
            'students',
            'lessons',
            'planExpiredDate',
            'earning',
            'paymentTypes',
            'documents',
            'documentsDatas',
            'posts',
            'events',
            'supports',
            'purchaseComplete',
            'purchaseInprogress',
            'inPersonCompleted',
            'inPersonPending',
            'instructorStats'
        ));
    }

    // Fetch purchase counts based on lesson type
    private function fetchPurchaseStats($user, $lessonType)
    {
        $query = Slots::where('is_active', 1);


        if ($user->type == "Instructor") {
            $query->whereHas('lesson', function ($q) use ($user, $lessonType) {
                $q->where('type', $lessonType)
                    ->where('created_by', $user->id);
            });
        } else {
            $query->whereHas('lesson', function ($q) use ($lessonType) {
                $q->where('type', $lessonType);
            });
        }

        $completed = (clone $query)->where('is_completed', 1)->count();
        $inprogress = $query->where('is_completed', 0)->count();

        return [$completed, $inprogress];
    }

    public function sales(SalesDataTable $dataTable)
    {
        if (Auth::user()->type == 'Super Admin' | Auth::user()->type == 'Admin') {
            return $dataTable->render('admin.sales.index');
        } else {
            return redirect()->back()->with('failed', __('Permission denied.'));
        }
    }

    public function chart(Request $request)
    {
        $arrLable   = [];
        $arrValue   = [];
        $startDate  = Carbon::parse($request->start);
        $endDate    = Carbon::parse($request->end);
        $monthsDiff = $endDate->diffInMonths($startDate);
        if ($monthsDiff >= 0 && $monthsDiff < 3) {
            $endDate    = $endDate->addDay();
            $interval   = CarbonInterval::day();
            $timeType   = "date";
            $dateFormat = "DATE_FORMAT(created_at, '%Y-%m-%d')";
        } elseif ($monthsDiff >= 3 && $monthsDiff < 12) {
            $interval   = CarbonInterval::month();
            $timeType   = "month";
            $dateFormat = "DATE_FORMAT(created_at, '%Y-%m')";
        } else {
            $interval   = CarbonInterval::year();
            $timeType   = "year";
            $dateFormat = "YEAR(created_at)";
        }
        $userReaports = User::select(DB::raw($dateFormat . ' AS ' . $timeType . ',COUNT(id) AS userCount'))
            ->whereDate('created_at', '>=', $startDate)
            ->whereDate('created_at', '<=', $endDate)
            ->groupBy(DB::raw($dateFormat))
            ->get()
            ->toArray();
        $dateRange  = new DatePeriod($startDate, $interval, $endDate);
        switch ($timeType) {
            case 'date':
                $format = 'Y-m-d';
                $labelFormat = 'd M';
                break;
            case 'month':
                $format = 'Y-m';
                $labelFormat = 'M Y';
                break;
            default:
                $format = 'Y';
                $labelFormat = 'Y';
                break;
        }
        foreach ($dateRange as $date) {
            $foundReport = false;
            $Date = Carbon::parse($date->format('Y-m-d'));
            foreach ($userReaports as $orderReaport) {
                if ($orderReaport[$timeType] == $date->format($format)) {
                    $arrLable[] = $Date->format($labelFormat);
                    $arrValue[] = $orderReaport['userCount'];
                    $foundReport = true;
                    break;
                }
            }
            if (!$foundReport) {
                $arrLable[] = $Date->format($labelFormat);
                $arrValue[] = 0.0;
            } else if (!$userReaports) {
                $arrLable[] = $Date->format($labelFormat);
                $arrValue[] = 0.0;
            }
        }
        return response()->json(
            [
                'lable'    => $arrLable,
                'value'     => $arrValue
            ],
            200
        );
    }

    public function readNotification()
    {
        $user   = User::where('tenant_id', tenant('id'))->first();
        $user->notifications->markAsRead();
        return response()->json(['is_success' => true], 200);
    }

    public function changeThemeMode()
    {
        $user   = \Auth::user();
        if ($user->dark_layout == 1) {
            $user->dark_layout = 0;
        } else {
            $user->dark_layout = 1;
        }
        $user->save();
        $data   = [
            'dark_mode' => ($user->dark_layout == 1) ? 'on' : 'off',
        ];
        foreach ($data as $key => $value) {
            UtilityFacades::storesettings([
                'key'   => $key,
                'value' => $value
            ]);
        }
        return response()->json(['mode' => $user->dark_layout]);
    }
}
