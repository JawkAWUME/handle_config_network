<?php
// app/Http/Requests/Firewall/StoreFirewallRequest.php

namespace App\Http\Requests\Firewall;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\Firewall;

class StoreFirewallRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', Firewall::class);
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:50', 'unique:firewalls'],
            'site_id' => ['required', 'exists:sites,id'],
            'firewall_type' => ['required', Rule::in(array_keys(Firewall::getFirewallTypes()))],
            'ip_nms' => ['required', 'ipv4', 'unique:firewalls,ip_nms'],
            'ip_service' => ['nullable', 'ipv4'],
            'username' => ['required', 'string', 'max:50'],
            'password' => ['required', 'string', 'min:8'],
            'status' => ['required', 'boolean']
        ];
    }

    public function messages(): array
    {
        return [
            'firewall_type.in' => 'Type de firewall non valide',
            'ip_nms.unique' => 'Cette IP NMS est déjà utilisée'
        ];
    }
}