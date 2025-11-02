<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class TelephoneSenegalais implements ValidationRule
{
    /**
     * Run the validation rule.
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // Vérifier le format +221 suivi de 9 chiffres
        if (!preg_match('/^\+221\d{9}$/', $value)) {
            $fail('Le numéro de téléphone doit être au format sénégalais (+221XXXXXXXXX).');
            return;
        }

        // Extraire les 2 premiers chiffres après +221 pour vérifier les opérateurs
        $operateur = substr($value, 4, 2);

        // Liste des préfixes valides pour le Sénégal
        $prefixesValides = [
            '77', '78', '76', '70', '75', '33', '32', '31', '93', '95', '94', '96', '97', '98'
        ];

        if (!in_array($operateur, $prefixesValides)) {
            $fail('Le numéro de téléphone doit commencer par un préfixe d\'opérateur sénégalais valide (+22177..., +22178..., etc.).');
        }
    }
}