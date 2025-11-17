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
        Schema::create('attendance_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->onDelete('cascade');
            $table->date('date');
            $table->time('check_in_time')->nullable();
            $table->time('check_out_time')->nullable();
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->boolean('is_in_zone')->default(false);
            $table->integer('total_minutes')->default(0); // Minutes travaillées
            $table->integer('overtime_minutes')->default(0); // Minutes supplémentaires
            $table->boolean('is_absent')->default(false);
            $table->boolean('is_late')->default(false);
            $table->integer('late_minutes')->default(0);
            $table->text('notes')->nullable();
            $table->string('qr_code_used')->nullable(); // QR code utilisé pour ce pointage
            $table->timestamps();
            
            $table->unique(['employee_id', 'date']);
            $table->index(['date', 'employee_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendance_records');
    }
};
