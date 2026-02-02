<?php
// app/Http/Requests/Firewall/UpdateFirewallRequest.php

namespace App\Http\Requests\Firewall;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\Firewall;

class UpdateFirewallRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('firewall'));
    }

    public function rules(): array
    {
        $firewall = $this->route('firewall');
        
        return [
            'name' => ['required', 'string', 'max:50', Rule::unique('firewalls')->ignore($firewall)],
            'site_id' => ['required', 'exists:sites,id'],
            'firewall_type' => ['required', Rule::in(array_keys(Firewall::getFirewallTypes()))],
            'ip_nms' => ['required', 'ipv4', Rule::unique('firewalls', 'ip_nms')->ignore($firewall)],
            'ip_service' => ['nullable', 'ipv4'],
            'username' => ['required', 'string', 'max:50'],
            'password' => ['sometimes', 'string', 'min:8'],
            'status' => ['required', 'boolean']
        ];
    }
}