<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Rules\MinimumSolde;

class UpdateCompteRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'solde' => ['sometimes', 'numeric', new MinimumSolde()],
            'type_compte' => 'sometimes|in:cheque,epargne',
            'etat_compte' => 'sometimes|in:actif,inactif,bloque',
            'motif_blocage' => 'nullable|string|max:500',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'solde.numeric' => 'Le solde doit être un nombre.',
            'type_compte.in' => 'Le type de compte doit être soit cheque soit epargne.',
            'etat_compte.in' => 'L\'état du compte doit être actif, inactif ou bloque.',
            'motif_blocage.max' => 'Le motif de blocage ne peut pas dépasser 500 caractères.',
        ];
    }
}