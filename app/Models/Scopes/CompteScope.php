<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

/**
 * Scope global pour filtrer automatiquement les comptes non supprimés
 */
class CompteScope implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     * Filtre automatiquement les comptes supprimés (soft delete)
     */
    public function apply(Builder $builder, Model $model): void
    {
        // Si la table a une colonne 'deleted_at', on filtre les comptes non supprimés
        if (\Schema::hasColumn($model->getTable(), 'deleted_at')) {
            $builder->whereNull('deleted_at');
        }
    }
}
