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
        Schema::create('sites', function (Blueprint $table) {
            $table->id();

            $table->string('name');
            $table->string('address')->nullable();
            $table->string('city')->nullable();
            $table->string('country')->nullable();
            $table->string('postal_code')->nullable();

            $table->string('phone')->nullable();
            $table->string('technical_contact')->nullable();
            $table->string('technical_email')->nullable();

            $table->text('description')->nullable();

            // status en STRING (cohérent avec le métier)
            $table->enum('status', ['active', 'maintenance', 'planned'])->default('active');

            $table->integer('capacity')->nullable();
            $table->text('notes')->nullable();

            $table->timestamps();
        });


    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sites');
    }
};
