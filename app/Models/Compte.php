<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use App\Enums\TypeCompte;
use App\Enums\EtatCompte;
use App\Models\Scopes\CompteScope;

class Compte extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * Les scopes globaux à appliquer automatiquement
     */
    protected static function booted()
    {
        static::addGlobalScope(new CompteScope());

        static::creating(function ($compte) {
            if (empty($compte->id)) {
                $compte->id = (string) Str::uuid();
            }

            if (empty($compte->numero_compte)) {
                $compte->numero_compte = 'COMP-' . date('Ymd') . '-' . strtoupper(Str::random(8));
            }
        });
    }
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




   /**
    * Scope local pour rechercher un compte par numéro
    *
    * @param Builder $query
    * @param string $numero
    * @return Builder
    */
   public function scopeNumero(Builder $query, string $numero): Builder
   {
       return $query->where('numero_compte', $numero);
   }

   /**
    * Scope local pour rechercher les comptes d'un client via son téléphone
    *
    * @param Builder $query
    * @param string $telephone
    * @return Builder
    */
   public function scopeClient(Builder $query, string $telephone): Builder
   {
       return $query->whereHas('client', function ($q) use ($telephone) {
           $q->where('telephone', $telephone);
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





