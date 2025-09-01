<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('achievements')) {
            return;
        }

        Schema::table('achievements', function (Blueprint $table) {
            if (! Schema::hasColumn('achievements', 'created_by_id')) {
                $table->foreignId('created_by_id')->nullable()->after('user_id')
                    ->constrained('users')->nullOnDelete();
            }
            if (! Schema::hasColumn('achievements', 'created_via')) {
                $table->string('created_via', 16)->default('self')->after('created_by_id')->index();
            }
            if (! Schema::hasColumn('achievements', 'source_type')) {
                $table->string('source_type')->nullable()->after('created_via')->index();
            }
            if (! Schema::hasColumn('achievements', 'source_id')) {
                $table->unsignedBigInteger('source_id')->nullable()->after('source_type')->index();
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('achievements')) {
            return;
        }

        Schema::table('achievements', function (Blueprint $table) {
            if (Schema::hasColumn('achievements', 'created_by_id')) {
                $table->dropConstrainedForeignId('created_by_id');
            }
            if (Schema::hasColumn('achievements', 'created_via')) {
                $table->dropColumn('created_via');
            }
            if (Schema::hasColumn('achievements', 'source_type')) {
                $table->dropColumn('source_type');
            }
            if (Schema::hasColumn('achievements', 'source_id')) {
                $table->dropColumn('source_id');
            }
        });
    }
};

