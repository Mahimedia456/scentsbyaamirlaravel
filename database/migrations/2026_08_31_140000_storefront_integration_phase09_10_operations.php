<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
 public function up(): void {
  if (!Schema::hasTable('contact_inquiries')) Schema::create('contact_inquiries', function(Blueprint $t){$t->id();$t->string('first_name',100);$t->string('last_name',100)->nullable();$t->string('email',190);$t->string('subject',80);$t->string('order_number',60)->nullable()->index();$t->text('message');$t->string('status',30)->default('new')->index();$t->text('admin_notes')->nullable();$t->timestamps();});
  if (!Schema::hasTable('newsletter_subscribers')) Schema::create('newsletter_subscribers', function(Blueprint $t){$t->id();$t->string('email',190)->unique();$t->string('status',30)->default('subscribed')->index();$t->string('source',60)->default('footer');$t->timestamp('subscribed_at')->nullable();$t->timestamp('unsubscribed_at')->nullable();$t->timestamps();});
 }
 public function down(): void { Schema::dropIfExists('newsletter_subscribers'); Schema::dropIfExists('contact_inquiries'); }
};
