<?php

namespace App\Http\Requests;

use App\Rules\TelephoneSenegalais;
use App\Rules\NciSenegalais;
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
            'type' => 'required|in:cheque,epargne',
            'soldeInitial' => [
                'required',
                'numeric',
                'min:10000',
            ],
            'devise' => 'required|string|in:FCFA',
            'client' => 'required|array',
            'client.id' => 'nullable|uuid|exists:clients,id',
            'client.titulaire' => 'required_if:client.id,null|string|min:2|max:255',
            'client.nci' => ['required_if:client.id,null', new NciSenegalais()],
            'client.email' => 'required_if:client.id,null|email',
            'client.telephone' => ['required_if:client.id,null', new TelephoneSenegalais()],
            'client.adresse' => 'required_if:client.id,null|string|min:5|max:500',
        ];
    }

    public function messages(): array
    {
        return [
            'type.required' => 'Le type de compte est obligatoire.',
            'type.in' => 'Le type de compte doit être soit "cheque" soit "epargne".',
            'soldeInitial.required' => 'Le solde initial est obligatoire.',
            'soldeInitial.numeric' => 'Le solde initial doit être un nombre.',
            'soldeInitial.min' => 'Le solde initial doit être d\'au moins 10 000 FCFA.',
            'devise.required' => 'La devise est obligatoire.',
            'devise.in' => 'La devise doit être FCFA.',
            'client.required' => 'Les informations du client sont obligatoires.',
            'client.array' => 'Les informations du client doivent être un objet.',
            'client.id.uuid' => 'L\'ID du client doit être un UUID valide.',
            'client.id.exists' => 'Le client sélectionné n\'existe pas.',
            'client.titulaire.required_if' => 'Le nom du titulaire est obligatoire pour un nouveau client.',
            'client.titulaire.string' => 'Le nom du titulaire doit être une chaîne de caractères.',
            'client.titulaire.min' => 'Le nom du titulaire doit contenir au moins 2 caractères.',
            'client.titulaire.max' => 'Le nom du titulaire ne peut pas dépasser 255 caractères.',
            'client.nci.required_if' => 'Le numéro de carte d\'identité est obligatoire pour un nouveau client.',
            'client.nci.unique' => 'Ce numéro de carte d\'identité est déjà utilisé.',
            'client.email.required_if' => 'L\'email est obligatoire pour un nouveau client.',
            'client.email.email' => 'L\'email doit être une adresse email valide.',
            'client.email.unique' => 'Cet email est déjà utilisé.',
            'client.telephone.required_if' => 'Le numéro de téléphone est obligatoire pour un nouveau client.',
            'client.telephone.unique' => 'Ce numéro de téléphone est déjà utilisé.',
            'client.adresse.required_if' => 'L\'adresse est obligatoire pour un nouveau client.',
            'client.adresse.string' => 'L\'adresse doit être une chaîne de caractères.',
            'client.adresse.min' => 'L\'adresse doit contenir au moins 5 caractères.',
            'client.adresse.max' => 'L\'adresse ne peut pas dépasser 500 caractères.',
        ];
    }

    public function attributes(): array
    {
        return [
            'type' => 'type de compte',
            'soldeInitial' => 'solde initial',
            'devise' => 'devise',
            'client' => 'client',
            'client.id' => 'ID du client',
            'client.titulaire' => 'nom du titulaire',
            'client.nci' => 'numéro de carte d\'identité',
            'client.email' => 'email',
            'client.telephone' => 'numéro de téléphone',
            'client.adresse' => 'adresse',
        ];
    }
}
