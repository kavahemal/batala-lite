<?php

namespace App\DataTables;

use App\Models\Purchase;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class PurchaseDataTable extends BaseDataTable
{
    protected string $permissionPrefix = 'purchase';

    /**
     * Build the DataTable class.
     *
     * @param  QueryBuilder<Purchase>  $query  Results from query() method.
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        $dataTable = new EloquentDataTable($query);

        if ($this->canShowAction()) {
            $dataTable->addColumn('action', 'purchase.action');
        }

        return $dataTable
            ->editColumn('purchase_date', fn ($data) => dateTimeFormat($data->purchase_date))
            ->editColumn('truck.truck_no', fn ($data) => $data->truck?->label())
            ->setRowId('id')
            ->editColumn('updated_at', fn ($data) => $this->dateBy($data->updated_at, $data->updater))
            ->rawColumns(['action', 'updated_at']);
    }

    /**
     * Get the query source of dataTable.
     *
     * @return QueryBuilder<Purchase>
     */
    public function query(Purchase $model): QueryBuilder
    {
        return $model->newQuery()->select('purchases.*')->with(['truck', 'driver', 'updater']);
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
            Column::make('purchase_no'),
            Column::make('purchase_date')->width(250),
            Column::make('driver.name')->title('Driver'),
            Column::make('driver.mobile')->title('Driver mobile'),
            Column::make('truck.truck_no')->title('Truck no'),
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
        return 'Purchase_'.date('YmdHis');
    }
}
