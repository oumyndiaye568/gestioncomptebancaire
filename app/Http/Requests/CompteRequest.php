<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Rules\UniqueEmail;
use App\Rules\UniqueTelephone;
use App\Rules\MinimumSolde;

class CompteRequest extends FormRequest
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

            'name' => 'required|string|max:255',
            'email' => ['required', 'email', new UniqueEmail()],
            'telephone' => ['required', new UniqueTelephone()],
            'solde' => ['required', 'numeric', new MinimumSolde()],
            'type_compte' => 'required|in:cheque,epargne',
            'etat_compte' => 'required|in:actif,inactif,bloque',
            'admin_id' => 'required|exists:users,id',
            'client_id'     => 'required|exists:clients,id',
           
        ];
    }
}
