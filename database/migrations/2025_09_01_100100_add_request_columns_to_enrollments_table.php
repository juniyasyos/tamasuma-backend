<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('enrollments', function (Blueprint $table) {
            $table->timestamp('requested_at')->nullable()->after('status');
            $table->timestamp('approved_at')->nullable()->after('requested_at');
            $table->timestamp('rejected_at')->nullable()->after('approved_at');
        });
    }

    public function down(): void
    {
        Schema::table('enrollments', function (Blueprint $table) {
            $table->dropColumn(['requested_at', 'approved_at', 'rejected_at']);
        });
    }
};

