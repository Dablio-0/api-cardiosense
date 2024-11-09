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
        Schema::create('user_bpms_histories', function (Blueprint $table) {
            $table->id();
            $table->integer('bpm_interval_average');
            $table->integer('bpm_interval_max');
            $table->integer('bpm_interval_min');
            $table->timestamps();
            
            // Foreigh Keys
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('family_id')->constrained('families')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_bpms_histories');
    }
};
