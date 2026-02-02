<?php

namespace App\Http\Requests\Switch;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\SwitchModel;

class UpdateSwitchRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $switchId = $this->route('switch') ?? $this->route('id');
        
        return [
            'name' => [
                'sometimes',
                'string',
                'max:100',
                Rule::unique('switches', 'name')
                    ->ignore($switchId)
                    ->whereNull('deleted_at')
            ],
            'site_id' => 'sometimes|exists:sites,id',
            'brand' => 'sometimes|string|max:50',
            'model' => 'sometimes|string|max:100',
            'serial_number' => [
                'nullable',
                'string',
                'max:100',
                Rule::unique('switches', 'serial_number')
                    ->ignore($switchId)
                    ->whereNull('deleted_at')
            ],
            'asset_tag' => [
                'nullable',
                'string',
                'max:50',
                Rule::unique('switches', 'asset_tag')
                    ->ignore($switchId)
                    ->whereNull('deleted_at')
            ],
            
            'ip_nms' => [
                'nullable',
                'ipv4',
                Rule::unique('switches', 'ip_nms')
                    ->ignore($switchId)
                    ->whereNull('deleted_at')
            ],
            'ip_service' => [
                'nullable',
                'ipv4',
                Rule::unique('switches', 'ip_service')
                    ->ignore($switchId)
                    ->whereNull('deleted_at')
            ],
            'vlan_nms' => 'nullable|integer|min:1|max:4094',
            'vlan_service' => 'nullable|integer|min:1|max:4094',
            
            'username' => 'nullable|string|max:100',
            'password' => 'nullable|string|max:255',
            
            'ports_total' => 'sometimes|integer|min:1|max:4096',
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
            'name.unique' => 'Un switch avec ce nom existe déjà',
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
            // Validation conditionnelle pour les ports utilisés
            if ($this->has('ports_total') && $this->filled('ports_used')) {
                if ($this->ports_used > $this->ports_total) {
                    $validator->errors()->add('ports_used', 
                        'Les ports utilisés ne peuvent pas dépasser le total des ports');
                }
            }
            
            // Validation des IPs uniques
            if ($this->filled('ip_nms') && $this->filled('ip_service') && 
                $this->ip_nms === $this->ip_service) {
                $validator->errors()->add('ip_service', 
                    'L\'adresse IP Service doit être différente de l\'IP NMS');
            }
        });
    }
}