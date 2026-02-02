<?php
// app/Http/Requests/SearchRequest.php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SearchRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Tous les utilisateurs authentifiés peuvent rechercher
    }

    public function rules(): array
    {
        return [
            'q' => ['nullable', 'string', 'max:255'],
            'site_id' => ['nullable', 'exists:sites,id'],
            'status' => ['nullable', 'string'],
            'type' => ['nullable', 'in:switch,router,firewall']
        ];
    }
}