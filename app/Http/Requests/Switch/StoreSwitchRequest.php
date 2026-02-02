<?php

namespace App\Http\Requests\Switch;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSwitchRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:100',
                Rule::unique('switches', 'name')->whereNull('deleted_at')
            ],
            'site_id' => 'required|exists:sites,id',
            'brand' => 'required|string|max:50',
            'model' => 'required|string|max:100',
            'serial_number' => 'nullable|string|max:100|unique:switches,serial_number',
            'asset_tag' => 'nullable|string|max:50|unique:switches,asset_tag',
            
            'ip_nms' => [
                'nullable',
                'ipv4',
                Rule::unique('switches', 'ip_nms')->whereNull('deleted_at')
            ],
            'ip_service' => [
                'nullable',
                'ipv4',
                Rule::unique('switches', 'ip_service')->whereNull('deleted_at')
            ],
            'vlan_nms' => 'nullable|integer|min:1|max:4094',
            'vlan_service' => 'nullable|integer|min:1|max:4094',
            
            'username' => 'nullable|string|max:100',
            'password' => 'nullable|string|max:255',
            
            'ports_total' => 'required|integer|min:1|max:4096',
            'ports_used' => 'nullable|integer|min:0|lte:ports_total',
            
            'firmware_version' => 'nullable|string|max:50',
            'configuration' => 'nullable|array',
            'last_backup' => 'nullable|date',
            'status' => 'nullable|in:active,maintenance,inactive',
            'notes' => 'nullable|string|max:1000',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Le nom du switch est requis',
            'name.unique' => 'Un switch avec ce nom existe déjà',
            'site_id.required' => 'Le site est requis',
            'ports_total.required' => 'Le nombre total de ports est requis',
            'ports_used.lte' => 'Les ports utilisés ne peuvent pas dépasser le total des ports',
            'ip_nms.unique' => 'Cette adresse IP NMS est déjà utilisée',
            'ip_service.unique' => 'Cette adresse IP Service est déjà utilisée',
            'serial_number.unique' => 'Ce numéro de série est déjà enregistré',
            'asset_tag.unique' => 'Cette étiquette d\'actif existe déjà',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            if ($this->has('ip_nms') && $this->has('ip_service') && 
                $this->ip_nms === $this->ip_service) {
                $validator->errors()->add('ip_service', 
                    'L\'adresse IP Service doit être différente de l\'IP NMS');
            }
        });
    }
}