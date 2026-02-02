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
        Schema::create('configuration_histories', function (Blueprint $table) {
            $table->id();

            $table->string('device_type');
            $table->unsignedBigInteger('device_id');

            $table->longText('configuration')->nullable();
            $table->string('configuration_file')->nullable();
            $table->integer('config_size')->nullable();
            $table->string('config_checksum')->nullable();

            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();

            $table->string('change_type');
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('restored_from')->nullable();

            $table->ipAddress('ip_address')->nullable();
            $table->text('change_summary')->nullable();
            $table->longText('pre_change_config')->nullable();
            $table->longText('post_change_config')->nullable();

            $table->timestamps();

            $table->index(['device_type', 'device_id']);
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('configuration_histories');
    }
};
