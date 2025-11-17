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
        Schema::create('qr_codes', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->timestamp('expires_at');
            $table->boolean('is_used')->default(false);
            $table->foreignId('used_by_employee_id')->nullable()->constrained('employees')->onDelete('set null');
            $table->timestamp('used_at')->nullable();
            $table->timestamps();
            
            $table->index('expires_at');
            $table->index('is_used');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('qr_codes');
    }
};
