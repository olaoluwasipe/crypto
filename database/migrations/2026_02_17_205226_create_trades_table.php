<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('trades', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users');
            $table->foreignId('base_currency_id')->constrained('currencies');
            $table->foreignId('quote_currency_id')->constrained('currencies');
            $table->decimal('base_amount', 10, 2);
            $table->decimal('quote_amount', 10, 2);
            $table->decimal('price', 10, 2);
            $table->decimal('fee', 10, 2);
            $table->foreignId('fee_currency_id')->constrained('currencies');
            $table->enum('type', ['buy', 'sell']);
            $table->integer('status')->default(1);
            $table->timestamp('executed_at');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trades');
    }
};
