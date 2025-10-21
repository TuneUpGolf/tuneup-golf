<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\DataTables\Admin\PostDataTable;
use App\DataTables\Admin\ReportedPostDataTable;
use App\Facades\UtilityFacades;
use App\Http\Resources\PostAPIResource;
use App\Models\Category;
use App\Models\LikePost;
use App\Models\Post;
use App\Models\ReportPost;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Str;
use Illuminate\Validation\UnauthorizedException;

class PostsController extends Controller
{
    public function index(PostDataTable $dataTable)
    {

        if (Auth::user()->can('manage-blog')) {
            $posts = Post::where('instructor_id', Auth::user()->id)->where('status', 'active');
            switch (request()->query('filter')) {
                case ('free'):
                    $posts = $posts->where('paid', false);
                    break;
                case ('paid'):
                    $posts = $posts->where('paid', true);
                    break;
                case ('student'):
                    $posts = $posts->where('isStudentPost', true);
                    break;
                case ('instructor'):
                    $posts = $posts->where('isStudentPost', false);
            }
            $posts = $posts->orderBy('created_at', 'desc')->paginate(6);
            $posts->load('instructor');
            $posts->load('student');
            return view('admin.posts.infinite', compact('posts'));
        } else {
            return redirect()->back()->with('failed', __('Permission denied.'));
        }
    }

    public function managePosts(PostDataTable $dataTable)
    {
        if (Auth::user()->can('manage-blog')) {
            return $dataTable->render('admin.posts.index');
        }
    }

    public function manageReportedPosts(ReportedPostDataTable $dataTable)
    {
        if (Auth::user()->can('manage-blog')) {
            return $dataTable->render('admin.posts.index');
        }
    }

    public function create()
    {
        $settingData    = UtilityFacades::getsettings('plan_setting');
        $plans          = json_decode($settingData, true);

        if (Auth::user()->can('create-blog')) {
            $category   = Category::where('status', 1)->pluck('name', 'id');
            return  view('admin.posts.create', compact('category'));
        } else {
            return redirect()->back()->with('failed', __('Permission denied.'));
        }
    }

    public function store(Request $request)
    {
        if (Auth::user()->can('create-blog')) {
            try {
                request()->validate([
                    'title' => 'required|string',
                    'description' => 'required|string',
                ]);

                if (Auth::user()->type === Role::ROLE_STUDENT) {
                    $request->merge(['student_id' => Auth::user()->id]);
                    $request->merge(['isStudentPost' => true]);
                } else {
                    $request->merge(['instructor_id' => Auth::user()->id]);
                    $request->merge(['isStudentPost' => false]);
                }

                $post = Post::create($request->all());
                $post['paid'] = $request?->paid == 'on' ? true : false;
                $post['price'] = $request?->paid == 'on' && !empty($request?->price) ? $request?->price : 0;
                $post['status'] = 'active';
                if ($request->hasfile('file')) {
                    $post['file'] = $request->file('file')->store('posts');
                    $post['file_type'] = Str::contains($request->file('file')->getMimeType(), 'video') ? 'video' : 'image';
                }
                $post->update();
                return redirect()->route('blogs.index')->with('success', __('Post created successfully.'));
            } catch (ValidationException $e) {
                report($e);
                return response()->json(['error' => 'Validation failed.', 'message' => $e->errors()], 422);
            } catch (\Exception $e) {
                report($e);
                return response()->json(['error' => 'Error', 'message' => $e->getMessage()], 500);
            }
        } else {
            return redirect()->back()->with('failed', __('Permission denied.'));
        }
    }

    public function edit($id)
    {
        if (Auth::user()->can('edit-blog')) {
            $posts      = Post::find($id);
            $category   = Category::where('status', 1)->pluck('name', 'id');
            return  view('admin.posts.edit', compact('posts', 'category'));
        } else {
            return redirect()->back()->with('failed', __('Permission denied.'));
        }
    }

    public function update(Request $request, $id)
    {
        if (Auth::user()->can('edit-blog')) {
            request()->validate([
                'title'         => 'required|max:50',
                'description'   => 'required',
                'short_description' => 'required',
            ]);
            $post   = Post::find($id);
            if ($request->hasFile('file')) {
                $path           = $request->file('file')->store('posts');
                $post->file    = $path;
            }
            $post->title                = $request->title;
            $post->paid                 = $request?->paid == 'on' ? true : false;
            $post->price                = $request?->paid == 'on' ? $request?->price : 0;
            $post->description          = $request->description;
            $post->short_description    = $request->short_description;

            $post->save();
            return redirect()->route('blogs.index')->with('success', __('Posts updated successfully'));
        } else {
            return redirect()->back()->with('failed', __('Permission denied.'));
        }
    }

