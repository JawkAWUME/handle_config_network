<?php
// app/Http/Requests/Router/StoreRouterRequest.php

namespace App\Http\Requests\Router;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreRouterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', \App\Models\Router::class);
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:50', 'unique:routers'],
            'site_id' => ['required', 'exists:sites,id'],
            'management_ip' => ['required', 'ipv4', 'unique:routers,management_ip'],
            'username' => ['required', 'string', 'max:50'],
            'password' => ['required', 'string', 'min:8'],
            'status' => ['required', 'boolean']
        ];
    }
}