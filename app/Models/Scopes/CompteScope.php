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
     * Filtre automatiquement les comptes supprimés (soft delete) et bloqués
     */
    public function apply(Builder $builder, Model $model): void
    {
        // Si la table a une colonne 'deleted_at', on filtre les comptes non supprimés
        if (\Schema::hasColumn($model->getTable(), 'deleted_at')) {
            $builder->whereNull('deleted_at');
        }

        // Filtre les comptes bloqués
        $builder->where('etat_compte', '!=', 'bloque');

        // Filtre les comptes archivés (non archivés par défaut)
        if (\Schema::hasColumn($model->getTable(), 'is_archived')) {
            $builder->where('is_archived', false);
        }
    }
}