    public function destroy($id)
    {
        if (Auth::user()->can('delete-blog')) {
            $post = Post::find($id);
            $post->delete();
            return redirect()->route('blogs.index')->with('success', __('Posts deleted successfully.'));
        } else {
            return redirect()->back()->with('failed', __('Permission denied.'));
        }
    }

    public function upload(Request $request)
    {
        if ($request->hasFile('upload')) {
            $originName         = $request->file('upload')->getClientOriginalName();
            $fileName           = pathinfo($originName, PATHINFO_FILENAME);
            $extension          = $request->file('upload')->getClientOriginalExtension();
            $fileName           = $fileName . '_' . time() . '.' . $extension;
            $request->file('upload')->move(public_path('images'), $fileName);
            $CKEditorFuncNum    = $request->input('CKEditorFuncNum');
            $url                = asset('images/' . $fileName);
            $msg                = 'Image uploaded successfully';
            $response           = "<script>window.parent.CKEDITOR.tools.callFunction($CKEditorFuncNum, '$url', '$msg')</script>";
            @header('Content-type: text/html; charset=utf-8');
            echo $response;
        }
    }

    public function allPost()
    {
        $categories     = Category::all();
        $category       = [];
        $category['0']  = __('Select category');
        foreach ($categories as $cate) {
            $category[$cate->id] = $cate->name;
        }
        $posts  = Post::all();
        return view('admin.posts.view', compact('posts', 'category'));
    }

    public function viewBlog($slug)
    {
        $lang       = UtilityFacades::getActiveLanguage();
        \App::setLocale($lang);
        $blog       =  Post::where('slug', $slug)->first();
        $allBlogs   =  Post::all();
        return view('admin.posts.view-blog', compact('blog', 'allBlogs', 'lang'));
    }

    public function seeAllBlogs(Request $request)
    {
        $lang           = UtilityFacades::getActiveLanguage();
        \App::setLocale($lang);
        if ($request->category_id != '') {
            $allBlogs   = Post::where('category_id', $request->category_id)->paginate(3);
            return response()->json(['all_blogs' => $allBlogs]);
        } else {
            $allBlogs   = Post::paginate(3);
        }
        $recentBlogs    = Post::latest()->take(3)->get();
        $lastBlog       = Post::latest()->first();
        $categories     = Category::all();
        return view('admin.posts.view-all-blogs', compact('allBlogs', 'recentBlogs', 'lastBlog', 'categories', 'lang'));
    }

