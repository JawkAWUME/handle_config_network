<?php
// app/Http/Requests/Export/ExportDataRequest.php

namespace App\Http\Requests\Export;

use Illuminate\Foundation\Http\FormRequest;

class ExportDataRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('export-data');
    }

    public function rules(): array
    {
        return [
            'type' => ['required', 'in:excel,pdf,csv,json'],
            'data_type' => ['required', 'in:switches,routers,firewalls,sites,all'],
            'site_id' => ['nullable', 'exists:sites,id'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date']
        ];
    }

    public function messages(): array
    {
        return [
            'type.in' => 'Format d\'export non supporté',
            'data_type.in' => 'Type de données non valide',
            'end_date.after_or_equal' => 'La date de fin doit être après la date de début'
        ];
    }
}