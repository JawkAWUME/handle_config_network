<?php
// database/migrations/2026_02_13_000002_create_backups_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('backups', function (Blueprint $table) {
            $table->id();

            // Relation polymorphique
            $table->unsignedBigInteger('backupable_id');
            $table->string('backupable_type');

            $table->string('filename');
            $table->string('path');
            $table->bigInteger('size'); // taille en octets
            $table->string('status')->default('pending'); // success, failed, pending
            $table->string('hash', 64)->nullable(); // sha1 ou md5
            
            $table->timestamp('executed_at')->nullable();
            
            // Créé par un utilisateur
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();

            $table->timestamps();

            // Index pour la relation polymorphique
            $table->index(['backupable_id', 'backupable_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('backups');
    }
};
