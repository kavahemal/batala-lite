<?php

namespace App\DataTables;

use App\Models\Booking;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Column;

class BookingDataTable extends BaseDataTable
{
    protected string $permissionPrefix = 'booking';

    /**
     * Build the DataTable class.
     *
     * @param  QueryBuilder<Booking>  $query  Results from query() method.
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        $dataTable = new EloquentDataTable($query);

        if ($this->canShowAction()) {
            $dataTable->addColumn('action', fn ($data) => view('booking.action', ['id' => $data->id, 'booking' => $data]));
        }

        return $dataTable
            ->filter(function ($query) {
                if ($status = request('filter_status')) {
                    $query->where('bookings.status', $status);
                }
                if (request('filter_short_stock')) {
                    $query->where('bookings.is_short_stock', true);
                }
            }, true)
            ->editColumn('customer_id', fn ($data) => $data->customer?->name)
            ->editColumn('booking_date', fn ($data) => dateTimeFormat($data->booking_date))
            ->editColumn('created_at', fn ($data) => $this->dateBy($data->created_at, $data->creator))
            ->editColumn('status', fn ($data) => view('booking.status', ['booking' => $data])->render())
            ->setRowId('id')
            ->rawColumns(['action', 'status', 'created_at']);
    }

    /**
     * Get the query source of dataTable.
     *
     * @return QueryBuilder<Booking>
     */
    public function query(Booking $model): QueryBuilder
    {
        return $model->newQuery()->with(['customer', 'creator'])->select('bookings.*');
    }

    /**
     * Optional method if you want to use the html builder.
     */
    public function html(): HtmlBuilder
    {
        // Send the list filters (booking/index.blade.php) with each request; newest bookings first
        return $this->baseHtml()
            ->minifiedAjax(getAjaxDatatableUrl(), "data.filter_status = $('#filter-status').val(); data.filter_short_stock = $('#filter-short-stock').is(':checked') ? 1 : '';")
            ->orderBy(0, 'desc');
    }

    /**
     * Get the dataTable columns definition.
     */
    public function getColumns(): array
    {
        $columns = [
            Column::make('booking_no'),
            Column::make('customer_id')->title('Customer')->name('customer.name'),
            Column::make('status'),
            Column::make('booking_date')->title('Booking Date')->width(250),
            Column::make('created_at')->title('Created')->width(250),
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
        return 'Booking_'.date('YmdHis');
    }
}
