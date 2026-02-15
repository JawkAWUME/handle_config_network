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
        // Ajouter user_id aux routers
        Schema::table('routers', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->after('site_id')->constrained()->nullOnDelete();
            $table->index('user_id');
        });

        // Ajouter user_id aux firewalls
        Schema::table('firewalls', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->after('site_id')->constrained()->nullOnDelete();
            $table->index('user_id');
        });

        // Ajouter user_id aux switches
        Schema::table('switches', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->after('site_id')->constrained()->nullOnDelete();
            $table->index('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('routers', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn('user_id');
        });

        Schema::table('firewalls', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn('user_id');
        });

        Schema::table('switches', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn('user_id');
        });
    }
};