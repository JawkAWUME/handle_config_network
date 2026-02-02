<?php
// app/Http/Requests/Configuration/RestoreBackupRequest.php

namespace App\Http\Requests\Configuration;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\ConfigurationHistory;

class RestoreBackupRequest extends FormRequest
{
    public function authorize(): bool
    {
        $backup = ConfigurationHistory::find($this->route('backup'));
        return $backup && $this->user()->can('restore', $backup->device);
    }

    public function rules(): array
    {
        return [
            'reason' => ['nullable', 'string', 'max:500']
        ];
    }
}