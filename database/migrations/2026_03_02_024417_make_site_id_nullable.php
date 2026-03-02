<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // switches
        Schema::table('switches', function (Blueprint $table) {
            $table->dropForeign(['site_id']);
            $table->foreignId('site_id')->nullable()->change();
            $table->foreign('site_id')->references('id')->on('sites')->nullOnDelete();
        });

        // routers
        Schema::table('routers', function (Blueprint $table) {
            $table->dropForeign(['site_id']);
            $table->foreignId('site_id')->nullable()->change();
            $table->foreign('site_id')->references('id')->on('sites')->nullOnDelete();
        });

        // firewalls
        Schema::table('firewalls', function (Blueprint $table) {
            $table->dropForeign(['site_id']);
            $table->foreignId('site_id')->nullable()->change();
            $table->foreign('site_id')->references('id')->on('sites')->nullOnDelete();
        });
    }

    public function down(): void
    {
        // Attention : rollback impossible si des lignes ont site_id = null
        Schema::table('switches', function (Blueprint $table) {
            $table->dropForeign(['site_id']);
            $table->foreignId('site_id')->nullable(false)->change();
            $table->foreign('site_id')->references('id')->on('sites')->cascadeOnDelete();
        });

        Schema::table('routers', function (Blueprint $table) {
            $table->dropForeign(['site_id']);
            $table->foreignId('site_id')->nullable(false)->change();
            $table->foreign('site_id')->references('id')->on('sites')->cascadeOnDelete();
        });

        Schema::table('firewalls', function (Blueprint $table) {
            $table->dropForeign(['site_id']);
            $table->foreignId('site_id')->nullable(false)->change();
            $table->foreign('site_id')->references('id')->on('sites')->cascadeOnDelete();
        });
    }
};