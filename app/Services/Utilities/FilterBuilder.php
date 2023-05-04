<?php

namespace App\Services\Utilities;

use Illuminate\Database\Eloquent\Builder;

class FilterBuilder
{
    protected Builder $query;
    protected array $filters;
    protected string $namespace;

    public function __construct($query, $filters, $namespace)
    {
        $this->query = $query;
        $this->filters = $filters;
        $this->namespace = $namespace;
    }

    public function apply(): Builder
    {
        foreach ($this->filters as $name => $value) {
            $normalizedName = $this->normalizeName($name);
            $class = "{$this->namespace}\\{$normalizedName}";

            if (!class_exists($class)) {
                continue;
            }

            (new $class($this->query))->handle($value);
        }

        return $this->query;
    }

    /**
     * Normalize name
     * @param $name
     * @return string
     */
    private function normalizeName($name): string
    {
        // Remove everything upto and including '__'
        [$result] = explode("__", $name, 2);
        return implode('', array_map(function ($var) {
            return ucfirst($var);
        }, explode('_', $result)));
    }
}
