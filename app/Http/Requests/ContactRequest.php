<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ContactRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Autoriser toutes les requêtes, même pour les utilisateurs non authentifiés
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
            'email' => 'required|email|max:255',
            'subject' => 'required|string|max:255',
            'message' => 'required|string|min:10|max:2000',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Le nom est obligatoire',
            'name.max' => 'Le nom ne doit pas dépasser 255 caractères',
            
            'email.required' => 'L\'adresse email est obligatoire',
            'email.email' => 'Veuillez entrer une adresse email valide',
            'email.max' => 'L\'adresse email ne doit pas dépasser 255 caractères',
            
            'subject.required' => 'L\'objet du message est obligatoire',
            'subject.max' => 'L\'objet ne doit pas dépasser 255 caractères',
            
            'message.required' => 'Le message est obligatoire',
            'message.min' => 'Le message doit contenir au moins 10 caractères',
            'message.max' => 'Le message ne doit pas dépasser 2000 caractères',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'name' => 'nom complet',
            'email' => 'adresse email',
            'subject' => 'objet du message',
            'message' => 'message',
        ];
    }
}