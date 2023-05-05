<?php

namespace App\Services\Utilities;

interface FilterContract
{
    public function handle(string|bool $value): void;
}
