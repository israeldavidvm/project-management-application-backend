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
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            
            $table->foreignId('user_creator_id')->constrained('users')->onDelete('cascade'); // Creador del proyecto
            
            $table->string('name');
            $table->text('description')->nullable();
            $table->float('progress_percentage')->default(0.0); // Progreso del proyecto
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};
