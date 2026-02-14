<?php
// database/migrations/2026_02_13_000001_create_alerts_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('alerts', function (Blueprint $table) {
            $table->id();
            
            // Relation polymorphique
            $table->unsignedBigInteger('alertable_id');
            $table->string('alertable_type');

            $table->string('title');
            $table->text('message');
            
            // Sévérité et statut
            $table->string('severity')->default('info'); // info, warning, critical
            $table->string('status')->default('open');   // open, resolved, ignored

            $table->timestamp('triggered_at')->nullable();
            $table->timestamp('resolved_at')->nullable();

            // Créé par un utilisateur
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();

            $table->timestamps();

            // Index pour la relation polymorphique
            $table->index(['alertable_id', 'alertable_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('alerts');
    }
};
