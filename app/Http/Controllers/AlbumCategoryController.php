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
                $album_category->payment_mode = $request?->paid == 'on' ? true : false;
                $album_category->price = $request?->paid == 'on' && !empty($request?->price) ? $request?->price : 0;
                
                if ($request->hasfile('file')) {
                    $file = $request->file('file')->store('album_category');
                    $album_category->image = $file ?? null;
                }
                $album_category->status = 'active';
                $album_category->save();
                return redirect()->back()->with('success', __('Album Category created successfully.'));
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
}