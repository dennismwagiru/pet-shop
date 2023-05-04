<?php

namespace App\Models\Events;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;

class SetModelUuid
{
    public function __construct(Model $model)
    {
        $model->uuid = Str::uuid();
    }
}
