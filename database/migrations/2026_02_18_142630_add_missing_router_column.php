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
        Schema::table('routers', function (Blueprint $table) {
            // Ajouter les champs manquants pour cohérence avec les vues
            $table->string('ip_nms', 45)->nullable()->after('model');
            $table->string('ip_service', 45)->nullable()->after('ip_nms');
            $table->string('enable_password')->nullable()->after('password');
            $table->integer('interfaces_count')->default(0)->after('notes');
            $table->integer('interfaces_up_count')->default(0)->after('interfaces_count');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('routers', function (Blueprint $table) {
            $table->dropColumn([
                'ip_nms',
                'ip_service',
                'enable_password',
                'interfaces_count',
                'interfaces_up_count',
                'configuration'
            ]);
        });
    }
};