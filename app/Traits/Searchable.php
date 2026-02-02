<?php
// app/Traits/Searchable.php

namespace App\Traits;

trait Searchable
{
    /**
     * Scope pour la recherche
     */
    public function scopeSearch($query, $search)
    {
        if (empty($search)) {
            return $query;
        }

        return $query->where(function($q) use ($search) {
            foreach ($this->searchable as $field) {
                $q->orWhere($field, 'LIKE', "%{$search}%");
            }
        });
    }

    /**
     * Scope pour la recherche avancée avec filtres
     */
    public function scopeAdvancedSearch($query, $filters)
    {
        foreach ($filters as $field => $value) {
            if (!empty($value) && in_array($field, $this->searchable)) {
                $query->where($field, 'LIKE', "%{$value}%");
            }
        }
        
        return $query;
    }
}