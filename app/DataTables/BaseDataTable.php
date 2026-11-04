<?php

namespace App\DataTables;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Services\DataTable;

abstract class BaseDataTable extends DataTable
{
    /**
     * Permission prefix (customers, roles, products, etc.)
     * MUST be defined in child class
     */
    protected string $permissionPrefix;

    /**
     * Authenticated user (cached)
     */
    protected ?User $user;

    public function __construct()
    {
        $this->user = Auth::user();
    }

    /**
     * Default HTML builder (shared by all datatables)
     */
    protected function baseHtml(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId($this->permissionPrefix.'-table')
            ->languageSearchPlaceholder('Search')
            ->columns($this->getColumns())
            ->minifiedAjax(getAjaxDatatableUrl())
            ->orderBy(0, 'asc');
        // ->selectStyleSingle();
    }

    /**
     * Can show Action column
     */
    protected function canShowAction(): bool
    {
        if (! $this->user || empty($this->permissionPrefix)) {
            return false;
        }

        return $this->user->canAny([
            "{$this->permissionPrefix}.edit",
            "{$this->permissionPrefix}.destroy",
        ]);
    }

    /**
     * Check edit permission
     */
    protected function canEdit(): bool
    {
        return $this->user
            ? $this->user->can("{$this->permissionPrefix}.edit")
            : false;
    }

    /**
     * Check delete permission
     */
    protected function canDelete(): bool
    {
        return $this->user
            ? $this->user->can("{$this->permissionPrefix}.destroy")
            : false;
    }

    /**
     * Date with "by user" below it, for created_at / updated_at columns
     */
    protected function dateBy($date, ?User $by, string $format = 'date_time'): string
    {
        return dateTimeFormat($date, $format).($by ? '<br /><small class="text-muted">by '.e($by->name).'</small>' : '');
    }
}
