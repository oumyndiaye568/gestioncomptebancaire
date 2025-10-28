<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateCompteRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // L'admin peut créer des comptes
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'client_id' => 'required|exists:clients,id',
            'type_compte' => 'required|in:cheque,epargne',
            'solde' => [
                'required',
                'numeric',
                'min:0',
                function ($attribute, $value, $fail) {
                    if ($this->input('type_compte') === 'epargne' && $value < 10000) {
                        $fail('Le solde minimum pour un compte épargne est de 10 000 FCFA.');
                    }
                },
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'client_id.required' => 'Le client est obligatoire.',
            'client_id.exists' => 'Le client sélectionné n\'existe pas.',
            'type_compte.required' => 'Le type de compte est obligatoire.',
            'type_compte.in' => 'Le type de compte doit être soit "cheque" soit "epargne".',
            'solde.required' => 'Le solde initial est obligatoire.',
            'solde.numeric' => 'Le solde doit être un nombre.',
            'solde.min' => 'Le solde ne peut pas être négatif.',
        ];
    }

    public function attributes(): array
    {
        return [
            'client_id' => 'client',
            'type_compte' => 'type de compte',
            'solde' => 'solde initial',
        ];
    }
}
