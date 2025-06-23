<?php

namespace App\Http\Livewire;

use App\Models\Lesson;
use App\Models\Post;
use App\Models\User;
use Illuminate\Contracts\Database\Eloquent\Builder;
use LaravelViews\Views\GridView;

class StudentDashboardView extends GridView
{
    public $maxCols = 4;
    // protected $paginate = 4;

    /**
     * Sets the data to every card on the view
     *
     * @param $model Current model for each card
     */
    public $cardComponent = 'admin.lessons.card';

    protected $currentView;

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
            'in-person' => $query->with('packages')->whereIn('type', [Lesson::LESSON_TYPE_INPERSON, Lesson::LESSON_TYPE_PACKAGE])
                ->where(function ($q) {
                    $q->where('payment_method', '!=', 'online')
                        ->orWhereHas('user', function ($q) {
                            $q->where('is_stripe_connected', true);
                        });
                }),
            'online' => $query->where('type', Lesson::LESSON_TYPE_ONLINE)
                ->whereHas('user', function ($q) {
                    $q->where('is_stripe_connected', true);
                }),
            default => $query
        };
    }

    public function card($model)
    {
        if ($this->currentView == 'posts') {
            return [];
        }

        $model->load('user');
        $tenantId = tenancy()->tenant->id;
        $currency = tenancy()->central(function () use ($tenantId) {
            return User::where('tenant_id', $tenantId)->value('currency');
        });

        $symbol = User::getCurrencySymbol($currency);

        return [
            'image' =>  isset($model->user->logo) ?
                asset('/storage' . '/' . tenant('id') . '/' . $model->user->logo) :
                asset('assets/img/logo/logo.png'),
            'title' => $model->lesson_name,
            'subtitle' => str_replace(['(', ')'], '', $symbol) . ' ' . $model->lesson_price . ' (' . strtoupper($currency) . ')',
            'description' => $model->lesson_description,
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
