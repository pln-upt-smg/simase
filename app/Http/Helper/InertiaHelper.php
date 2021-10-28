<?php

namespace App\Http\Helper;

use Spatie\QueryBuilder\AllowedFilter;

abstract class InertiaHelper
{
    public static function filterBy(array $columns, bool $searchQuery = true): array
    {
        if ($searchQuery) {
            $columns[] = self::searchQueryCallback($columns);
        }
        return $columns;
    }

    public static function searchQueryCallback(array $columns): AllowedFilter
    {
        return AllowedFilter::callback('global', function ($query, $value) use ($columns) {
            if (count($columns) > 0) {
                $query->where(function ($query) use ($value, $columns) {
                    $query->where(trim($columns[0]), 'LIKE', "%$value%");
                    for ($i = 1, $max = count($columns); $i < $max; $i++) {
                        $query->orWhere(trim($columns[$i]), 'LIKE', "%$value%");
                    }
                });
            }
        });
    }
}
