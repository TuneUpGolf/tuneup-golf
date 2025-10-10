<?php

namespace App\Http\Livewire;

use App\Models\Post;
use App\Models\Album;
use App\Models\Lesson;
use App\Models\AlbumCategory;
use LaravelViews\Views\GridView;
use Illuminate\Support\Facades\Storage;
use Illuminate\Contracts\Database\Eloquent\Builder;

class StudentDashboardView extends GridView
{
    public $maxCols = 4;
    protected $paginate = 40;
    public $instructor_id;

    public $cardComponent = 'admin.lessons.card';

    protected $currentView;
    protected $cachedCurrency = null;
    protected $cachedCurrencySymbol = null;

    public function __construct()
    {
        parent::__construct();
        $currentView = request()->query('view');
        $this->currentView = !empty($currentView) ? $currentView : 'in-person';
    }

    public function repository(): Builder
    {
        if (request()->query('category_album')) {
            $this->cardComponent = 'admin.posts.album-card-view';
            if (request()->query('instructor_id')) {
                return Album::where('instructor_id', request()->query('instructor_id'))
                    ->where('album_category_id', request()->query('category_album'));
            } else {
                return Album::where('album_category_id', request()->query('category_album'));
            }
        }

        if (request()->query('category')) {
            $this->cardComponent = 'admin.posts.album-view';
            if (request()->query('instructor_id')) {
                return AlbumCategory::where('instructor_id', request()->query('instructor_id'));
            } else {
                return AlbumCategory::query();
            }
        }


        if ($this->currentView == 'posts') {
            $this->cardComponent = 'admin.posts.card-new';
            if (request()->query('instructor_id')) {
                return  Post::where('instructor_id', request()->query('instructor_id'))->orderBy('created_at', 'desc');
            } else {
                return  Post::orderBy('created_at', 'desc');
            }
        }

        $query = Lesson::where('active_status', true);

        if (!empty($this->instructor_id)) {
            $query->where('created_by', $this->instructor_id);
        }

        return match ($this->currentView) {
            'in-person' => $query
                ->with([
                    'packages' => fn($q) => $q->orderBy('number_of_slot'),
                    'slots.student',
                    'slots.lesson',
                    'user'
                ])
                ->whereIn('type', [Lesson::LESSON_TYPE_INPERSON, Lesson::LESSON_TYPE_PACKAGE])
                ->where(function ($q) {
                    $q->where('payment_method', '!=', 'online')
                        ->orWhereHas('user', function ($q) {
                            $q->where('is_stripe_connected', true);
                        });
                }),

            'online' => $query->with(['slots.student', 'slots.lesson', 'user'])
                ->where('type', Lesson::LESSON_TYPE_ONLINE)
                ->whereHas('user', fn($q) => $q->where('is_stripe_connected', true)),

            default => $query->with(['slots.student', 'slots.lesson', 'user']),
        };
    }

    public function card($model)
    {
        if ($this->currentView == 'posts') {
            return [];
        }

        $model->load('user');

        if ($this->cachedCurrency === null) {
            $this->cachedCurrency = \App\Facades\UtilityFacades::getsettings('currency');
        }
        if ($this->cachedCurrencySymbol === null) {
            $this->cachedCurrencySymbol = \App\Models\User::getCurrencySymbol($this->cachedCurrency);
        }

        $symbol = $this->cachedCurrencySymbol;
        $currency = $this->cachedCurrency;

        $firstSlot = $model->slots->first();
        if ($firstSlot) {
            $studentCount = $firstSlot->student->count();
            $isFullyBooked = $studentCount >= $firstSlot->lesson->max_students;
            $availableSlots = $firstSlot->lesson->max_students - $studentCount;
        } else {
            $studentCount = 0;
            $isFullyBooked = false;
            $availableSlots = 0;
        }

        $allSlots = ($model->type === Lesson::LESSON_TYPE_INPERSON || $model->type == Lesson::LESSON_TYPE_PACKAGE)
            ? $model->slots->filter(fn($slot) => $slot->student->count() < $slot->lesson->max_students)->values()
            : null;

        if ($model->type == Lesson::LESSON_TYPE_PACKAGE) {
            $subtitle = str_replace(['(', ')'], '', $symbol) . ' ' . $model->packages[0]->price . ' (' . strtoupper($currency) . ')';
        } else {
            $subtitle = str_replace(['(', ')'], '', $symbol) . ' ' . $model->lesson_price . ' (' . strtoupper($currency) . ')';
        }

        return [
            // 'image' => isset($model->user->avatar)
            //     ? asset('/storage/' . tenant('id') . '/' . $model->user->avatar)
            //     : asset('assets/img/logo/logo.png'),
            'image' => isset($model->user->avatar)
                ? Storage::disk('tenants')->url($model->user->avatar)
                : asset('assets/img/logo/logo.png'),
            'title' => $model->lesson_name,
            'subtitle' => $subtitle,
            'short_description' => $model->lesson_description,
            'long_description' => $model->long_description,
            'currency' => $currency,
            'currencySymbol' => $symbol,
            'firstSlot' => $firstSlot,
            'bookedCount' => $studentCount,
            'availableSlots' => $availableSlots,
            'isFullyBooked' => $isFullyBooked,
            'allSlots' => $allSlots
        ];
    }

    public function render()
    {
        if (request()->query('category_album')) {
            $albums = $this->repository()->get();
            return view('admin.posts.album-view', compact('albums'));
        }
        if (request()->query('category')) {
            $albums = $this->repository()->get();
            return view('admin.posts.album-category-view', compact('albums'));
        }

        if ($this->currentView === 'posts') {
            $posts = $this->repository()->paginate(6);
            return view('admin.posts.card-new', compact('posts'));
        }


        return parent::render(); // ✅ let GridView handle posts and lessons
    }
}
