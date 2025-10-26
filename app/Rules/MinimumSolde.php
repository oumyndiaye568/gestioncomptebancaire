<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class MinimumSolde implements ValidationRule
{

     protected int $minimum;

    public function __construct(int $minimum = 10000)
    {
        $this->minimum = $minimum;
    }


    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
         if ($value < $this->minimum) {
            $fail("Le solde initial doit être au moins de {$this->minimum}.");
        }
    }
}
