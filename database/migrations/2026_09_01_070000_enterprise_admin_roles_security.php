<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Production is MySQL/MariaDB on StackCP. Expand the legacy enum without
        // destroying existing values.
        if (Schema::hasTable('users')) {
            try {
                DB::statement("ALTER TABLE users MODIFY role ENUM('super_admin','admin','manager','catalog_manager','order_manager','content_manager','staff') NOT NULL DEFAULT 'staff'");
            } catch (Throwable $e) {
                // Non-MySQL/local engines can keep the existing column; role
                // authorization still remains application-level.
                report($e);
            }

            Schema::table('users', function (Blueprint $table) {
                if (!Schema::hasColumn('users','password_changed_at')) {
                    $table->timestamp('password_changed_at')->nullable()->index();
                }
                if (!Schema::hasColumn('users','must_change_password')) {
                    $table->boolean('must_change_password')->default(false)->index();
                }
            });
        }
    }

    public function down(): void
    {
        // Role values are intentionally retained to avoid data loss on rollback.
        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                if (Schema::hasColumn('users','must_change_password')) $table->dropColumn('must_change_password');
                if (Schema::hasColumn('users','password_changed_at')) $table->dropColumn('password_changed_at');
            });
        }
    }
};
