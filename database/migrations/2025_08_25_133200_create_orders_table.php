<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->onDelete('cascade');
            $table->string('order_code')->unique();
            $table->string('overall_code')->nullable();
            $table->text('inquiry_mail')->nullable();
            $table->string('region', 100)->nullable();
            $table->date('date_received')->nullable();
            $table->string('sent_to_client', 50)->nullable();
            $table->text('notes_to_purchasing')->nullable();
            $table->text('notes_to_elias')->nullable();
            $table->string('ref')->unique();
            $table->string('priority', 50)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
