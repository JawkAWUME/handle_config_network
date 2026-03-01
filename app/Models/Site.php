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
        'code',                  // ← ajouté (champ modal "Code")
        'address',
        'city',
        'country',
        'postal_code',
        'phone',                 // ← correspond à contact_phone dans le modal
        'technical_contact',     // ← correspond à contact_name dans le modal
        'technical_email',       // ← correspond à contact_email dans le modal
        'description',
        'status',                // ← ajouté
        'capacity',
        'notes',
        'latitude',              // ← ajouté (utilisé dans SiteController::store)
        'longitude',             // ← ajouté (utilisé dans SiteController::store)
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