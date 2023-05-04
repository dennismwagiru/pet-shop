<?php

namespace App\Models\Filters\User;

use App\Services\Utilities\FilterContract;
use App\Services\Utilities\QueryFilter;

class CreatedAt extends QueryFilter implements FilterContract
{
    public function handle($value): void
    {
        $this->query->where('created_at', $value);
    }
}
