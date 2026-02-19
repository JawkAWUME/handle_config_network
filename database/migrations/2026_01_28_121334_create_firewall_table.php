<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
            Schema::create('firewalls', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('site_id')->constrained()->cascadeOnDelete();

            $table->string('firewall_type')->nullable();
            $table->string('brand');
            $table->string('model');

            $table->ipAddress('ip_nms')->nullable();
            $table->ipAddress('ip_service')->nullable();
            $table->integer('vlan_nms')->nullable();
            $table->integer('vlan_service')->nullable();

            $table->string('username')->nullable();
            $table->text('password')->nullable();
            $table->text('enable_password')->nullable();

            $table->longText('configuration')->nullable();
            $table->string('configuration_file')->nullable();

            $table->json('security_policies')->nullable();
            $table->json('nat_rules')->nullable();
            $table->json('vpn_configuration')->nullable();
            $table->json('licenses')->nullable();

            $table->string('firmware_version')->nullable();
            $table->string('serial_number')->nullable();
            $table->string('asset_tag')->nullable();

            $table->boolean('status')->default(true);
            $table->boolean('high_availability')->default(false);
            $table->foreignId('ha_peer_id')->nullable()->references('id')->on('firewalls');

            $table->boolean('monitoring_enabled')->default(true);
            $table->timestamp('last_backup')->nullable();
            $table->text('notes')->nullable();

            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('firewalls');
    }
};
