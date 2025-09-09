<?php

namespace App\DataTables\Superadmin;

use App\Models\SuperAdminInstructor;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;
use Yajra\DataTables\EloquentDataTable;

class SuperAdminInstructorDataTable extends DataTable
{
    public function dataTable($query)
    {
        $dataTable = new EloquentDataTable($query);

        return $dataTable
            ->addIndexColumn()
            ->addColumn('instructor_image', function ($row) {
                if ($row->instructor_image) {
                    return '<img src="' . asset('storage/app/public/' . $row->instructor_image) . '" class="w-16 h-16 rounded-full"/>';
                }
                return '—'; // fallback if no image
            })
            ->addColumn('name', function ($row) {
                return e($row->name ?? '—');
            })
            ->addColumn('action', function ($row) {
                $editUrl = route('super-admin-instructors.edit', $row->id);
                $deleteUrl = route('super-admin-instructors.destroy', $row->id);

                return '
                    <a href="' . $editUrl . '" class="btn btn-sm btn-icon btn-warning me-1" title="Edit">
                        <i class="fas fa-edit"></i>
                    </a>
                    <button type="button" 
                        class="btn btn-sm btn-icon btn-danger" 
                        data-bs-toggle="modal" 
                        data-bs-target="#deleteModal" 
                        data-url="' . $deleteUrl . '" 
                        title="Delete">
                        <i class="fas fa-trash-alt"></i>
                    </button>
                ';
            })
            ->rawColumns(['instructor_image', 'action']);
    }

    public function query(SuperAdminInstructor $model)
    {
        return $model->newQuery();
    }

    public function html()
    {
        return $this->builder()
            ->setTableId('superadmininstructors-table')
            ->addTableClass('display responsive nowrap')
            ->columns($this->getColumns())
            ->minifiedAjax()
            ->orderBy(1)
            ->parameters([
                "dom" => "
                    <'dataTable-top row'<'dataTable-title col-lg-3 col-sm-12 d-none d-sm-block'>
                    <'dataTable-botton table-btn col-lg-6 col-sm-12'B><'dataTable-search tb-search col-lg-3 col-sm-12'f>>
                    <'dataTable-container'<'col-sm-12'tr>>
                    <'dataTable-bottom row'<'dataTable-dropdown page-dropdown col-lg-2 col-sm-12'l>
                    <'col-sm-7'p>>
                ",
                'buttons' => [
                    [
                        'extend' => 'create',
                        'className' => 'btn btn-light-primary no-corner me-1',
                        'text' => __('Create'),
                        'action' => "function ( e, dt, node, config ) {
                            window.location = '" . route('super-admin-instructors.create') . "';
                        }"
                    ],
                    ['extend' => 'reset', 'className' => 'btn btn-light-danger me-1'],
                    ['extend' => 'reload', 'className' => 'btn btn-light-warning'],
                ],
                "scrollX" => true,
                "responsive" => [
                    "scrollX" => false,
                    "details" => [
                        "display" => "$.fn.dataTable.Responsive.display.childRow",
                        "renderer" => "function (api, rowIdx, columns) {
                            var data = $('<table/>').addClass('vertical-table');
                            $.each(columns, function (i, col) {
                                data.append(
                                    '<tr>' +
                                        '<td><strong>' + col.title + '</strong></td>' +
                                        '<td>' + col.data + '</td>' +
                                    '</tr>'
                                );
                            });
                            return data;
                        }"
                    ]
                ],
            ]);
    }

    protected function getColumns()
    {
        return [
            Column::make('DT_RowIndex')->title(__('#'))->searchable(false)->orderable(false),
            Column::make('instructor_image')->title(__('Image'))->searchable(false)->orderable(false),
            Column::make('name')->title(__('Name')),
            Column::make('bio')->title(__('Bio')),
            Column::make('domain')->title(__('Domain')),
            Column::computed('action')
                ->title(__('Actions'))
                ->exportable(false)
                ->printable(false)
                ->addClass('text-center')
                ->width('20%'),
        ];
    }

    protected function filename(): string
    {
        return 'super-admin-instructors_' . date('YmdHis');
    }
}
