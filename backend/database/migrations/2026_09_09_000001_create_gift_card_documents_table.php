<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('gift_card_documents', function (Blueprint $table) {
            $table->id();
            $table->string('shop');
            $table->string('shopify_gift_card_id');
            $table->text('gift_card_code');
            $table->decimal('amount', 12, 2);
            $table->string('currency', 3)->default('EUR');
            $table->date('expires_on')->nullable();
            $table->string('note')->nullable();
            $table->string('qr_url')->nullable();
            $table->longText('qr_code')->nullable();
            $table->string('shopify_file_id')->nullable();
            $table->string('shopify_image_url')->nullable();
            $table->string('shopify_image_alt')->nullable();
            $table->longText('template_html');
            $table->longText('template_css');
            $table->timestamps();

            $table->index(['shop', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gift_card_documents');
    }
};
