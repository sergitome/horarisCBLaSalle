<?php

namespace App\Http\Controllers\Concerns;

trait AuthorizesCrud
{
    protected function booleanFilter($query, string $input, string $column): void
    {
        $value = request($input);

        if ($value === '1' || $value === '0') {
            $query->where($column, (bool) $value);
        }
    }
}
