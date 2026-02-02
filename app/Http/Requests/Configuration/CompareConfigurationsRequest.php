<?php
// app/Http/Requests/Configuration/CompareConfigurationsRequest.php

namespace App\Http\Requests\Configuration;

use Illuminate\Foundation\Http\FormRequest;

class CompareConfigurationsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('viewAny', \App\Models\ConfigurationHistory::class);
    }

    public function rules(): array
    {
        return [
            'backup_id_1' => ['required', 'exists:configuration_histories,id'],
            'backup_id_2' => ['required', 'exists:configuration_histories,id']
        ];
    }

    public function messages(): array
    {
        return [
            'backup_id_1.exists' => 'Le premier backup n\'existe pas',
            'backup_id_2.exists' => 'Le deuxième backup n\'existe pas'
        ];
    }
}