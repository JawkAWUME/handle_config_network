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
        Schema::table('firewalls', function (Blueprint $table) {
            // Ajouter les champs manquants pour cohérence avec les vues
            $table->integer('security_policies_count')->default(0)->after('notes');
            $table->integer('cpu')->default(0)->after('security_policies_count');
            $table->integer('memory')->default(0)->after('cpu');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('firewalls', function (Blueprint $table) {
            $table->dropColumn(['security_policies_count', 'cpu', 'memory', 'configuration']);
        });
    }
};