<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
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
        'user_id',   
    ];


        protected $casts = [
        'type_compte' => TypeCompte::class,
        'etat_compte' => EtatCompte::class,
    ];



     protected static function booted()
    {
        static::creating(function ($compte) {
            if (empty($compte->numero_compte)) {
                // Exemple de numéro : COMP-20251023-ABCDEFGH
                $compte->numero_compte = 'COMP-' . date('Ymd') . '-' . strtoupper(Str::random(8));
            }
        });
    }


    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->{$model->getKeyName()})) {
                $model->{$model->getKeyName()} = (string) Str::uuid();
            }
        });
    }



    /**
     * Relation : un compte appartient à un client (user)
     */
    public function client()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Relation : un compte est géré par un admin (user)
     */
    public function admin()
    {
        return $this->belongsTo(User::class, 'admin_id');
    }
}





