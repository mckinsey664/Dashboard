\<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->onDelete('cascade');
            $table->string('part_number')->nullable();
            $table->string('manufacturer')->nullable();
            $table->decimal('quantity', 20, 4)->nullable();
            $table->string('uom', 50)->nullable();
            $table->decimal('target_price_usd', 20, 4)->nullable();
            $table->text('item_notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};