    public function likePost()
    {
        try {
            $post = Post::find(request()->post_id);
            if (!!$post) {
                $postLike = Auth::user()->likePost->firstWhere('post_id', $post->id);

                if (!!$postLike) {
                    $postLike->delete();
                    return redirect()->back()->with('success', __('Unliked'));
                }

                $postLike = new LikePost();
                $postLike->post_id = $post->id;
                if (Auth::user()->type === Role::ROLE_STUDENT)
                    $postLike->student_id = Auth::user()->id;
                else
                    $postLike->instructor_id = Auth::user()->id;

                $postLike->save();
                return redirect()->back()->with('success', __('Post Liked Successfully'));
            } else
                return redirect()->back()->with('failed', __('UnSuccessfull'));
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    //APP APIs
    public function updatePostApi($id)
    {
        try {
            $post = Post::find($id);
            $user = Auth::user();
            if (!!$post && $user->post->firstWhere('id', $post->id)) {
                request()->validate([
                    'title' => 'string|max:255',
                    'description' => 'string|max:255',
                    'paid' => 'boolean',
                    'price' => 'string|max:6',
                    'status' => 'in:active,inactive',
                ]);
                if ($post->isStudentPost) {
                    $post->update(request()->only('title', 'description', 'short_description', 'status'));
                } else
                    $post->update(request()->all());
                $post->save();
                return response()->json(new PostAPIResource($post), 200);
            } else
                return response()->json(['error' => 'Post does not exist for logged in user'], 404);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function likePostAPi($id)
    {
        try {
            $post = Post::find($id);

            if (!!$post) {
                $postLike = Auth::user()->likePost->firstWhere('post_id', $post->id);

                if (!!$postLike) {
                    $postLike->delete();
                    return response()->json(['message' => 'unliked successfully'], 200);
                }

                $postLike = new LikePost();
                $postLike->post_id = $post->id;
                if (Auth::user()->type === Role::ROLE_STUDENT)
                    $postLike->student_id = Auth::user()->id;
                else
                    $postLike->instructor_id = Auth::user()->id;

                $postLike->save();
                return response()->json(new PostAPIResource($post), 200);
            } else
                return response()->json(['error' => 'Post does not exist'], 404);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function reportPost(Request $request)
    {

        try {
            $request->validate([
                'post_id' => 'required',
                'comment' => 'max:255',
            ]);
            $post = Post::find($request->get('post_id'));
            if (!!$post) {
                $reportPost = new ReportPost();
                $reportPost->post_id = $post->id;

                if (Auth::user()->type === Role::ROLE_STUDENT)
                    $reportPost->student_id = Auth::user()->id;
                else
                    $reportPost->instructor_id = Auth::user()->id;
                if (isset($request->comment))
                    $reportPost->comment = $request->comment;

                $reportPost->save();

                return response()->json(['message' => 'post reported successfully'], 200);
            } else  return response()->json(['error' => 'Post does not exist'], 404);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function getAllLikedPostApi()
    {
        try {

            $likedPosts = new Collection();
            $posts = Auth::user()->likePost;

            foreach ($posts as $item) {
                $post = $item->post;
                $likedPosts->push($post);
            }

            return PostAPIResource::collection($likedPosts);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function getAllPosts()
    {
        try {
            if (Auth::user()->can('manage-blog')) {
                $posts = Post::where('status', 'active')->orderBy(request()->get('sortKey', 'updated_at'), request()->get('sortOrder', 'desc'));
                switch (request()->filter) {
                    case ('free'):
                        $posts = $posts->where('paid', false);
                        break;
                    case ('paid'):
                        $posts = $posts->where('paid', true);
                        break;
                    case ('student'):
                        $posts = $posts->where('isStudentPost', true);
                        break;
                    case ('instructor'):
                        $posts = $posts->where('isStudentPost', false);
                        break;
                    case ('myPosts'):
                        if (Auth::user()->type === Role::ROLE_INSTRUCTOR) {
                            $posts = $posts->where('instructor_id', Auth::user()->id);
                        } else {
                            $posts = $posts->where('student_id', Auth::user()->id);
                        }
                        break;
                    case ('subscribed'):
                        if (Auth::user()->type === Role::ROLE_STUDENT) {
                            $ids = Auth::user()->follows->where('active_status', true)->where('isPaid', true)->implode('instructor_id', ',');
                            $posts = $posts->whereIn('instructor_id', explode(',', $ids));
                        } else {
                            throw new UnauthorizedException('This filter is only valid for students', 404);
                        }
                        break;
                    case ('followed'):
                        if (Auth::user()->type === Role::ROLE_STUDENT) {
                            $ids = Auth::user()->follows->where('active_status', true)->implode('instructor_id', ',');
                            $posts = $posts->whereIn('instructor_id', explode(',', $ids));
                        } else {
                            throw new UnauthorizedException('This filter is only valid for students', 404);
                        }
                        break;
                }
                return PostAPIResource::collection($posts->paginate(request()->get('perPage', 10)));
            } else {
                return response()->json(['error' => 'Access denied'], 500);
            }
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error fetching posts.', 'message' => $e->getMessage()], 500);
        }
    }

    public function getInstructorPosts(Request $request)
    {
        try {
            $this->validate($request, [
                'instructor_id' => 'required'
            ]);

            if (Auth::user()->can('manage-blog')) {
                $posts = PostAPIResource::collection(Post::with('instructor')
                    ->where('instructor_id', $request?->instructor_id)
                    ->orderBy(request()->get('sortKey', 'updated_at'), request()->get('sortOrder', 'desc'))
                    ->paginate(request()->get('per_page', 10)));
                return $posts;
            }
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error fetching posts.', 'message' => $e->getMessage()], 500);
        }
    }

    public function createPost(Request $request)
    {
        try {
            request()->validate([
                'title' => 'required|string|max:255',
                'description' => 'required|string|max:255',
                'paid' => 'boolean',
                'price' => 'string|max:6',
                'status' => 'in:active,inactive',
            ]);

            if (Auth::user()->type === Role::ROLE_STUDENT) {
                $request->merge(['student_id' => Auth::user()->id]);
                $request->merge(['isStudentPost' => true]);
            } else {
                $request->merge(['instructor_id' => Auth::user()->id]);
                $request->merge(['isStudentPost' => false]);
            }

            $post = Post::create($request->all());

            $post['paid'] = $request?->paid ?? false;
            $post['price'] = $request?->paid ? $request?->price : 0;

            if ($request->hasfile('file')) {
                $post['file'] = $request->file('file')->store('posts');
                $post['file_type'] = Str::contains($request->file('file')->getMimeType(), 'video') ? 'video' : 'image';
            }

            $post->update();

            return response()->json(new PostAPIResource($post), 200);
        } catch (ValidationException $e) {
            return response()->json(['error' => 'Validation failed.', 'message' => $e->errors()], 422);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error creating post.', 'message' => $e->getMessage()], 500);
        }
    }
}
