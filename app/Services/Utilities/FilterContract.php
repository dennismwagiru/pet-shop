<?php

namespace App\Services\Utilities;

interface FilterContract
{
    public function handle($value): void;
}
