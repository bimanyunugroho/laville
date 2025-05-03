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
        Schema::create('good_receipt_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('good_receipt_id')->constrained()->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('purchase_order_detail_id')->constrained()->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('unit_id')->constrained()->cascadeOnUpdate()->cascadeOnDelete();
            $table->integer('quantity')->default(0);
            $table->bigInteger('base_quantity')->default(0); // Quantity in base unit
            $table->bigInteger('price')->default(0);
            $table->bigInteger('subtotal')->default(0);
            $table->bigInteger('received_quantity')->default(0); // Received quantity in base unit
            $table->bigInteger('received_base_quantity')->default(0);
            $table->timestamps();
            $table->softDeletes();
            $table->index(['good_receipt_id', 'purchase_order_detail_id', 'product_id', 'unit_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('good_receipt_details');
    }
};
