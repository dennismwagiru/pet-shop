<?php

namespace App\Models\Filters\User;

use App\Services\Utilities\FilterContract;
use App\Services\Utilities\QueryFilter;

class Marketing extends QueryFilter implements FilterContract
{
    public function handle($value): void
    {
        $this->query->where('is_marketing', boolean($value));
    }
}
