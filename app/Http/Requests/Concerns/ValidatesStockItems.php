<?php

namespace App\Http\Requests\Concerns;

use App\Models\CylinderType;
use Illuminate\Validation\Validator;

/**
 * Shared validation for line-item forms posting parallel arrays brand_id[], cylinder_type_id[], quantity[]
 * A $prefix (e.g. 'extra_') validates a second item table in the same form: extra_brand_id[], ...
 */
trait ValidatesStockItems
{
    /**
     * Drop fully blank rows and re-index, so "at least one row" and row errors line up with the re-rendered form
     */
    /**
     * @param  array<int, string>  $extraFields  other per-row fields kept in step (e.g. ['customer_id'])
     */
    protected function prepareStockItems(string $prefix = '', array $extraFields = []): void
    {
        $columns = collect(['brand_id', 'cylinder_type_id', 'quantity', ...$extraFields])
            ->mapWithKeys(fn ($field) => [$prefix.$field => (array) $this->input($prefix.$field, [])]);

        $rows = collect(array_keys($columns->reduce(fn ($keys, $values) => $keys + $values, [])))
            ->filter(fn ($i) => $columns->contains(fn ($values) => filled($values[$i] ?? null)))
            ->values();

        $this->merge($columns->map(fn ($values) => $rows->map(fn ($i) => $values[$i] ?? null)->all())->all());
    }

    /**
     * @param  bool  $required  at least one row needed
     * @param  bool  $distinctType  each cylinder type only once (turn off when rows are also split by e.g. customer)
     */
    protected function stockItemRules(string $prefix = '', bool $required = true, bool $distinctType = true): array
    {
        $array = $required ? 'required|array|min:1' : 'nullable|array';

        return [
            $prefix.'brand_id' => $array,
            $prefix.'cylinder_type_id' => $required ? 'required|array' : 'nullable|array',
            $prefix.'quantity' => $required ? 'required|array' : 'nullable|array',

            $prefix.'brand_id.*' => 'required|exists:cylinder_brands,id',
            // A type belongs to one brand, so a distinct type also means a distinct brand + size row
            $prefix.'cylinder_type_id.*' => 'required|'.($distinctType ? 'distinct|' : '').'exists:cylinder_types,id',
            $prefix.'quantity.*' => 'required|integer|min:1',
        ];
    }

    protected function stockItemMessages(string $prefix = ''): array
    {
        return [
            $prefix.'brand_id.required' => 'Add at least one cylinder.',
            $prefix.'brand_id.min' => 'Add at least one cylinder.',
            $prefix.'brand_id.*.required' => 'The brand field is required.',
            $prefix.'cylinder_type_id.*.required' => 'The type field is required.',
            $prefix.'cylinder_type_id.*.distinct' => 'This brand and type is already added in another row.',
            $prefix.'quantity.*.required' => 'The quantity field is required.',
            $prefix.'quantity.*.integer' => 'The quantity must be a number.',
            $prefix.'quantity.*.min' => 'The quantity must be at least 1.',
        ];
    }

    /**
     * The selected type must belong to the selected brand (the form only filters this on the client)
     */
    protected function validateStockItemBrands(Validator $validator, string $prefix = ''): void
    {
        $typeIds = (array) $this->input($prefix.'cylinder_type_id');
        $brandOfType = CylinderType::whereIn('id', array_filter($typeIds))->pluck('brand_id', 'id');

        foreach ($typeIds as $i => $typeId) {
            if (isset($brandOfType[$typeId]) && (int) $brandOfType[$typeId] !== (int) $this->input("{$prefix}brand_id.$i")) {
                $validator->errors()->add("{$prefix}cylinder_type_id.$i", 'This type does not belong to the selected brand.');
            }
        }
    }

    /**
     * Total quantity per cylinder type across all rows
     *
     * @return array<int, int>
     */
    protected function quantitiesByType(string $prefix = ''): array
    {
        $totals = [];

        foreach ((array) $this->input($prefix.'cylinder_type_id') as $i => $typeId) {
            $totals[$typeId] = ($totals[$typeId] ?? 0) + (int) $this->input("{$prefix}quantity.$i");
        }

        return $totals;
    }

    /**
     * Validated rows as stock items: [['brand_id' => .., 'cylinder_type_id' => .., 'quantity' => ..], ...]
     */
    public function stockItems(string $prefix = '', array $extraFields = []): array
    {
        return collect((array) $this->input($prefix.'cylinder_type_id'))
            ->map(fn ($typeId, $i) => [
                'brand_id' => $this->input("{$prefix}brand_id.$i"),
                'cylinder_type_id' => $typeId,
                'quantity' => (int) $this->input("{$prefix}quantity.$i"),
            ] + collect($extraFields)->mapWithKeys(fn ($field) => [$field => $this->input("{$prefix}{$field}.$i")])->all())
            ->values()
            ->all();
    }
}
