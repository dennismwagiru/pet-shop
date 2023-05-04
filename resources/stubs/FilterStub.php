<?php

namespace DummyNamespace;

use App\Services\Utilities\QueryFilter;
use App\Services\Utilities\FilterContract;

class DummyClass extends QueryFilter implements FilterContract
{
    public function handle($value): void
    {
        // TODO -> implement query filter
        // $this->query->where();
    }
}
