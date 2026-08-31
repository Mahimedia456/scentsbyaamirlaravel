<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            if (!Schema::hasColumn('customers', 'admin_archived_at')) {
                $table->timestamp('admin_archived_at')->nullable()->index();
            }
            if (!Schema::hasColumn('customers', 'admin_archived_by')) {
                $table->unsignedBigInteger('admin_archived_by')->nullable()->index();
            }
        });
    }

    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            if (Schema::hasColumn('customers', 'admin_archived_by')) {
                $table->dropColumn('admin_archived_by');
            }
            if (Schema::hasColumn('customers', 'admin_archived_at')) {
                $table->dropColumn('admin_archived_at');
            }
        });
    }
};
