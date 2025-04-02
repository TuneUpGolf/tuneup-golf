<?php

namespace App\Http\Controllers\Admin;

use App\DataTables\Admin\PurchaseDataTable;
use App\Http\Controllers\Controller;
use App\DataTables\Admin\SalesDataTable;
use App\Facades\UtilityFacades;
use App\Models\DocumentGenrator;
use App\Models\Event;
use App\Models\Lesson;
use App\Models\Order;
use App\Models\Plan;
use App\Models\Posts;
use App\Models\Purchase;
use App\Models\Role;
use App\Models\Student;
use App\Models\SupportTicket;
use App\Models\User;
use App\Providers\AuthServiceProvider;
use Carbon\Carbon;
use Carbon\CarbonInterval;
use DatePeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class HomeController extends Controller
{

    public function landingPage()
    {
        $plans  = tenancy()->central(function ($tenant) {
            return Plan::where('active_status', 1)->get();
        });
        return view('welcome', compact('plans'));
    }

    public function index()
    {
        // $this->middleware(['auth', 'auth:student']);
        if (Auth::user()->type == Role::ROLE_STUDENT) {
            $paymentTypes       = UtilityFacades::getpaymenttypes();
            $user = Auth::user();
            $userType = $user->type;
            $instructor = 0;
            $purchaseComplete = Purchase::where('student_id', Auth::user()->id)->where('status', 'complete')->where('isFeedbackComplete', true)->count();
            $purchaseInprogress = Purchase::where('student_id', Auth::user()->id)->where('status', 'complete')->where('isFeedbackComplete', false)->count();
            $earning = 0;
            $students = 0;
            $role = 0;
            $lessons = 0;
            $planExpiredDate    = 0;
            $inPerson_completed = 0;
            $inPerson_pending = 0;
            $documents          = DocumentGenrator::where('tenant_id', tenant('id'))->count();
            $supports           = tenancy()->central(function ($tenant) {
                return SupportTicket::where('tenant_id', $tenant->id)->latest()->take(7)->get();
            });
            $documentsDatas = DocumentGenrator::where('tenant_id', tenant('id'))->latest()->take(5)->get();
            $posts          = Posts::latest()->take(6)->get();
            $events         = Event::latest()->take(5)->get();

            return view('admin.dashboard.home', compact('user', 'userType', 'instructor', 'purchaseComplete', 'purchaseInprogress', 'inPerson_pending', 'students', 'role', 'lessons', 'planExpiredDate', 'earning', 'paymentTypes', 'documents', 'inPerson_completed', 'supports', 'documentsDatas', 'posts', 'events'));
        }
        if (Auth::user()->type == AuthServiceProvider::ADMIN_TYPE) {
            $planExpiredDate    = tenancy()->central(function ($tenant) {
                $usr            = User::where('email', Auth::user()->email)->first();
                return $usr->plan_expired_date;
            });
        } else {
            $usr                = User::where('email', Auth::user()->email)->first();
            $planExpiredDate    = $usr->plan_expired_date;
        }
        $paymentTypes       = UtilityFacades::getpaymenttypes();
        $earning            = Auth::user()->type === Role::ROLE_INSTRUCTOR ? Purchase::where('instructor_id', Auth::user()->id)->where('status', 'complete')->sum('total_amount') : Purchase::where('status', 'complete')->sum('total_amount');
        $user               = User::where('tenant_id', tenant('id'))->where('type', '!=', 'Admin')->where('created_by', Auth::user()->id)->count();
        $userType = Auth::user()->type;
        $instructor         = User::where('tenant_id', tenant(('id')))->where('type', Role::ROLE_INSTRUCTOR)->count();
        $students           = Student::where('tenant_id', tenant(('id')))->where('active_status', true)->where('isGuest', false)->count();
        $lessons            = Auth::user()->type == "Admin" ? Lesson::where('tenant_id', tenant(('id')))->count() : Lesson::where('tenant_id', tenant(('id')))->where('created_by', Auth::user()->id)->count();
        $purchaseInprogress = Auth::user()->type == "Instructor" ? Purchase::where('instructor_id', Auth::user()->id)->where('status', Purchase::STATUS_COMPLETE)->whereHas('lesson', function ($query) {
            $query->where('type', Lesson::LESSON_TYPE_ONLINE);
        })->where('isFeedbackComplete', false)->count() : Purchase::where('status', Purchase::STATUS_COMPLETE)->whereHas('lesson', function ($query) {
            $query->where('type', Lesson::LESSON_TYPE_ONLINE);
        })->where('isFeedbackComplete', false)->count();
        $purchaseComplete = Auth::user()->type == "Instructor" ? Purchase::where('instructor_id', Auth::user()->id)->where('status', Purchase::STATUS_COMPLETE)->whereHas('lesson', function ($query) {
            $query->where('type', Lesson::LESSON_TYPE_ONLINE);
        })->where('isFeedbackComplete', true)->count() : Purchase::where('status', Purchase::STATUS_COMPLETE)->whereHas('lesson', function ($query) {
            $query->where('type', Lesson::LESSON_TYPE_ONLINE);
        })->where('isFeedbackComplete', true)->count();
        $role               = Role::where('tenant_id')->count();
        $planExpiredDate    = $planExpiredDate;
        $documents          = DocumentGenrator::where('tenant_id', tenant('id'))->count();
        $supports           = tenancy()->central(function ($tenant) {
            return SupportTicket::where('tenant_id', $tenant->id)->latest()->take(7)->get();
        });
        $documentsDatas = DocumentGenrator::where('tenant_id', tenant('id'))->latest()->take(5)->get();
        $posts          = Posts::latest()->take(6)->get();
        $events         = Event::latest()->take(5)->get();
        $inPerson_completed = Auth::user()->type == "Instructor" ? Purchase::where('instructor_id', Auth::user()->id)->where('status', Purchase::STATUS_COMPLETE)->whereHas('lesson', function ($query) {
            $query->where('type', Lesson::LESSON_TYPE_INPERSON);
        })->count() : Purchase::where('status', Purchase::STATUS_COMPLETE)->whereHas('lesson', function ($query) {
            $query->where('type', Lesson::LESSON_TYPE_INPERSON);
        })->count();
        $inPerson_pending = Auth::user()->type == "Instructor" ?
            Purchase::where('instructor_id', Auth::user()->id)->where('status', Purchase::STATUS_INCOMPLETE)->whereHas('lesson', function ($query) {
                $query->where('type', Lesson::LESSON_TYPE_INPERSON);
            })->count() :
            Purchase::where('status', Purchase::STATUS_INCOMPLETE)->whereHas('lesson', function ($query) {
                $query->where('type', Lesson::LESSON_TYPE_INPERSON);
            })->count();

        return view('admin.dashboard.home', compact(
            'user',
            'userType',
            'instructor',
            'purchaseComplete',
            'purchaseInprogress',
            'students',
            'role',
            'lessons',
            'planExpiredDate',
            'earning',
            'paymentTypes',
            'documents',
            'supports',
            'documentsDatas',
            'posts',
            'events',
            'inPerson_completed',
            'inPerson_pending'
        ));
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
