<?php

namespace App\Http\Controllers;

use App\DataTables\Admin\AlbumCategoryDataTable;
use App\Facades\UtilityFacades;
use App\Models\Album;
use App\Models\PurchasePost;
use App\Models\AlbumCategory;
use App\Models\LikeAlbum;
use App\Models\PurchaseAlbum;
use App\Models\Role;
use Error;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Stripe\Stripe;
use Stripe\Checkout\Session;

class AlbumCategoryController extends Controller
{
    public function index(Request $request)
    {
        if (!Auth::user()->can('manage-blog')) {
            return redirect()->back()->with('failed', __('Permission denied.'));
        }
        $categories = match (Auth::user()->type) {
            Role::ROLE_ADMIN      => AlbumCategory::query(),
            Role::ROLE_INSTRUCTOR => AlbumCategory::where('instructor_id', Auth::id()),
            default               => AlbumCategory::where('student_id', Auth::id()),
        };

        $categories = $categories->orderBy('column_order', 'asc')->get();

        if ($request->ajax()) {


            return datatables()
                ->of($categories)
                ->addIndexColumn()
                ->addColumn('column_order', fn($post) => $post->column_order)
                ->addColumn('title', fn($post) => '<a href="' . route('album.category.album', $post->id) . '">' . e($post->title) . '</a>')
                ->addColumn('paid', fn($post) => $post->payment_mode === 'paid' ? 'Yes' : 'No')
                ->addColumn('price', fn($post) => $post->payment_mode === 'paid' ? $post->price : 0)
                ->addColumn('sales', fn($post) => PurchasePost::where('active_status', true)
                    ->where('post_id', $post->id)
                    ->count())
                ->addColumn('photo', function ($post) {
                    if ($post->image) {
                        if ($post->file_type === 'image') {
                            return "<img src='" . asset($post->image) . "' width='50'>";
                        } else {
                            return 'Video';
                        }
                    }
                    return "<img src='" . asset('/storage/' . tenant('id') . '/seeder-image/350x250.png') . "' width='50'>";
                })
                ->addColumn('created_at', fn($post) => UtilityFacades::date_time_format($post->created_at))
                ->addColumn('action', fn($post) => view('admin.album.category.action', compact('post'))->render())
                ->rawColumns(['title', 'photo', 'action'])
                ->make(true);
        }

        return view('admin.album.category.index');
    }

