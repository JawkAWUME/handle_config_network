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
        Schema::create('switches', function (Blueprint $table) {
            $table->id();

            $table->foreignId('site_id')->constrained()->cascadeOnDelete();
            $table->string('name');

            // Infos matériel
            $table->string('brand')->nullable();
            $table->string('model')->nullable();
            $table->string('firmware_version')->nullable();
            $table->string('serial_number')->nullable();
            $table->string('asset_tag')->nullable();

            // Réseau
            $table->ipAddress('ip_nms')->nullable();
            $table->ipAddress('ip_service')->nullable();
            $table->integer('vlan_nms')->nullable();
            $table->integer('vlan_service')->nullable();

            // Accès
            $table->string('username')->nullable();
            $table->text('password')->nullable();

            // Ports
            $table->integer('ports_total')->nullable();
            $table->integer('ports_used')->nullable();

            // Config
            $table->longText('configuration')->nullable();
            $table->timestamp('last_backup')->nullable();

            // Métadonnées
            $table->boolean('status')->default(true);
            $table->text('notes')->nullable();

            $table->timestamps();
        });

    }
   
    public function down(): void
    {
        Schema::dropIfExists('switches');
    }
};
