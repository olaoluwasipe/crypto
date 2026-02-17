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
        Schema::create('wallet_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('wallet_id')->constrained('wallets');
            $table->string('type');
            $table->string('reference');
            $table->string('description');
            $table->json('metadata');
            $table->integer('status')->default(1);
            $table->decimal('amount', 10, 20);
            $table->string('idempotency_key')->unique();
            $table->decimal('prev_balance', 10, 2);
            $table->decimal('new_balance', 10, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wallet_transactions');
    }
};
