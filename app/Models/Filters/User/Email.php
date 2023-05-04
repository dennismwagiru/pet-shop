<?php

namespace App\Models\Filters\User;

use App\Services\Utilities\FilterContract;
use App\Services\Utilities\QueryFilter;

class Email extends QueryFilter implements FilterContract
{
    public function handle($value): void
    {
        $this->query->where('email', 'like', '%'.$value.'%');
    }
}
