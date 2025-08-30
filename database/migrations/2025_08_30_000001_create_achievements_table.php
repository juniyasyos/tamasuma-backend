<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('achievements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->string('category')->default('certificate'); // certificate, award, competition, training, other
            $table->string('issuer')->nullable();
            $table->date('achieved_at')->nullable();
            $table->string('proof_image')->nullable(); // stored path on public disk
            $table->string('url')->nullable();
            $table->text('description')->nullable();
            $table->boolean('is_featured')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('achievements');
    }
};
