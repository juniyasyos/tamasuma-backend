<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('units', function (Blueprint $table) {
            $table->dropUnique('units_slug_unique');
            $table->unique(['program_id', 'slug']);
            $table->unique(['program_id', 'order']);
        });
    }

    public function down(): void
    {
        Schema::table('units', function (Blueprint $table) {
            $table->dropUnique('units_program_id_slug_unique');
            $table->dropUnique('units_program_id_order_unique');
            $table->unique('slug');
        });
    }
};
