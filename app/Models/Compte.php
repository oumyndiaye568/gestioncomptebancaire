<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use App\Enums\TypeCompte;   
use App\Enums\EtatCompte;

class Compte extends Model
{
    use HasFactory;
    protected $primaryKey = 'id';
    public $incrementing = false; 
    protected $keyType = 'string'; 



     protected $fillable = [
        
        'numero_compte',
        'type_compte',
        'etat_compte',
        'solde',
        'motif_blocage',
        'client_id',
    ];


        protected $casts = [
        'type_compte' => TypeCompte::class,
        'etat_compte' => EtatCompte::class,
    ];



     protected static function booted()
    {
            static::creating(function ($compte) {
            if (empty($compte->id)) {
                $compte->id = (string) Str::uuid();
            }

            if (empty($compte->numero_compte)) {
                $compte->numero_compte = 'COMP-' . date('Ymd') . '-' . strtoupper(Str::random(8));
            }
        });
      
    }


    // protected static function boot()
    // {
    //     parent::boot();

    //     static::creating(function ($model) {
    //         if (empty($model->{$model->getKeyName()})) {
    //             $model->{$model->getKeyName()} = (string) Str::uuid();
    //         }
    //     });
    // }



    /**
     * Relation : un compte appartient à un client
     */
    public function client()
    {
        return $this->belongsTo(Client::class, 'client_id');
    }

    /**
     * Relation : un compte est géré par un admin (pas de relation directe, Admin peut accéder à tous les comptes)
     */
    // Pas de relation directe avec Admin, car Admin gère tous les comptes
}