    public function reorder(Request $request)
    {
        try {
            $orderData = $request->input('order');

            if (!is_array($orderData)) {
                throw new \Exception('Invalid order format received.');
            }

            \Log::info('Reorder request received', ['order' => $orderData]);

            foreach ($orderData as $item) {
                if (!isset($item['id'], $item['position'])) {
                    \Log::warning('Invalid item structure', ['item' => $item]);
                    continue;
                }

                AlbumCategory::where('id', $item['id'])
                    ->update(['column_order' => (int) $item['position']]);
            }

            \Log::info('Reorder operation completed successfully.');

            return response()->json(['success' => true]);
        } catch (\Throwable $e) {
            \Log::error('Error during reorder operation', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while reordering.',
            ], 500);
        }
    }



    public function index_old(AlbumCategoryDataTable $dataTable)
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

        // get highest column_order 
        $column_order = AlbumCategory::max('column_order');
        if (Auth::user()->can('create-blog')) {
            try {
                request()->validate([
                    'title' => 'required|string',
                    'description' => 'required|string',
                ]);
                $album_category = new AlbumCategory();
                $album_category->instructor_id = Auth::user()->id;
                $album_category->tenant_id = tenant('id');
                $album_category->column_order =  $column_order + 1 ?? 1;
                $album_category->title = $request->title;
                $album_category->slug = Str::slug($request->title);
                $album_category->description = $request->description;
                $album_category->payment_mode = array_key_exists('paid', $request->all()) ? ($request?->paid == 'on' ? "paid" : "un-paid") : "un-paid";
                $album_category->price =  array_key_exists('paid', $request->all()) ? ($request?->paid == 'on' && !empty($request?->price) ? $request?->price : 0) : 0;
                // $album_category->file_type = Str::contains($request->file('file')->getMimeType(), 'video') ? 'video' : 'image';

                if ($request->hasfile('file')) {
                    // $file = $request->file('file')->store('album_category');
                    // $album_category->image = $file ?? null;
                    $tenantId = tenant()->id; // e.g. 3
                    $destination = public_path("{$tenantId}/album_category");
                    if (!file_exists($destination)) {
                        mkdir($destination, 0777, true);
                    }
                    $filename = time() . '_' . $request->file('file')->getClientOriginalName();
                    $request->file('file')->move($destination, $filename);
                    $album_category->image = "{$tenantId}/album_category/{$filename}";

                    $mimeType = $request->file('file')->getClientOriginalExtension();
                    $video_types = ['mp4', 'avi', 'mov', 'mkv', 'flv', 'wmv', 'webm', 'mpeg', '3gp'];
                    $album_category->file_type = in_array($mimeType, $video_types) ? 'video' : 'image';
                }
                $album_category->status = 'active';
                $album_category->save();
                return redirect()->route('album.category.manage')->with('success', __('Album Category created successfully.'));
            } catch (ValidationException $e) {
                Log::info($e->getMessage());
                return redirect()->back()->withErrors($e->errors())->withInput();
            } catch (\Exception $e) {
                Log::info($e->getMessage());
                return redirect()->back()->with('danger', $e->getMessage())->withInput();
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
            if ($request->hasFile('file') && $request->file('file')->isValid()) {
                // $path           = $request->file('file')->store('album_category');
                // $album_category->image    = $path;
                $tenantId = tenant()->id; // e.g. 3
                $destination = public_path("{$tenantId}/album_category");
                if (!file_exists($destination)) {
                    mkdir($destination, 0777, true);
                }
                $filename = time() . '_' . $request->file('file')->getClientOriginalName();
                $request->file('file')->move($destination, $filename);
                $album_category->image = "{$tenantId}/album_category/{$filename}";
                $mimeType = $request->file('file')->getClientOriginalExtension();
                $video_types = ['mp4', 'avi', 'mov', 'mkv', 'flv', 'wmv', 'webm', 'mpeg', '3gp'];
                $album_category->file_type = in_array($mimeType, $video_types) ? 'video' : 'image';
            }
            $album_category->instructor_id = Auth::user()->id;
            $album_category->tenant_id = tenant('id');
            $album_category->title = $request->title;
            $album_category->slug = Str::slug($request->title);
            $album_category->description = $request->description;
            $album_category->payment_mode = array_key_exists('paid', $request->all()) ? ($request?->paid == 'on' ? 'paid' : 'un-paid') : 'un-paid';
            $album_category->price = array_key_exists('paid', $request->all()) && $request?->paid == 'on' && !empty($request?->price) ? $request?->price : 0;
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
            if (!is_null($posts)) {
                return  view('admin.album.category.edit', compact('posts'));
            } else {
                return redirect()->back()->with('failed', __('Album Category not found.'));
            }
        } else {
            return redirect()->back()->with('failed', __('Permission denied.'));
        }
    }

    public function getCategories()
    {
        $album_categories = AlbumCategory::with('purchaseAlbum')
            ->where([
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

    public function getCategoryAlbums($id)
    {
        if (Auth::user()->can('manage-blog')) {
            $albums = Album::where('album_category_id', $id)->get();
            return view('admin.posts.album', compact('albums'));
        } else {
            return redirect()->back()->with('failed', __('Permission denied.'));
        }
    }

    public function likeAlbum()
    {
        try {
            $post = Album::find(request()->post_id);
            if (!!$post) {
                $postLike = Auth::user()->likeAlbum->firstWhere('album_id', $post->id);

                if (!!$postLike) {
                    $postLike->delete();
                    return redirect()->back()->with('success', __('Unliked'));
                }

                $postLike = new LikeAlbum();
                $postLike->album_id = $post->id;
                if (Auth::user()->type === Role::ROLE_STUDENT)
                    $postLike->student_id = Auth::user()->id;
                else
                    $postLike->instructor_id = Auth::user()->id;
                $postLike->save();
                return redirect()->back()->with('success', __('Album Liked Successfully'));
            } else
                return redirect()->back()->with('failed', __('UnSuccessfull'));
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function purchaseAlbumCategory(Request $request)
    {
        $request->validate([
            'post_id' => 'required'
        ]);

        try {
            $post = AlbumCategory::where('payment_mode', 'paid')->where('id', $request->post_id)->where('status', 'active')->first();
            $purchasePost = PurchaseAlbum::firstOrCreate(
                [
                    'student_id' => Auth::user()->id,
                    'album_category_id' => $post->id,
                ],
                [
                    'active_status' => false,
                ]
            );

            Stripe::setApiKey(config('services.stripe.secret'));

            $session = Session::create(
                [
                    'line_items'            => [[
                        'price_data'    => [
                            'currency'      => config('services.stripe.currency'),
                            'product_data'  => [
                                'name'      => "$post->title",
                            ],
                            'unit_amount'   => $post->price * 100,
                        ],
                        'quantity'      => 1,
                    ]],
                    'customer' => Auth::user()?->stripe_cus_id,
                    'mode' => 'payment',
                    'success_url' => route('purchase-album-success', [
                        'purchase_post_id' => $purchasePost?->id,
                        'student_id' => Auth::user()->id,
                        'redirect' => $request->redirect
                    ]),
                    'cancel_url' => route('subscription-unsuccess'),
                ]
            );
            if (!empty($session?->id)) {
                $purchasePost->session_id = $session?->id;
                $purchasePost->save();
            }
            if ($request->redirect == 1) {
                return response($session->url);
            }
            return redirect($session->url);
        } catch (Error $e) {
            return response($e, 419);
        }
    }

    public function createAlbum($id)
    {
        if (Auth::user()->can('create-blog')) {
            $album_category = AlbumCategory::where('id', $id)
                ->where('instructor_id', Auth::user()->id)
                ->first(['id', 'title']);

            if (!$album_category) {
                return redirect()->back()->with('failed', __('Category not found.'));
            }

            // convert to array for Form::select
            $album_categories = [$album_category->id => $album_category->title];

            return view('admin.album.create', compact('album_categories', 'album_category'));
        } else {
            return redirect()->back()->with('failed', __('Permission denied.'));
        }
    }
}
