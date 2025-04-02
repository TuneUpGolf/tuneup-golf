<?php

namespace App\Http\Controllers\Admin;

use App\Actions\SendEmail;
use App\Actions\SendSMS;
use App\DataTables\Admin\InstructorDataTable;
use App\Facades\UtilityFacades;
use App\Http\Controllers\Controller;
use App\Http\Resources\AnnotationVideoApiResource;
use App\Http\Resources\InstructorAPIResource;
use App\Models\Role;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Stancl\Tenancy\Database\Models\Domain;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\InstructorsImport;
use App\Mail\Admin\WelcomeMail;
use App\Models\AnnotationVideos;
use App\Models\Follow;
use App\Models\Lesson;
use App\Models\Post;
use App\Models\Purchase;
use App\Models\PurchaseVideos;
use App\Models\ReportUser;
use App\Models\Review;
use App\Models\Student;
use App\Traits\ConvertVideos;
use Illuminate\Support\Str;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Laravel\Horizon\Listeners\SendNotification;

use function PHPUnit\Framework\isEmpty;
use Illuminate\Support\Facades\Storage;

class InstructorController extends Controller
{
    use ConvertVideos;

    public function __construct()
    {
        $path               = storage_path() . "/json/country.json";
        $this->countries    = json_decode(file_get_contents($path), true);
    }

    public function index(InstructorDataTable $dataTable)
    {
        if (Auth::user()->can('manage-instructors')) {
            return $dataTable->render('admin.instructors.index');
        } else {
            return redirect()->back()->with('failed', __('Permission denied.'));
        }
    }

    public function create()
    {

        if (Auth::user()->can('create-instructors')) {
            return view('admin.instructors.create');
        }
        return redirect()->back()->with('failed', __('Permission denied.'));
    }
    public function import()
    {

        if (Auth::user()->can('create-instructors')) {
            return view('admin.instructors.import');
        }
        return redirect()->back()->with('failed', __('Permission denied.'));
    }

    public function instructorProfile()
    {
        $instructors = User::where('type', Role::ROLE_INSTRUCTOR)->get();
        return view('admin.instructors.profiles', compact('instructors'));
    }

    public function viewProfile(Request $request)
    {
        $instructor  = User::where('type', Role::ROLE_INSTRUCTOR)->where('id', request()->query('instructor_id'))->first();
        $posts = Post::where('instructor_id', $instructor->id);
        $totalLessons = Lesson::where('created_by', request()->query('instructor_id'))->count();
        $totalPosts = $posts->count();
        $posts = $posts->orderBy('created_at', 'desc')->paginate(6);
        $followers = Follow::where('instructor_id', $instructor->id)->count();
        $section = $request->section;
        $follow = Follow::where('instructor_id', $instructor->id);
        $subscribers = Follow::where('instructor_id', $instructor->id)->where('active_status', 1)->where('isPaid', true)->count();
        return view('admin.instructors.profile', compact('instructor', 'totalPosts', 'totalLessons', 'followers', 'subscribers', 'section', 'posts', 'follow'));
    }

    public function store(Request $request)
    {
        if (Auth::user()->can('create-instructors')) {
            if (Auth::user()->type == 'Admin') {
                request()->validate([
                    'name'          => 'required|max:50',
                    'email'         => 'required|email|unique:users,email,',
                    'password'      => 'same:confirm-password',
                    'country_code'  => 'required',
                    'dial_code'     => 'required',
                    'phone'         => 'required',
                ]);
                $userData                      = $request->all();
                $userData['uuid']              = Str::uuid();
                $userData['password']          = Hash::make($userData['password']);
                $userData['type']              = 'Instructor';
                $userData['created_by']        = Auth::user()->id;
                $userData['email_verified_at'] = (UtilityFacades::getsettings('email_verification') == '1') ? null : Carbon::now()->toDateTimeString();
                $userData['phone_verified_at'] = (UtilityFacades::getsettings('phone_verification') == '1') ? null : Carbon::now()->toDateTimeString();
                $userData['country_code']      = $request->country_code;
                $userData['dial_code']         = $request->dial_code;
                $userData['phone']             = str_replace(' ', '', $request->phone);
                $user                          = User::create($userData);
                $user->assignRole('Instructor');
                if ($request->hasFile('file')) {
                    $user['logo'] = $request->file('file')->store('dp');
                }
                $user->update();
                SendEmail::dispatch($userData['email'], new WelcomeMail($userData['name']));
                $message = __('Welcome, :name, you have successfully signed up!, Please login at :link', [
                    'name' => $userData['name'],
                    'link' => route('login'),
                ]);
                $userPhone = Str::of($userData['dial_code'])->append($userData['phone'])->value();
                $userPhone = str_replace(array('(', ')'), '', $userPhone);
                SendSMS::dispatch("+" . $userPhone, $message);
            }
            return redirect()->route('instructor.index')->with('success', __('Instructor created successfully.'));
        } else {
            return redirect()->back()->with('failed', __('Permission denied.'));
        }
    }

