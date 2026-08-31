<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
 public function up(): void {
  Schema::table('customers', function(Blueprint $t){
   if (!Schema::hasColumn('customers','password')) $t->string('password')->nullable()->after('phone');
   if (!Schema::hasColumn('customers','remember_token')) $t->rememberToken();
  });
  if (!Schema::hasTable('customer_addresses')) Schema::create('customer_addresses', function(Blueprint $t){
   $t->id(); $t->foreignId('customer_id')->constrained()->cascadeOnDelete(); $t->string('label',80)->default('Home');
   $t->string('first_name',100); $t->string('last_name',100)->nullable(); $t->string('phone',40)->nullable();
   $t->string('address_line_1'); $t->string('address_line_2')->nullable(); $t->string('city',120); $t->string('region',120)->nullable();
   $t->string('postal_code',30)->nullable(); $t->string('country_code',2)->default('PK'); $t->boolean('is_default')->default(false); $t->timestamps();
  });
 }
 public function down(): void { Schema::dropIfExists('customer_addresses'); }
};
