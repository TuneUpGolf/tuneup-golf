<?php

namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
use App\Facades\UtilityFacades;
use App\Models\HelpSection;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class HelpSectionController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $role = $user ? $user->roles->pluck('name')->first() : null;
        $help_sections = HelpSection::when($user, function ($query) use ($user) {
            if ($user->hasRole('Instructor')) {
                return $query->where('role', 'instructor');
            } elseif ($user->hasRole('Student')) {
                return $query->where('role', 'student');
            }
            return $query;
        })
            ->paginate(8);
        return view('superadmin.help-section.index', compact('help_sections', 'role'));
    }

    public function create()
    {
        $user = Auth::user();
        $current_role = $user ? $user->roles->pluck('name')->first() : null;
        if ($current_role != 'Admin' && $current_role != 'Super Admin') {
            return redirect()->route('help-section.index')->with('error', "You do not have permission to create help section");
        }
        $databasePermission = UtilityFacades::getsettings('database_permission');
        $roles = array(
            "instructor" => "Instructor",
            "student" => "Student"
        );
        return view('superadmin.help-section.create', compact('databasePermission', 'roles'));
    }

    public function store(Request $request)
    {
        request()->validate([
            'role' =>   'required|string|in:instructor,student',
            'title' => 'required',
            'type' => 'required',
            'uploadFileName' => 'required',
        ]);
        DB::beginTransaction();
        try {
            $help_section = new HelpSection();
            $help_section->role = $request->role;
            $help_section->title = $request->title;
            $help_section->url = $request->uploadFileName;
            $help_section->type = $request->type;
            $help_section->save();
            DB::commit();
            return redirect()->route('help-section.index')->with('success', 'Help Section created successfully.');
        } catch (Exception $ex) {
            DB::rollBack();
            return redirect()->back()->with('failed', $ex->getMessage());
        }
    }

    public function show($id)
    {
        dd('show');
    }

    public function edit($id)
    {
        dd('edit');
    }

    public function update(Request $request, $id)
    {
        dd('update', $id);
    }

    // public function sales(SalesDataTable $dataTable)
    // {
    //     if (Auth::user()->type == 'Super Admin') {
    //         return $dataTable->render('superadmin.sales.index');
    //     } else {
    //         return redirect()->back()->with('failed', __('Permission denied.'));
    //     }
    // }

    // public function chart(Request $request)
    // {
    //     $arrLable   = [];
    //     $arrValue   = [];
    //     $startDate  = Carbon::parse($request->start);
    //     $endDate    = Carbon::parse($request->end);
    //     $monthsDiff = $endDate->diffInMonths($startDate);
    //     if ($monthsDiff >= 0 && $monthsDiff < 3) {
    //         $endDate    = $endDate->addDay();
    //         $interval   = CarbonInterval::day();
    //         $timeType   = "date";
    //         $dateFormat = "DATE_FORMAT(created_at, '%Y-%m-%d')";
    //     } elseif ($monthsDiff >= 3 && $monthsDiff < 12) {
    //         $interval   = CarbonInterval::month();
    //         $timeType   = "month";
    //         $dateFormat = "DATE_FORMAT(created_at, '%Y-%m')";
    //     } else {
    //         $interval   = CarbonInterval::year();
    //         $timeType   = "year";
    //         $dateFormat = "YEAR(created_at)";
    //     }
    //     $orderReaports  = Order::select(DB::raw($dateFormat . ' AS ' . $timeType . ',SUM(amount) AS totalAmount'))
    //         ->whereDate('created_at', '>=', $startDate)
    //         ->whereDate('created_at', '<=', $endDate)
    //         ->groupBy(DB::raw($dateFormat))
    //         ->get()
    //         ->toArray();
    //     $dateRange      = new DatePeriod($startDate, $interval, $endDate);
    //     switch ($timeType) {
    //         case 'date':
    //             $format         = 'Y-m-d';
    //             $labelFormat    = 'd M';
    //             break;
    //         case 'month':
    //             $format         = 'Y-m';
    //             $labelFormat    = 'M Y';
    //             break;
    //         default:
    //             $format         = 'Y';
    //             $labelFormat    = 'Y';
    //             break;
    //     }
    //     foreach ($dateRange as $date) {
    //         $foundReport    = false;
    //         $Date           = Carbon::parse($date->format('Y-m-d'));
    //         foreach ($orderReaports as $orderReaport) {
    //             if ($orderReaport[$timeType] == $date->format($format)) {
    //                 $arrLable[]     = $Date->format($labelFormat);
    //                 $arrValue[]     = $orderReaport['totalAmount'];
    //                 $foundReport    = true;
    //                 break;
    //             }
    //         }
    //         if (!$foundReport) {
    //             $arrLable[] = $Date->format($labelFormat);
    //             $arrValue[] = 0.0;
    //         } else if (!$orderReaports) {
    //             $arrLable[] = $Date->format($labelFormat);
    //             $arrValue[] = 0.0;
    //         }
    //     }
    //     return response()->json([
    //         'lable' => $arrLable,
    //         'value' => $arrValue
    //     ], 200);
    // }

    // public function readNotification()
    // {
    //     auth()->user()->notifications->markAsRead();
    //     return response()->json(['is_success' => true], 200);
    // }

    // public function changeThemeMode()
    // {
    //     $user = Auth::user();
    //     if ($user->dark_layout == 1) {
    //         $user->dark_layout = 0;
    //     } else {
    //         $user->dark_layout = 1;
    //     }
    //     $user->save();
    //     $data = [
    //         'dark_mode' => ($user->dark_layout == 1) ? 'on' : 'off',
    //     ];
    //     foreach ($data as $key => $value) {
    //         UtilityFacades::storesettings(['key' => $key, 'value' => $value]);
    //     }
    //     return response()->json(['mode' => $user->dark_layout]);
    // }

}