<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leads', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('telegram_id')->unique();
            $table->string('telegram_username')->nullable();
            $table->string('name')->nullable();
            $table->string('join_purpose')->nullable();
            $table->string('trading_experience')->nullable();
            $table->boolean('has_hfm_account')->nullable();
            $table->string('full_name')->nullable();
            $table->string('wallet_id')->nullable();
            $table->string('mt5_id')->nullable();
            $table->string('hfm_account_name')->nullable();
            $table->string('hfm_email')->nullable();
            $table->string('hfm_phone')->nullable();
            $table->string('hfm_ib_status')->nullable();
            $table->decimal('hfm_equity', 12, 2)->nullable();
            $table->decimal('hfm_deposit', 12, 2)->nullable();
            $table->timestamp('hfm_registered_at')->nullable();
            $table->string('status')->default('LEAD');
            $table->string('previous_status')->nullable();
            $table->string('conversation_step')->nullable();
            $table->boolean('bot_paused')->default(false);
            $table->unsignedBigInteger('assigned_admin_id')->nullable();
            $table->text('admin_notes')->nullable();
            $table->text('reject_reason')->nullable();
            $table->timestamp('last_activity_at')->nullable();
            $table->timestamp('follow_up_30m_at')->nullable();
            $table->timestamp('follow_up_24h_at')->nullable();
            $table->timestamps();

            $table->foreign('assigned_admin_id')->references('id')->on('users')->nullOnDelete();
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leads');
    }
};
