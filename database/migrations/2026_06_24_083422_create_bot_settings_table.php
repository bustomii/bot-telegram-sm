<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bot_settings', function (Blueprint $table) {
            $table->id();
            $table->string('telegram_bot_token')->nullable();
            $table->string('telegram_webhook_secret')->nullable();
            $table->string('admin_group_chat_id')->nullable();
            $table->string('community_link')->nullable();
            $table->string('hfm_referral_link')->nullable();
            $table->string('hfm_api_url')->nullable();
            $table->string('hfm_api_key')->nullable();
            $table->string('hfm_ib_id')->nullable();
            $table->decimal('min_deposit', 10, 2)->default(20);
            $table->string('pdf_registration')->nullable();
            $table->string('pdf_ib_step1')->nullable();
            $table->string('pdf_ib_step2')->nullable();
            $table->text('welcome_message')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bot_settings');
    }
};
