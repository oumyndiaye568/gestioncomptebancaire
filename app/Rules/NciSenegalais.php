<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class NciSenegalais implements ValidationRule
{
    /**
     * Run the validation rule.
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // Vérifier que c'est exactement 13 chiffres
        if (!preg_match('/^\d{13}$/', $value)) {
            $fail('Le numéro de carte d\'identité doit contenir exactement 13 chiffres.');
            return;
        }

        // Validation basique du format NCI sénégalais
        // Le NCI sénégalais commence généralement par l'année de naissance (2 chiffres)
        // suivi du numéro de série (7 chiffres) et se termine par un chiffre de contrôle
        $annee = (int) substr($value, 0, 2);
        $mois = (int) substr($value, 2, 2);
        $jour = (int) substr($value, 4, 2);

        // Vérifier que l'année est plausible (entre 1900 et l'année actuelle)
        $anneeComplete = $annee < 50 ? 2000 + $annee : 1900 + $annee;
        $anneeActuelle = (int) date('Y');

        if ($anneeComplete < 1900 || $anneeComplete > $anneeActuelle) {
            $fail('L\'année de naissance dans le NCI n\'est pas valide.');
            return;
        }

        // Vérifier que le mois est valide (01-12)
        if ($mois < 1 || $mois > 12) {
            $fail('Le mois dans le NCI n\'est pas valide.');
            return;
        }

        // Vérifier que le jour est valide selon le mois
        $joursParMois = [31, ($anneeComplete % 4 == 0 && ($anneeComplete % 100 != 0 || $anneeComplete % 400 == 0)) ? 29 : 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31];

        if ($jour < 1 || $jour > $joursParMois[$mois - 1]) {
            $fail('Le jour dans le NCI n\'est pas valide pour le mois donné.');
            return;
        }

        // Les 7 derniers chiffres doivent être numériques (numéro de série)
        $numeroSerie = substr($value, 5, 7);
        if (!is_numeric($numeroSerie)) {
            $fail('Le numéro de série du NCI n\'est pas valide.');
        }
    }
}