<?php

use App\Enums\StatusTransaction;
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
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_number')->unique();
            $table->string('slug');
            $table->foreignId('customer_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamp('transaction_date');
            $table->foreignId('user_id')->constrained(); // Kasir/Admin yang melakukan transaksi
            $table->bigInteger('total');
            $table->bigInteger('discount')->default(0);
            $table->bigInteger('subtotal'); // total - diskon
            $table->bigInteger('tax')->default(0); // subtotal * 10% PPN
            $table->bigInteger('total_amount'); // Total yang harus dibayarkan
            $table->bigInteger('paid_amount'); // Total Bayar di Customer
            $table->bigInteger('change_amount')->default(0); // Apakah ada kembalian (Paid Amount - Total Amount)
            $table->enum('status', StatusTransaction::values())->default(StatusTransaction::PENDING->value);
            $table->enum('source_transaction', TypeSourceTransaction::values())->default(TypeSourceTransaction::OFFLINE->value);
            $table->text('notes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
            $table->index(['invoice_number']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
