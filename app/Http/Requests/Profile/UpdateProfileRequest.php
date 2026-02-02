<?php
// app/Http/Requests/Profile/UpdateProfileRequest.php

namespace App\Http\Requests\Profile;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UpdateProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // L'utilisateur peut toujours mettre à jour son propre profil
    }

    public function rules(): array
    {
        $user = $this->user();
        
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user)],
            'current_password' => ['required_with:password', 'current_password'],
            'password' => ['sometimes', 'confirmed', Password::defaults()]
        ];
    }

    public function messages(): array
    {
        return [
            'current_password.required_with' => 'Le mot de passe actuel est requis pour changer le mot de passe',
            'current_password.current_password' => 'Le mot de passe actuel est incorrect'
        ];
    }
}