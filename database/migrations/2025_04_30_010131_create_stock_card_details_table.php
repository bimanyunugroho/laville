<?php

use App\Enums\MovementTypeStockCardEnum;
use App\Enums\ReferencesStockCardEnum;
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
        Schema::create('stock_card_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stock_card_id')->constrained()->cascadeOnUpdate()->cascadeOnDelete();
            $table->unsignedBigInteger('reference_id');
            $table->string('reference_type');
            $table->enum('reference_status', ReferencesStockCardEnum::values());
            $table->foreignId('unit_id')->constrained()->cascadeOnUpdate()->cascadeOnDelete();
            $table->timestamp('transaction_date');
            $table->enum('movement_type', MovementTypeStockCardEnum::values());
            $table->bigInteger('quantity');
            $table->bigInteger('base_quantity');
            $table->bigInteger('balance_quantity');
            $table->bigInteger('balance_base_quantity');
            $table->string('notes');
            $table->timestamps();
            $table->softDeletes();
            $table->index(['stock_card_id', 'reference_id', 'reference_type', 'unit_id', 'transaction_date', 'reference_type', 'movement_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_card_details');
    }
};
