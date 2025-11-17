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
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->string('employee_code')->unique();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email')->unique();
            $table->string('phone')->nullable();
            $table->string('password');
            $table->foreignId('department_id')->constrained()->onDelete('cascade');
            $table->string('position'); // Poste
            $table->time('standard_start_time')->default('08:00:00');
            $table->time('standard_end_time')->default('17:00:00');
            $table->integer('standard_hours_per_day')->default(8);
            $table->json('rest_days')->nullable(); // Jours de repos [1,2,3] pour lundi, mardi, mercredi
            $table->boolean('is_active')->default(true);
            $table->rememberToken();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};
