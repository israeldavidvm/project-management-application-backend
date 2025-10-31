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
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->onDelete('cascade'); 
            
            $table->foreignId('user_assignee_id')->nullable()->constrained('users')->onDelete('set null'); // Usuario asignado
            
            $table->string('title');
            $table->text('description')->nullable();
            $table->enum('status', ['pendiente', 'en progreso', 'completada'])->default('pendiente'); 
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};
