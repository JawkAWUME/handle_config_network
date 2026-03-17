<?php
<<<<<<< HEAD

=======
>>>>>>> 1cf0ed699ed853c71591f8d1c6535623739730c9
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
<<<<<<< HEAD
        'code',
=======
        'code',                  // ← ajouté (champ modal "Code")
>>>>>>> 1cf0ed699ed853c71591f8d1c6535623739730c9
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
<<<<<<< HEAD
        'latitude',
        'longitude',
=======
        'latitude',              // ← ajouté (utilisé dans SiteController::store)
        'longitude',             // ← ajouté (utilisé dans SiteController::store)

>>>>>>> 1cf0ed699ed853c71591f8d1c6535623739730c9
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