    public function edit($id)
    {
        if (Auth::user()->can('edit-user')) {
            $user   = User::find($id);
            if (Auth::user()->type == 'Admin') {
                $roles      = Role::where('name', '!=', 'Super Admin')->where('name', '!=', 'Admin')->pluck('name', 'name');
                $domains    = Domain::pluck('domain', 'domain')->all();
            } else {
                $roles      = Role::where('name', '!=', 'Admin')->where('name', Auth::user()->type)->pluck('name', 'name');
                $domains    = Domain::pluck('domain', 'domain')->all();
            }
            $countries = $this->countries;
            return view('admin.instructors.edit', compact('user', 'roles', 'domains', 'countries'));
        } else {
            return redirect()->back()->with('failed', __('Permission denied.'));
        }
    }

    public function reportInstructor()
    {
        try {
            request()->validate([
                'instructor_id' => 'required',
                'commnet' => 'max:255',
            ]);

            $instructor = User::findOrFail(request()->get('instructor_id'));

            if (!!$instructor) {

                $reportUser = new ReportUser();
                $reportUser->instructor_id = $instructor->id;

                if (Auth::user()->type === Role::ROLE_STUDENT)
                    $reportUser->student_id = Auth::user()->id;
                else
                    throw new Exception('UnAuthorized', 401);
                if (isset(request()->comment))
                    $reportUser->comment = request()->comment;

                $reportUser->save();

                return response('Instructor Successfully Reported', 200);
            }
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error', 'message' => $e->getMessage()], 500);
        }
    }

    public function addReview()
    {
        try {
            request()->validate([
                'instructor_id' => 'required',
                'review' => 'max:255',
                'rating' => 'required|gte:1|lte:5',
            ]);

            $instructor = User::findOrFail(request()->get('instructor_id'));

            if (!!$instructor && Auth::user()->type === Role::ROLE_STUDENT) {

                $review = Review::firstOrCreate(['student_id' => Auth::user()->id, 'instructor_id' => $instructor->id]);

                if (isset(request()->review))
                    $review->review = request()->review;
                $review->rating = request()->rating;

                $review->save();

                $instructor->avg_rate = DB::table('reviews')
                    ->where('instructor_id', request()->get('instructor_id'))
                    ->groupBy('instructor_id')
                    ->avg('rating');

                $instructor->save();

                return response(['message' => 'Success', 'review' => $review], 200);
            }
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error', 'message' => $e->getMessage()], 500);
        }
    }

    public function getReviews(Request $request)
    {
        try {
            $request->validate([
                'instructor_id' => 'required'
            ]);
            $instructor = User::findOrFail(request()->get('instructor_id'));

            if (!!$instructor) {
                $reviews = Review::where('instructor_id', $request->get('instructor_id'))->orderBy(request()->get('sortKey', 'updated_at'), request()->get('sortOrder', 'desc'))->paginate(request()->get('perPage'));
                return response()->json([
                    'reviews' => $reviews
                ]);
            } else
                throw new Exception('instructor not found', 404);
        } catch (\Exception $e) {
            return throw new Exception($e->getMessage());
        }
    }

    public function updateInstructorBio()
    {
        request()->validate([
            'instructor_id' => 'required',
            'bio' => 'required|max:250',
        ]);
        try {
            if (Auth::user()->type = Role::ROLE_INSTRUCTOR) {
                $instructor = User::where('type', Role::ROLE_INSTRUCTOR)->find(request()->instructor_id);
                if ($instructor->active_status == true) {
                    $instructor['bio'] = request()?->bio;
                    $instructor->update();
                    return response(new InstructorAPIResource($instructor));
                } else {
                    return response()->json(['error' => 'Instructor is currently disabled, please contact administror', 419]);
                }
            } else {
                return response()->json(['error' => 'Unauthorized', 401]);
            }
        } catch (\Exception $e) {
            return throw new Exception($e->getMessage());
        }
    }

