<?php

namespace App\Models\Filters\User;

use App\Services\Utilities\QueryFilter;
use App\Services\Utilities\FilterContract;

class FirstName extends QueryFilter implements FilterContract
{
    public function handle($value): void
    {
        $this->query->where('first_name', 'like', '%'.$value.'%');
    }
}
