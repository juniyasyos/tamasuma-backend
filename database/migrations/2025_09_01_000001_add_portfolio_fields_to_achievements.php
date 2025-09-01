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
            if (! Schema::hasColumn('achievements', 'visibility')) {
                $table->string('visibility', 16)->default('private')->index(); // public|private|unlisted
            }
            if (! Schema::hasColumn('achievements', 'tags')) {
                $table->json('tags')->nullable();
            }
            if (! Schema::hasColumn('achievements', 'is_featured')) {
                $table->boolean('is_featured')->default(false)->index();
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('achievements')) {
            return;
        }

        Schema::table('achievements', function (Blueprint $table) {
            if (Schema::hasColumn('achievements', 'visibility')) {
                $table->dropColumn('visibility');
            }
            if (Schema::hasColumn('achievements', 'tags')) {
                $table->dropColumn('tags');
            }
            if (Schema::hasColumn('achievements', 'is_featured')) {
                $table->dropColumn('is_featured');
            }
        });
    }
};

