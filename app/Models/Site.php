<?php

// app/Models/Site.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;

class Site extends Model
{

    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'address',
        'city',
        'country',
        'postal_code',
        'phone',
        'technical_contact',
        'technical_email',
        'description',
        'status',
        'capacity',
        'notes',
    ];


    public function switches(): HasMany
    {
        return $this->hasMany(SwitchModel::class);
    }

    public function routers(): HasMany
    {
        return $this->hasMany(Router::class);
    }

    public function firewalls(): HasMany
    {
        return $this->hasMany(Firewall::class);
    }
}