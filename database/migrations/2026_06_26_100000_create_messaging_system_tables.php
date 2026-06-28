<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('messaging_providers', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->string('name');
            $table->string('icon')->nullable();
            $table->string('color')->default('#6366f1');
            $table->boolean('is_enabled')->default(false);
            $table->json('supported_account_types')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->unsignedBigInteger('telegram_id')->nullable()->unique()->after('id');
            $table->string('telegram_username')->nullable()->after('telegram_id');
            $table->string('telegram_photo_url')->nullable()->after('telegram_username');
            $table->string('auth_provider')->default('email')->after('telegram_photo_url');
        });

        Schema::create('messaging_accounts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('messaging_provider_id')->constrained()->cascadeOnDelete();
            $table->string('account_type', 32);
            $table->string('label');
            $table->string('external_id')->nullable();
            $table->string('username')->nullable();
            $table->string('display_name')->nullable();
            $table->text('credentials');
            $table->string('status', 32)->default('disconnected');
            $table->string('webhook_secret', 64)->nullable();
            $table->json('metadata')->nullable();
            $table->text('status_message')->nullable();
            $table->timestamp('connected_at')->nullable();
            $table->timestamp('last_activity_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'messaging_provider_id']);
            $table->index(['messaging_provider_id', 'status']);
        });

        Schema::create('auto_reply_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('messaging_account_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('trigger_type', 32);
            $table->string('trigger_pattern')->nullable();
            $table->text('response_message');
            $table->boolean('is_active')->default(true);
            $table->boolean('match_case_sensitive')->default(false);
            $table->unsignedSmallInteger('priority')->default(0);
            $table->timestamps();

            $table->index(['messaging_account_id', 'is_active', 'priority']);
        });

        Schema::create('message_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('messaging_account_id')->constrained()->cascadeOnDelete();
            $table->foreignId('auto_reply_rule_id')->nullable()->constrained()->nullOnDelete();
            $table->string('direction', 16);
            $table->string('external_chat_id')->nullable();
            $table->string('external_message_id')->nullable();
            $table->text('content')->nullable();
            $table->json('payload')->nullable();
            $table->timestamps();

            $table->index(['messaging_account_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('message_logs');
        Schema::dropIfExists('auto_reply_rules');
        Schema::dropIfExists('messaging_accounts');
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['telegram_id', 'telegram_username', 'telegram_photo_url', 'auth_provider']);
        });
        Schema::dropIfExists('messaging_providers');
    }
};
