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
        Schema::table('attendance_settings', function (Blueprint $table) {
            $table->foreignId('site_id')->nullable()->after('id')->constrained()->onDelete('cascade');
            $table->dropUnique(['setting_key']);
            $table->unique(['site_id', 'setting_key']);
            $table->index('site_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attendance_settings', function (Blueprint $table) {
            $table->dropForeign(['site_id']);
            $table->dropIndex(['site_id']);
            $table->dropUnique(['site_id', 'setting_key']);
            $table->unique(['setting_key']);
            $table->dropColumn('site_id');
        });
    }
};
