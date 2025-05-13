<?php

use App\Enums\StatusPayment;
use App\Enums\TypePayment;
use App\Enums\TypeSourceTransaction;
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
        Schema::create('transaction_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('transaction_id')->constrained()->cascadeOnDelete();
            $table->timestamp('payment_date')->nullable();
            $table->enum('payment_method', TypePayment::values())->default(TypePayment::CASH->value);
            $table->string('payment_reference')->nullable(); // No kartu, ID transaksi e-wallet, dll
            $table->bigInteger('amount')->default(0);
            $table->enum('status', StatusPayment::values());
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transaction_payments');
    }
};
