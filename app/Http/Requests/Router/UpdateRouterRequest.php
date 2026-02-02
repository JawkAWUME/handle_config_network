<?php
// app/Http/Requests/Router/UpdateRouterRequest.php

namespace App\Http\Requests\Router;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateRouterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('router'));
    }

    public function rules(): array
    {
        $router = $this->route('router');
        
        return [
            'name' => ['required', 'string', 'max:50', Rule::unique('routers')->ignore($router)],
            'site_id' => ['required', 'exists:sites,id'],
            'management_ip' => ['required', 'ipv4', Rule::unique('routers', 'management_ip')->ignore($router)],
            'username' => ['required', 'string', 'max:50'],
            'password' => ['sometimes', 'string', 'min:8'],
            'status' => ['required', 'boolean']
        ];
    }
}