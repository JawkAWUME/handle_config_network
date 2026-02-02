<?php
// app/Http/Requests/Site/StoreSiteRequest.php

namespace App\Http\Requests\Site;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSiteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', \App\Models\Site::class);
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100', 'unique:sites'],
            'address' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:500'],
            'technical_contact' => ['required', 'email', 'max:100'],
            'status' => ['required', 'in:active,inactive']
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Le nom du site est obligatoire',
            'name.unique' => 'Ce nom de site existe déjà',
            'address.required' => 'L\'adresse du site est obligatoire',
            'technical_contact.required' => 'Le contact technique est obligatoire'
        ];
    }
}