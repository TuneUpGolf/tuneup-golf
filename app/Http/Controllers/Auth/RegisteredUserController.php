<?php

namespace App\Http\Controllers\Auth;

use App\Actions\SendEmail;
use App\Facades\UtilityFacades;
use App\Http\Controllers\Controller;
use App\Mail\Admin\WelcomeMailStudent;
use App\Models\Follow;
use App\Models\Role;
use App\Models\Student;
use App\Models\User;
use App\Services\ChatService;
use App\Providers\RouteServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Foundation\Auth\RegistersUsers;
use Carbon\Carbon;
use Google\Service\ServiceControl\Auth;

class RegisteredUserController extends Controller
{
    use RegistersUsers;
    protected $redirectTo = RouteServiceProvider::HOME;
    protected $chatService;

    public function __construct(ChatService $chatService)
    {
        $this->chatService = $chatService;
    }

    public function create()
    {
        $lang = UtilityFacades::getActiveLanguage();
        \App::setLocale($lang);
        $roles = Role::whereNotIn('name', ['Super Admin', 'Admin'])->pluck('name', 'name')->all();
        return view('auth.register', compact('roles', 'lang'));
    }

    public function store(Request $request)
    {
        request()->validate([
            'name' => 'required|max:255',
            'email' => 'required|email|max:255|unique:users',
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);
        $user = Student::create([
            'name' => $request->name,
            'email' => $request->email,
            'uuid' => Str::uuid(),
            'password' => Hash::make($request->password),
            'tenant_id' => tenant('id'),
            'type' => Role::ROLE_STUDENT,
            'created_by' => 'signup',
            'email_verified_at' => (UtilityFacades::getsettings('email_verification') == '1') ? null : Carbon::now()->toDateTimeString(),
            'country_code' => $request->country_code,
            'dial_code' => $request->dial_code,
            'phone' => str_replace(' ', '', $request->phone),
            'phone_verified_at' => Carbon::now(),
            'lang' => 'en',
            'active_status' => 1
        ]);
        $user->assignRole(Role::ROLE_STUDENT);
        $chatUserDetails = $this->chatService->getUserProfile($request->email);

        if ($chatUserDetails['code'] == 200) {
            $this->chatService->updateUser($chatUserDetails['data']['_id'], 'tenant_id', tenant('id'), $request->eamil);
            $user->update([
                'chat_user_id' => $chatUserDetails['data']['_id'],
            ]);
        } elseif ($chatUserDetails['code'] == 204) {
            $created = $this->chatService->createUser($user);
            if (! $created) {
                throw new \Exception('Failed to chat user.');
            }
        } else {
            throw new \Exception('Failed to chat user.');
        }

        $instructor = User::where('type', Role::ROLE_INSTRUCTOR)->orderBy('id', 'desc')->first();
        if ($instructorId = $instructor->id ?? false) {
            Follow::updateOrCreate(
                ['student_id' => $user->id, 'instructor_id' => $instructorId],
                ['active_status' => true, 'isPaid' => false]
            );
        }

        if(!is_null($instructor->chat_user_id))
        {
            $groupId = $this->chatService->createGroup($user->chat_user_id, $instructor->chat_user_id);
            if ($groupId) {
                $user->group_id = $groupId;
                $user->save();
            }
        }

        SendEmail::dispatch($user->email, new WelcomeMailStudent($user, ''));

        // else {
        //     $user = User::create([
        //         'name' => $request->name,
        //         'email' => $request->email,
        //         'uuid' => Str::uuid(),
        //         'password' => Hash::make($request->password),
        //         'tenant_id' => tenant('id'),
        //         'type' => Role::ROLE_INSTRUCTOR,
        //         'created_by' => 'signup',
        //         'email_verified_at' => (UtilityFacades::getsettings('email_verification') == '1') ? null : Carbon::now()->toDateTimeString(),
        //         'country_code' => $request->country_code,
        //         'dial_code' => $request->dial_code,
        //         'phone' => str_replace(' ', '', $request->phone),
        //         'phone_verified_at' => Carbon::now(),
        //         'lang' => 'en',
        //         'active_status' => 0
        //     ]);
        //     $user->assignRole(Role::ROLE_INSTRUCTOR);
        //     return redirect(RouteServiceProvider::LOGIN)->with('success', 'Signup successful, please contact admin to activate your account.');
        // }

        $res = Auth::loginUsingId($user->id);

        if($res)
        {
            return redirect()->route('home');
        }
        
        return redirect(RouteServiceProvider::LOGIN)->with('success', 'Signup successful, please login with your credentials');
    }
}