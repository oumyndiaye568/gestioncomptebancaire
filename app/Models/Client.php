<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;
use Illuminate\Support\Str;

class Client extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    use \Illuminate\Database\Eloquent\SoftDeletes;


     /**
      * On désactive l’auto-incrémentation de la clé primaire
      */
     public $incrementing = false;

     /**
      * On précise que la clé primaire est de type string (UUID)
      */
     protected $keyType = 'string';

     /**
      * On génère automatiquement un UUID avant la création du client
      */
     protected static function boot()
     {
         parent::boot();

         static::creating(function ($model) {
             if (empty($model->{$model->getKeyName()})) {
                 $model->{$model->getKeyName()} = (string) Str::uuid();
             }
         });
     }

      protected $fillable = [
         'nom_complet',
         'email',
         'telephone',
         'adresse',
         'password',
         'nci',
         'code_verification',
     ];

     /**
      * The attributes that should be hidden for serialization.
      *
      * @var array<int, string>
      */
     protected $hidden = [
         'password',
         'remember_token',
         'code_verification',
     ];

     /**
      * The attributes that should be cast.
      *
      * @var array<string, string>
      */
     protected $casts = [
         'email_verified_at' => 'datetime',
         'password' => 'hashed',
     ];
         // Un client possède plusieurs comptes
      public function comptes()
     {
         return $this->hasMany(Compte::class, 'client_id');
     }

     /**
      * Générer un mot de passe temporaire aléatoire
      */
     public static function generateTemporaryPassword(): string
     {
         return Str::random(12);
     }

     /**
      * Générer un code de vérification SMS (6 chiffres)
      */
     public static function generateVerificationCode(): string
     {
         return str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
     }


}
