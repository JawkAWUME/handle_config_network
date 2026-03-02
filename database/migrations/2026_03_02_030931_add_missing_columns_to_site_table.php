<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sites', function (Blueprint $table) {
            // Vérifier et ajouter uniquement les colonnes manquantes
            if (!Schema::hasColumn('sites', 'code')) {
                $table->string('code', 50)->nullable()->unique()->after('name');
            }
            if (!Schema::hasColumn('sites', 'city')) {
                $table->string('city', 255)->nullable()->after('address');
            }
            if (!Schema::hasColumn('sites', 'country')) {
                $table->string('country', 255)->nullable()->after('city');
            }
            if (!Schema::hasColumn('sites', 'postal_code')) {
                $table->string('postal_code', 20)->nullable()->after('country');
            }
            if (!Schema::hasColumn('sites', 'latitude')) {
                $table->decimal('latitude', 10, 7)->nullable();
            }
            if (!Schema::hasColumn('sites', 'longitude')) {
                $table->decimal('longitude', 10, 7)->nullable();
            }
            if (!Schema::hasColumn('sites', 'technical_contact')) {
                $table->string('technical_contact', 255)->nullable();
            }
            if (!Schema::hasColumn('sites', 'technical_email')) {
                $table->string('technical_email', 255)->nullable();
            }
            if (!Schema::hasColumn('sites', 'phone')) {
                $table->string('phone', 50)->nullable();
            }
            if (!Schema::hasColumn('sites', 'description')) {
                $table->text('description')->nullable();
            }
            if (!Schema::hasColumn('sites', 'notes')) {
                $table->text('notes')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('sites', function (Blueprint $table) {
            $table->dropColumn(array_filter([
                Schema::hasColumn('sites', 'code')              ? 'code'              : null,
                Schema::hasColumn('sites', 'city')              ? 'city'              : null,
                Schema::hasColumn('sites', 'country')           ? 'country'           : null,
                Schema::hasColumn('sites', 'postal_code')       ? 'postal_code'       : null,
                Schema::hasColumn('sites', 'latitude')          ? 'latitude'          : null,
                Schema::hasColumn('sites', 'longitude')         ? 'longitude'         : null,
                Schema::hasColumn('sites', 'technical_contact') ? 'technical_contact' : null,
                Schema::hasColumn('sites', 'technical_email')   ? 'technical_email'   : null,
                Schema::hasColumn('sites', 'phone')             ? 'phone'             : null,
                Schema::hasColumn('sites', 'description')       ? 'description'       : null,
                Schema::hasColumn('sites', 'notes')             ? 'notes'             : null,
            ]));
        });
    }
};