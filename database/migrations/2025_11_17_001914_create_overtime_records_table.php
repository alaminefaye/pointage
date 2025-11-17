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
        Schema::create('overtime_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->onDelete('cascade');
            $table->date('date');
            $table->decimal('hours', 5, 2); // Nombre d'heures supplémentaires
            $table->enum('type', ['manual', 'auto'])->default('manual'); // manual = défini manuellement, auto = calculé automatiquement
            $table->foreignId('attendance_record_id')->nullable()->constrained()->onDelete('set null'); // Lien vers le pointage si auto
            $table->text('notes')->nullable(); // Notes optionnelles
            $table->timestamps();
            
            // Empêcher les doublons : un employé ne peut avoir qu'un seul enregistrement manuel par date
            $table->unique(['employee_id', 'date', 'type'], 'unique_employee_date_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('overtime_records');
    }
};
