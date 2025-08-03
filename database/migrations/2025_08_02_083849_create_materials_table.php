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
        Schema::create('materials', function (Blueprint $table) {
            $table->id();
            $table->foreignId('unit_id')->constrained()->onDelete('cascade');
            $table->string('title');
            $table->string('slug')->unique();
            $table->enum('type', ['text', 'video', 'file', 'quiz']);
            $table->text('content')->nullable(); 
            $table->unsignedInteger('duration_minutes')->nullable();
            $table->unsignedInteger('order')->default(1);
            $table->boolean('is_mandatory')->default(true);
            $table->boolean('is_visible')->default(true);
            $table->timestamps();
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('materials');
    }
};
