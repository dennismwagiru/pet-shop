<?php

namespace App\Services\Utilities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

abstract class QueryFilter
{
    /**
     * @var Builder<Model>
     */
    protected Builder $query;

    /**
     * @param Builder<Model> $query
     */
    public function __construct(Builder $query)
    {
        $this->query = $query;
    }
}
