<?php

namespace App\DataTables;

use App\Models\CylinderType;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Column;

class CylinderTypeDataTable extends BaseDataTable
{
    protected string $permissionPrefix = 'cylinder-types';

    /**
     * Build the DataTable class.
     *
     * @param  QueryBuilder<CylinderType>  $query  Results from query() method.
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        $dataTable = new EloquentDataTable($query);

        if ($this->canShowAction()) {
            $dataTable->addColumn('action', 'cylinder-types.action');
        }

        return $dataTable
            ->editColumn('brand_id', fn ($data) => $data->cylinderBrand?->name)
            ->editColumn('updated_at', fn ($data) => $this->dateBy($data->updated_at, $data->updater))
            ->editColumn('weight', fn ($data) => ($data->weight.' kg'))
            ->editColumn('is_active', fn ($data) => ($data->is_active ? '<span class="badge bg-success">Yes</span>' : '<span class="badge bg-danger">No</span>'))
            ->setRowId('id')
            ->rawColumns(['action', 'is_active', 'updated_at']);
    }

    /**
     * Get the query source of dataTable.
     *
     * @return QueryBuilder<CylinderType>
     */
    public function query(CylinderType $model): QueryBuilder
    {
        return $model->newQuery()->with(['cylinderBrand', 'updater'])->select('cylinder_types.*');
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
            Column::make('brand_id')->title('Brand')->name('cylinderBrand.name'),
            Column::make('weight'),
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
        return 'CylinderType_'.date('YmdHis');
    }
}
