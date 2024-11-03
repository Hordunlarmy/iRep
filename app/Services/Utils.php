<?php

namespace App\Services;

class Utils
{
    public static function filterNullValues(array $data): array
    {
        return array_map(function ($item) {
            return array_filter($item, function ($value) {
                return !is_null($value);
            });
        }, $data);
    }
}
