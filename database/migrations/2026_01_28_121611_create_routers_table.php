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
        Schema::create('routers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('site_id')->constrained()->cascadeOnDelete();

            $table->string('brand');
            $table->string('model');

            $table->json('interfaces')->nullable();
            $table->json('routing_protocols')->nullable();

            $table->ipAddress('management_ip')->nullable();
            $table->integer('vlan_nms')->nullable();
            $table->integer('vlan_service')->nullable();

            $table->string('username')->nullable();
            $table->text('password')->nullable();

            $table->longText('configuration')->nullable();
            $table->string('configuration_file')->nullable();

            $table->string('operating_system')->nullable();
            $table->string('serial_number')->nullable();
            $table->string('asset_tag')->nullable();

            $table->boolean('status')->default(true);
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
        Schema::dropIfExists('routers');
    }
};
