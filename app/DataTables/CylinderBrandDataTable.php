<?php

namespace App\DataTables;

use App\Models\CylinderBrand;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Column;

class CylinderBrandDataTable extends BaseDataTable
{
    protected string $permissionPrefix = 'cylinder-brands';

    /**
     * Build the DataTable class.
     *
     * @param  QueryBuilder<CylinderBrand>  $query  Results from query() method.
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        $dataTable = new EloquentDataTable($query);

        if ($this->canShowAction()) {
            $dataTable->addColumn('action', 'cylinder-brands.action');
        }

        return $dataTable
            ->editColumn('updated_at', fn ($data) => $this->dateBy($data->updated_at, $data->updater))
            ->editColumn('is_active', fn ($data) => ($data->is_active ? '<span class="badge bg-success">Yes</span>' : '<span class="badge bg-danger">No</span>'))
            ->editColumn('cylinder_types_count', fn ($data) => ($data->cylinder_types_count ? '<span class="badge bg-info">'.$data->cylinder_types_count.'</span>' : ''))
            ->editColumn('image', fn ($data) => ($data->image ? '<img class="img-fluid border rounded-3" src="'.(asset('storage/'.$data->image)).'" alt="Image" width="60">' : ''))
            ->setRowId('id')
            ->rawColumns(['action', 'image', 'cylinder_types_count', 'is_active', 'updated_at']);
    }

    /**
     * Get the query source of dataTable.
     *
     * @return QueryBuilder<CylinderBrand>
     */
    public function query(CylinderBrand $model): QueryBuilder
    {
        return $model->newQuery()->with('updater')->withCount('cylinderTypes');
    }

    /**
     * Optional method if you want to use the html builder.
     */
    public function html(): HtmlBuilder
    {
        return $this->baseHtml();
    }

    /**
     * Get the dataTable columns definition.
     */
    public function getColumns(): array
    {
        $columns = [
            Column::make('name'),
            Column::make('image')->width(150)->addClass('text-center')->orderable(false)->searchable(false),
            Column::make('cylinder_types_count')->title('Types Count')->addClass('text-center'),
            Column::make('is_active')->width(200)->addClass('text-center'),
            Column::make('updated_at')->title('Updated')->width(250),
        ];

        if ($this->canShowAction()) {
            $columns[] = Column::computed('action')
                ->exportable(false)
                ->printable(false)
                ->width(180)
                ->addClass('text-center');
        }

        return $columns;
    }

    /**
     * Get the filename for export.
     */
    protected function filename(): string
    {
        return 'CylinderBrand_'.date('YmdHis');
    }
}
