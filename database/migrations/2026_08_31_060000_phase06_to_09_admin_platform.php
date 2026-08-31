<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
 public function up(): void {
  Schema::create('media_assets', function(Blueprint $t){$t->id();$t->string('name');$t->string('disk')->default('public');$t->string('path');$t->string('mime_type')->nullable();$t->unsignedBigInteger('size_bytes')->default(0);$t->string('alt_text')->nullable();$t->string('caption')->nullable();$t->unsignedBigInteger('uploaded_by')->nullable();$t->timestamps();});
  Schema::create('seo_redirects', function(Blueprint $t){$t->id();$t->string('from_path')->unique();$t->string('to_path');$t->unsignedSmallInteger('status_code')->default(301);$t->boolean('active')->default(true);$t->unsignedBigInteger('hits')->default(0);$t->timestamps();});
  Schema::create('store_settings', function(Blueprint $t){$t->id();$t->string('group')->default('general');$t->string('key')->unique();$t->longText('value')->nullable();$t->string('type')->default('string');$t->timestamps();});
  Schema::create('shipping_zones', function(Blueprint $t){$t->id();$t->string('name');$t->string('country_code',2)->default('PK');$t->string('regions')->nullable();$t->decimal('base_rate',12,2)->default(0);$t->decimal('free_shipping_over',12,2)->nullable();$t->boolean('active')->default(true);$t->timestamps();});
  Schema::create('payment_methods', function(Blueprint $t){$t->id();$t->string('code')->unique();$t->string('name');$t->boolean('enabled')->default(false);$t->boolean('test_mode')->default(true);$t->json('config')->nullable();$t->unsignedSmallInteger('sort_order')->default(0);$t->timestamps();});
  Schema::create('audit_logs', function(Blueprint $t){$t->id();$t->unsignedBigInteger('user_id')->nullable();$t->string('action');$t->string('entity_type')->nullable();$t->unsignedBigInteger('entity_id')->nullable();$t->string('ip_address',64)->nullable();$t->json('meta')->nullable();$t->timestamps();$t->index(['entity_type','entity_id']);});
  Schema::create('woocommerce_import_runs', function(Blueprint $t){$t->id();$t->string('status')->default('pending');$t->string('source_url')->nullable();$t->json('options')->nullable();$t->json('summary')->nullable();$t->text('last_error')->nullable();$t->timestamp('started_at')->nullable();$t->timestamp('finished_at')->nullable();$t->timestamps();});
  Schema::create('woocommerce_import_maps', function(Blueprint $t){$t->id();$t->foreignId('run_id')->constrained('woocommerce_import_runs')->cascadeOnDelete();$t->string('entity_type');$t->unsignedBigInteger('source_id');$t->unsignedBigInteger('local_id')->nullable();$t->string('status')->default('imported');$t->text('message')->nullable();$t->timestamps();$t->unique(['run_id','entity_type','source_id']);});
 }
 public function down(): void { foreach(['woocommerce_import_maps','woocommerce_import_runs','audit_logs','payment_methods','shipping_zones','store_settings','seo_redirects','media_assets'] as $table) Schema::dropIfExists($table); }
};