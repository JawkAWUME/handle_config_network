<?php
// database/factories/BackupFactory.php

namespace Database\Factories;

use App\Models\Backup;
use App\Models\User;
use App\Models\SwitchModel;
use App\Models\Router;
use App\Models\Firewall;
use Illuminate\Database\Eloquent\Factories\Factory;

class BackupFactory extends Factory
{
    protected $model = Backup::class;

    public function definition()
    {
        $backupableClasses = [
            SwitchModel::class,
            Router::class,
            Firewall::class,
        ];

        $backupableType = $this->faker->randomElement($backupableClasses);

        return [
            'backupable_id' => $backupableType::factory(),
            'backupable_type' => $backupableType,
            'filename' => 'backup_' . $this->faker->unique()->numerify('###') . '.zip',
            'path' => '/backups/' . $this->faker->unique()->numerify('backup_###') . '.zip',
            'size' => $this->faker->numberBetween(1024, 10*1024*1024), // 1 KB -> 10 MB
            'status' => $this->faker->randomElement([
                Backup::STATUS_SUCCESS,
                Backup::STATUS_FAILED,
                Backup::STATUS_PENDING
            ]),
            'hash' => $this->faker->sha1(),
            'executed_at' => $this->faker->dateTimeBetween('-2 months', 'now'),
            'created_by' => User::factory(),
        ];
    }

    /**
     * Scope pour backups réussis
     */
    public function success()
    {
        return $this->state([
            'status' => Backup::STATUS_SUCCESS,
        ]);
    }

    /**
     * Scope pour backups échoués
     */
    public function failed()
    {
        return $this->state([
            'status' => Backup::STATUS_FAILED,
        ]);
    }
}