    public function annotate(Request $request)
    {
        try {
            $request->validate([
                'video' => 'required|mimetypes:video/avi,video/mpeg,video/quicktime,video/mov,video/mp4',
            ]);
            if (Auth::user()->type == Role::ROLE_INSTRUCTOR) {
                if($request->hasFile('video')) {
                    $file = $request->file('video');
                    if (Str::endsWith($file->getClientOriginalName(), '.mov')) {
                        $localPath = $request->file('video')->store('AnnotationVideos');
                        $path = $this->convertSingleVideo($localPath);
                    } else {
                        $extension = $file->getClientOriginalExtension();
                        $randomFileName = Str::random(25) . '.' . $extension;
                        $filePath = Auth::user()->tenant_id.'/AnnotationVideos/'.$randomFileName;
                        Storage::disk('spaces')->put($filePath, file_get_contents($file), 'public');
                        $path = Storage::disk('spaces')->url($filePath);
                    }
                } else {
                    $path = '/error';
                }
                $annotationVideo =  AnnotationVideos::create(
                    [
                        'uuid' => Str::uuid(),
                        'instructor_id' => Auth::user()->id,
                        'video_url' => $path,
                    ]
                );
            } else
                throw new Exception('UnAuthorized', 401);
            return response()->json(new AnnotationVideoApiResource($annotationVideo));
        } catch (\Exception $e) {
            return throw new Exception($e->getMessage());
        }
    }

    public function setProfilePicture(Request $request)
    {
        try {
            $request->validate([
                'dp' => 'required',
            ]);
            if ($request->hasFile('dp') && Auth::user()->type === Role::ROLE_INSTRUCTOR) {
                $instructor = User::find(Auth::user()?->id);
                $instructor['logo'] = $request->file('dp')->store('dp/');
                $instructor->update();
                return response()->json([
                    'instructor' => $instructor,
                    'message' => 'Profile Picture has been successfully updated'
                ], 201);
            }
        } catch (ValidationException $e) {
            return response()->json(['error' => 'Validation failed.', 'message' => $e->errors()], 422);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error', 'message' => $e->getMessage()], 500);
        }
    }



    public function update(Request $request, $id)
    {
        if (Auth::user()->can('edit-user')) {
            request()->validate([
                'name'          => 'required|max:50',
                'country_code'  => 'required',
                'dial_code'     => 'required',
                'phone'         => 'required',
                'password'      => 'same:password_confirmation',
                'country'       => 'required',
            ]);
            $input          = $request->all();
            $user           = User::find($id);
            $user->country_code = $request->country_code;
            $user->dial_code    = $request->dial_code;
            $user->phone        = str_replace(' ', '', $request->phone);
            $currentdate        = Carbon::now();
            $newEndingDate      = date("Y-m-d", strtotime(date("Y-m-d", strtotime($user->created_at)) . " + 1 year"));
            if ($currentdate <= $newEndingDate) {
            }
            $user->update($input);
            if (!empty($request->password)) {
                $user->password = bcrypt($request->password);
                $user->save();
            }

            return redirect()->route('instructor.index')->with('success', __('User updated successfully.'));
        } else {
            return redirect()->back()->with('failed', __('Permission denied.'));
        }
    }

    public function destroy($id)
    {
        if (Auth::user()->can('delete-user')) {
            $user = User::find($id);
            $user->purchase()->delete();
            $user->lessons()->delete();
            $user->post()->delete();
            $user->delete();

            return redirect()->route('instructor.index')->with('success', __('User deleted successfully.'));
        } else {
            return redirect()->back()->with('failed', __('Permission denied.'));
        }
    }

    public function userEmailVerified($id)
    {
        $user = User::find($id);
        if ($user->email_verified_at) {
            $user->email_verified_at = null;
            $user->save();
            return redirect()->back()->with('success', __('User email unverified successfully.'));
        } else {
            $user->email_verified_at = Carbon::now();
            $user->save();
            return redirect()->back()->with('success', __('User email verified successfully.'));
        }
    }

