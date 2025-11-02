<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Operation extends Model
{
    use HasFactory;

    protected $fillable = [
        'compte_id',
        'type_operation',
        'montant',
        'description',
        'date_operation',
    ];

    protected $casts = [
        'montant' => 'decimal:2',
        'date_operation' => 'datetime',
    ];

    /**
     * Relation : une opération appartient à un compte
     */
    public function compte()
    {
        return $this->belongsTo(Compte::class, 'compte_id');
    }
}
