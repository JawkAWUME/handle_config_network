<?php
// app/Http/Requests/Site/UpdateSiteRequest.php

namespace App\Http\Requests\Site;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateSiteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('site'));
    }

    public function rules(): array
    {
        $site = $this->route('site');
        
        return [
            'name' => ['required', 'string', 'max:100', Rule::unique('sites')->ignore($site)],
            'address' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:500'],
            'technical_contact' => ['required', 'email', 'max:100'],
            'status' => ['required', 'in:active,inactive']
        ];
    }
}