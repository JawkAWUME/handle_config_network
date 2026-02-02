<?php

// app/Traits/HasEncryptedCredentials.php
namespace App\Traits;

use Illuminate\Support\Facades\Crypt;

trait HasEncryptedCredentials
{
    public function setPasswordAttribute($value)
    {
        $this->attributes['password'] = Crypt::encryptString($value);
    }

    public function getPasswordAttribute($value)
    {
        try {
            return Crypt::decryptString($value);
        } catch (\Exception $e) {
            return $value;
        }
    }
}