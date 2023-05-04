<?php

namespace App\Services\Utilities;

use Illuminate\Database\Eloquent\Builder;

abstract class QueryFilter
{
    protected Builder $query;

    public function __construct($query)
    {
        $this->query = $query;
    }
}
