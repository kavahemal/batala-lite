<?php

namespace App\DataTables;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Column;

class UserDataTable extends BaseDataTable
{
    protected string $permissionPrefix = 'users';

    /**
     * Build the DataTable class.
     *
     * @param  QueryBuilder<User>  $query  Results from query() method.
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        $dataTable = new EloquentDataTable($query);

        if ($this->canShowAction()) {
            $dataTable->addColumn('action', 'users.action');
        }

        return $dataTable
            ->editColumn('updated_at', fn ($data) => $this->dateBy($data->updated_at, $data->updater))
            ->editColumn('is_active', fn ($data) => ($data->is_active ? '<span class="badge bg-success">Yes</span>' : '<span class="badge bg-danger">No</span>'))
            ->editColumn('role', fn ($data) => '<span class="badge bg-info fw-bold">'.ucfirst($data->roles->first()->name).'</span>')
            ->setRowId('id')
            ->rawColumns(['action', 'is_active', 'role', 'updated_at']);
    }

    /**
     * Get the query source of dataTable.
     *
     * @return QueryBuilder<User>
     */
    public function query(User $model): QueryBuilder
    {
        $user = Auth::user();

        return $model->newQuery()->with(['roles', 'updater'])
            ->where('id', '!=', $user->id)
            ->whereHas('roles', function ($query) use ($user) {
                $query->where('id', '>=', $user->roles->first()->id);
            })->select('users.*');
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
            Column::make('email'),
            Column::make('role')->name('roles.name'),
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
        return 'User_'.date('YmdHis');
    }
}
