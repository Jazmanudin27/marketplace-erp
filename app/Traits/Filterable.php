<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;

trait Filterable
{
    /**
     * Filter query based on request parameters.
     */
    public function scopeFilter(Builder $query, array $filters): Builder
    {
        foreach ($filters as $key => $value) {
            if ($value === null || $value === '') {
                continue;
            }

            if (method_exists($this, 'scope' . ucfirst($key))) {
                $query->{$key}($value);
            } elseif (in_array($key, $this->fillable)) {
                $query->where($key, $value);
            }
        }

        return $query;
    }
}
