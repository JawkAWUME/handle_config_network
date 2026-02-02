<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\HasEncryptedCredentials;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;

class SwitchModel extends Model
{
    use HasEncryptedCredentials, HasFactory, Notifiable;
    
    protected $table = 'switches';
    
    protected $fillable = [
            'name',
            'site_id',

            'brand',
            'model',
            'firmware_version',
            'serial_number',
            'asset_tag',

            'ip_nms',
            'ip_service',
            'vlan_nms',
            'vlan_service',

            'username',
            'password',

            'ports_total',
            'ports_used',

            'configuration',
            'last_backup',
            'status',
            'notes'
        ];


    protected $casts = [
        'last_backup' => 'datetime',
    ];

    public function site()
    {
        return $this->belongsTo(Site::class);
    }

    public function configurationHistories()
    {
        return $this->hasMany(ConfigurationHistory::class, 'device_id')
            ->where('device_type', 'switch');
    }
}