<?php

namespace App\Http\Controllers;

use App\DataTables\Admin\AlbumCategoryDataTable;
use App\Models\AlbumCategory;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class AlbumCategoryController extends Controller
{
    public function index(AlbumCategoryDataTable $dataTable)
    {
        if (Auth::user()->can('manage-blog')) {
            return $dataTable->render('admin.album.category.index');
        }
    }
    public function create()
    {
        if (Auth::user()->can('create-blog')) {
            return  view('admin.album.category.create');
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
                $album_category = new AlbumCategory();
                $album_category->instructor_id = Auth::user()->id;
                $album_category->tenant_id = tenant('id');
                $album_category->title = $request->title;               
                $album_category->slug = Str::slug($request->title);
                $album_category->description = $request->description;
                $album_category->payment_mode = array_key_exists('paid', $request->all()) ? ($request?->paid == 'on' ? "paid" : "un-paid") : "un-paid";
                $album_category->price =  array_key_exists('paid', $request->all()) ? ($request?->paid == 'on' && !empty($request?->price) ? $request?->price : 0) : 0;
                
                if ($request->hasfile('file')) {
                    $file = $request->file('file')->store('album_category');
                    $album_category->image = $file ?? null;
                }
                $album_category->status = 'active';
                $album_category->save();
                return redirect()->route('album.category.manage')->with('success', __('Album Category created successfully.'));
            } catch (ValidationException $e) {
                Log::info($e->getMessage());
                return redirect()->back()->withErrors($e->errors())->withInput();
            } catch (\Exception $e) {
                Log::info($e->getMessage());
                return redirect()->back()->with('danger',$e->getMessage())->withInput();
            }
        } else {
            return redirect()->back()->with('failed', __('Permission denied.'));
        }
    }

    public function destroy($id)
    {
        if (Auth::user()->can('delete-blog')) {
            $post = AlbumCategory::find($id);
            $post->delete();
            return redirect()->route('album.category.manage')->with('success', __('Album Category deleted successfully.'));
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
            ]);
            $album_category   = AlbumCategory::find($id);
            if ($request->hasFile('file')) {
                $path           = $request->file('file')->store('album_category');
                $album_category->image    = $path;
            }
            $album_category->instructor_id = Auth::user()->id;
            $album_category->tenant_id = tenant('id');
            $album_category->title = $request->title;               
            $album_category->slug = Str::slug($request->title);
            $album_category->description = $request->description;
            $album_category->payment_mode = array_key_exists('paid',$request->all()) ? ($request?->paid == 'on' ? 'paid' : 'un-paid') : 'un-paid';
            $album_category->price = array_key_exists('paid',$request->all()) && $request?->paid == 'on' && !empty($request?->price) ? $request?->price : 0;
            $album_category->save();
            return redirect()->route('album.category.manage')->with('success', __('Album Category updated successfully'));
        } else {
            return redirect()->back()->with('failed', __('Permission denied.'));
        }
    }

    public function edit($id)
    {
        if (Auth::user()->can('edit-blog')) {
            $posts      = AlbumCategory::find($id);
            if(!is_null($posts)) {
                return  view('admin.album.category.edit', compact('posts'));
            }else {
                return redirect()->back()->with('failed', __('Album Category not found.'));
            }
        } else {
            return redirect()->back()->with('failed', __('Permission denied.'));
        }
    }

    public function getCategories()
    {
        $album_categories = AlbumCategory::where([
            ['tenant_id', tenant()->id],
            ['status', 'active'],
        ]);
        if (Auth::user()->can('manage-blog')) {
            switch (request()->query('filter')) {
                case ('free'):
                    $album_categories = $album_categories->where('payment_mode', 'un-paid');
                    break;
                case ('paid'):
                    $album_categories = $album_categories->where('payment_mode', 'paid');
                    break;
            }
            $album_categories = $album_categories->orderBy('created_at', 'desc')->paginate(6);
            return view('admin.posts.student_album_category', compact('album_categories'));
        } else {
            return redirect()->back()->with('failed', __('Permission denied.'));
        }
    }
}