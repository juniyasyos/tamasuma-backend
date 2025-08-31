<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'mute_program_enrollment_notifications')) {
                $table->boolean('mute_program_enrollment_notifications')->default(false)
                    ->after('remember_token');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'mute_program_enrollment_notifications')) {
                $table->dropColumn('mute_program_enrollment_notifications');
            }
        });
    }
};

