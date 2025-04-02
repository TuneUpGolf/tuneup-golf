<?php

namespace App\Http\Livewire;

use App\Filters\LessonTypeFilter;
use App\Models\Lesson;
use Illuminate\Contracts\Database\Eloquent\Builder;
use LaravelViews\Views\GridView;

class LessonsGridView extends GridView
{
    /**
     * Sets a model class to get the initial data
     */
    public function repository(): Builder
    {
        if (!!request()->query('instructor_id'))
            return Lesson::where('created_by', request()->query('instructor_id'));
        else if (request()->query('type') == Lesson::LESSON_TYPE_ONLINE)
            return Lesson::where('type', Lesson::LESSON_TYPE_ONLINE);
        else if (request()->query('type') == Lesson::LESSON_TYPE_INPERSON)
            return Lesson::where('type', Lesson::LESSON_TYPE_INPERSON);
        else
            return Lesson::query();
    }

    public $maxCols = 3;
    // protected $paginate = 4;

    /**
     * Sets the data to every card on the view
     *
     * @param $model Current model for each card
     */
    public $cardComponent = 'admin.lessons.card';

    public function card($model)
    {
        $model->load('user');
        return [
            'image' =>  asset('/storage' . '/' . tenant('id') . '/' . $model->user->logo) ?? asset('assets/img/logo/logo.png'),
            'title' => $model->lesson_name,
            'subtitle' => "$" . $model->lesson_price,
            'description' => $model->lesson_description,
        ];
    }
}
