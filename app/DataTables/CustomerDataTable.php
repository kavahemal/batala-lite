<?php

namespace App\DataTables;

use App\Models\Customer;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Column;

class CustomerDataTable extends BaseDataTable
{
    protected string $permissionPrefix = 'customers';

    /**
     * Build the DataTable class.
     *
     * @param  QueryBuilder<Customer>  $query  Results from query() method.
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        $dataTable = new EloquentDataTable($query);

        if ($this->canShowAction()) {
            $dataTable->addColumn('action', 'customers.action');
        }

        return $dataTable
            ->editColumn('updated_at', fn ($data) => $this->dateBy($data->updated_at, $data->updater))
            ->editColumn('address', fn ($data) => nl2br($data->address))
            ->editColumn('is_active', fn ($data) => ($data->is_active ? '<span class="badge bg-success">Yes</span>' : '<span class="badge bg-danger">No</span>'))
            ->setRowId('id')
            ->rawColumns(['action', 'address', 'is_active', 'updated_at']);
    }

    /**
     * Get the query source of dataTable.
     *
     * @return QueryBuilder<Customer>
     */
    public function query(Customer $model): QueryBuilder
    {
        return $model->newQuery()->with('updater');
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
            Column::make('customer_no'),
            Column::make('name'),
            Column::make('mobile_number')->width(150),
            Column::make('address'),
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
        return 'Customer_'.date('YmdHis');
    }
}
