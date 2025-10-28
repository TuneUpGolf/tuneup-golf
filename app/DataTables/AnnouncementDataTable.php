<?php

namespace App\DataTables;

use App\Models\Announcement;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Html\Editor\Editor;
use Yajra\DataTables\Html\Editor\Fields;
use Yajra\DataTables\Services\DataTable;

class AnnouncementDataTable extends DataTable
{
    /**
     * Build DataTable class.
     *
     * @param QueryBuilder $query Results from query() method.
     * @return \Yajra\DataTables\EloquentDataTable
     */
     public function dataTable($query)
    {
        return datatables()
            ->eloquent($query)
            ->addColumn('action', function ($announcement) {
                return view('admin.announcements.action', compact('announcement'));
            })
            ->rawColumns(['action']);
    }

    /**
     * Get query source of dataTable.
     *
     * @param \App\Models\Announcement $model
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query(Announcement $model): QueryBuilder
    {
        return $model->newQuery();
    }

    /**
     * Optional method if you want to use html builder.
     *
     * @return \Yajra\DataTables\Html\Builder
     */
   public function html()
    {
        return $this->builder()
            ->setTableId('announcements-table')
            ->columns($this->getColumns())
            ->minifiedAjax()
            ->orderBy(1)
            ->parameters([
                'dom' => 'Bfrtip',
                'stateSave' => true,
                'buttons' => [],
            ]);
    }

    /**
     * Get the dataTable columns definition.
     *
     * @return array
     */
    protected function getColumns()
    {
         return [
            Column::make('id'),
          
            Column::make('title')->title(__('Title')),
            Column::make('content')->title(__('Content')),
            Column::make('is_active')->title(__('Status')),
            Column::computed('action')
                ->exportable(false)
                ->printable(false)
                ->width(120)
                ->addClass('text-center'),
        ];
    }

    /**
     * Get filename for export.
     *
     * @return string
     */
    protected function filename(): string
    {
        return 'Announcement_' . date('YmdHis');
    }
}
