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
            $table->string('reference')->unique();
            $table->foreignId('base_currency_id')->constrained('currencies');
            $table->foreignId('quote_currency_id')->constrained('currencies');
            $table->decimal('base_amount', 36, 18);
            $table->decimal('quote_amount', 36, 18);
            $table->decimal('price', 36, 18);
            $table->decimal('fee', 36, 18);
            $table->decimal('rate', 36, 18);
            $table->foreignId('fee_currency_id')->constrained('currencies');
            $table->enum('type', ['buy', 'sell'])->index();
            $table->integer('status')->default(1)->index();
            $table->timestamp('executed_at')->index();
            $table->foreignId('credit_transaction_id')->constrained('wallet_transactions');
            $table->foreignId('debit_transaction_id')->constrained('wallet_transactions');
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
