<?php

namespace App\Http\Livewire;

use App\Models\Lesson;
use App\Models\Post;
use Illuminate\Contracts\Database\Eloquent\Builder;
use LaravelViews\Views\GridView;

class StudentDashboardView extends GridView
{
    public $maxCols = 4;
    protected $paginate = 40;

    /**
     * Sets the data to every card on the view
     *
     * @param $model Current model for each card
     */
    public $cardComponent = 'admin.lessons.card';

    protected $currentView;
    protected $cachedCurrency = null;
    protected $cachedCurrencySymbol = null;

    public function __construct()
    {
        $currentView = request()->query('view');
        $this->currentView = !empty($currentView) ? $currentView : 'in-person';
    }
    /**
     * Sets a model class to get the initial data
     */
    public function repository(): Builder
    {
        if ($this->currentView == 'posts') {
            $this->cardComponent = 'admin.posts.card';
            return Post::orderBy('created_at', 'desc');
        }
        $query = Lesson::where('active_status', true);

        return match ($this->currentView) {
            'in-person' => $query->with(['packages' => function ($query) {
                return $query->orderBy('number_of_slot');
            }, 'slots.student', 'slots.lesson', 'user'])->whereIn('type', [Lesson::LESSON_TYPE_INPERSON, Lesson::LESSON_TYPE_PACKAGE])
                ->where(function ($q) {
                    $q->where('payment_method', '!=', 'online')
                        ->orWhereHas('user', function ($q) {
                            $q->where('is_stripe_connected', true);
                        });
                }),
            'online' => $query->with(['slots.student', 'slots.lesson', 'user'])->where('type', Lesson::LESSON_TYPE_ONLINE)
                ->whereHas('user', function ($q) {
                    $q->where('is_stripe_connected', true);
                }),
            default => $query->with(['slots.student', 'slots.lesson', 'user'])
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

        $allSlots = ($model->type === 'inPerson' || $model->type == 'package') ?
            $model->slots->filter(function ($slot) {
                return $slot->student->count() < $slot->lesson->max_students;
            })->values() : null;

        return [
            'image' => isset($model->user->avatar)
                ? asset('/storage' . '/' . tenant('id') . '/' . $model->user->avatar)
                : asset('assets/img/logo/logo.png'),
            'title' => $model->lesson_name,
            'subtitle' => str_replace(['(', ')'], '', $symbol) . ' ' . $model->lesson_price . ' (' . strtoupper($currency) . ')',
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
        $data = $this->repository()->get();

        if ($data->isEmpty()) {
            return view('admin.lessons.nolesson');
        }
        return parent::render();
    }
}