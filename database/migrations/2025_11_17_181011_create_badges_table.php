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
        Schema::create('badges', function (Blueprint $table) {
            $table->id();
            $table->string('badge_number')->unique(); // Numéro unique du badge
            $table->string('qr_code')->unique(); // Code QR unique pour le badge
            $table->foreignId('employee_id')->constrained()->onDelete('cascade'); // Lien avec l'employé
            $table->text('notes')->nullable(); // Notes optionnelles
            $table->boolean('is_active')->default(true); // Badge actif ou non
            $table->date('issued_at')->nullable(); // Date d'émission
            $table->date('expires_at')->nullable(); // Date d'expiration (optionnelle)
            $table->timestamps();
            
            $table->index('badge_number');
            $table->index('qr_code');
            $table->index('employee_id');
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('badges');
    }
};