    public function userPhoneVerified($id)
    {
        $user = User::find($id);
        if ($user->phone_verified_at) {
            $user->phone_verified_at = null;
            $user->save();
            return redirect()->back()->with('success', __('User phone unverified successfully.'));
        } else {
            $user->phone_verified_at = Carbon::now();
            $user->save();
            return redirect()->back()->with('success', __('User phone verified successfully.'));
        }
    }

    public function getStats(Request $request)
    {
        // Total Students, Total Lessons Pending, Total Lessons Completed, Lesson Revenue.
        $request->validate([
            'instructor_id' => 'required',
        ]);
        $instructor = User::where('type', Role::ROLE_INSTRUCTOR)->where('id', $request->instructor_id)->first();
        if ($instructor) {
            try {
                return response()->json([
                    'students' => Student::where('active_status', 1)->where('isGuest', false)->count(),
                    'lessons_pending' => Purchase::where('instructor_id', $request->instructor_id)->where('status', Purchase::STATUS_COMPLETE)->whereHas('lesson', function ($query) {
                        $query->where('type', Lesson::LESSON_TYPE_ONLINE);
                    })->where('isFeedbackComplete', false)->count(),
                    'lessons_completed' => Purchase::where('instructor_id', $request->instructor_id)->where('status', Purchase::STATUS_COMPLETE)->whereHas('lesson', function ($query) {
                        $query->where('type', Lesson::LESSON_TYPE_ONLINE);
                    })->where('isFeedbackComplete', true)->count(),
                    'lessons_revenue' => Purchase::where('instructor_id', $request->instructor_id)->where('status', Purchase::STATUS_COMPLETE)->sum('total_amount'),
                    'inPerson_completed' => Purchase::where('instructor_id', $request->instructor_id)->where('status', Purchase::STATUS_COMPLETE)->whereHas('lesson', function ($query) {
                        $query->where('type', Lesson::LESSON_TYPE_INPERSON);
                    })->count(),
                    'inPerson_pending' => Purchase::where('instructor_id', $request->instructor_id)->where('status', Purchase::STATUS_INCOMPLETE)->whereHas('lesson', function ($query) {
                        $query->where('type', Lesson::LESSON_TYPE_INPERSON);
                    })->count(),
                ]);
            } catch (\Exception $e) {
                return throw new Exception($e->getMessage());
            }
        }
        return response()->json('Instructor not found', 419);
    }

    public function userStatus(Request $request, $id)
    {
        $user   = User::find($id);
        $input  = ($request->value == "true") ? 1 : 0;
        if ($user) {
            $user->active_status = $input;
            $user->save();
        }
        return response()->json([
            'is_success'    => true,
            'message'       => __('User status changed successfully.')
        ]);
    }
    public function getAllUsers()
    {
        try {
            if (Auth::user()->active_status == true) {
                $instructors = User::where('type', Role::ROLE_INSTRUCTOR)->where('active_status', 1)->orderBy(request()->get('sortKey', 'created_at'), request()->get('sortOrder', 'desc'));
                return InstructorAPIResource::collection($instructors->paginate(request()->get('perPage')));
            } else {
                return response()->json(['error' => 'Unauthorized', 401]);
            }
        } catch (\Exception $e) {
            return throw new Exception($e->getMessage());
        }
    }

    public function deleteAPI($id)
    {
        try {

            if (Auth::user()->id == $id && Auth::user()->type === Role::ROLE_INSTRUCTOR) {
                $user = User::find($id);
                if ($user->id != 1) {
                    $user->purchase()->delete();
                    $user->post()->delete();
                    $user->lessons()->delete();
                    $user->delete();
                }
                return response()->json(['message' => 'Instructor successfully deleted '], 200);
            } else {
                response()->json(['message' => 'unsuccessful'], 419);
            }
        } catch (\Exception $e) {
            return throw new Exception($e->getMessage());
        }
    }

    public function importfun(Request $request)
    {
        if (Auth::user()->can('create-instructors')) {
            if (Auth::user()->type == 'Admin') {
                Excel::import(new InstructorsImport, $request->file('file'));

                $imported = Excel::toArray(new InstructorsImport(), $request->file('file'));
                foreach ($imported[0] as $import) {
                    SendEmail::dispatch($import['email'], new WelcomeMail($import['name']));
                }

                return redirect()->route('instructor.index')->with('success', __('Instructors imported successfully.'));
            }
        } else {
            return redirect()->back()->with('failed', __('Permission denied.'));
        }
    }
}
