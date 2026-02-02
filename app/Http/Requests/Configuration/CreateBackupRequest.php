<?php
// app/Http/Requests/Configuration/CreateBackupRequest.php

namespace App\Http\Requests\Configuration;

use Illuminate\Foundation\Http\FormRequest;

class CreateBackupRequest extends FormRequest
{
    public function authorize(): bool
    {
        $device = $this->getDevice();
        return $device && $this->user()->can('backup', $device);
    }

    public function rules(): array
    {
        return [
            'device_id' => ['required', 'integer'],
            'device_type' => ['required', 'in:switch,router,firewall'],
            'notes' => ['nullable', 'string', 'max:500']
        ];
    }

    public function messages(): array
    {
        return [
            'device_type.in' => 'Type d\'appareil non valide'
        ];
    }

    protected function getDevice()
    {
        $deviceType = $this->input('device_type');
        $deviceId = $this->input('device_id');
        
        $models = [
            'switch' => \App\Models\SwitchModel::class,
            'router' => \App\Models\Router::class,
            'firewall' => \App\Models\Firewall::class
        ];
        
        if (!isset($models[$deviceType])) {
            return null;
        }
        
        return $models[$deviceType]::find($deviceId);
    }
}