<?php

namespace App\Services\Utilities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class FilterBuilder
{
    /**
     * @var Builder<Model>
     */
    protected Builder $query;

    /**
     * @var array|bool[]|int[]|string[]
     */
    protected array $filters;

    /**
     * @var string
     */
    protected string $namespace;

    /**
     * @param Builder<Model> $query
     * @param array<string, string|bool|int> $filters
     * @param string $namespace
     */
    public function __construct(Builder $query, array $filters, string $namespace)
    {
        $this->query = $query;
        $this->filters = $filters;
        $this->namespace = $namespace;
    }

    /**
     * @return Builder<Model>
     */
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
     * @param string $name
     * @return string
     */
    private function normalizeName(string $name): string
    {
        // Remove everything upto and including '__'
        [$result] = explode("__", $name, 2);
        return implode('', array_map(function ($var) {
            return ucfirst($var);
        }, explode('_', $result)));
    }
}
