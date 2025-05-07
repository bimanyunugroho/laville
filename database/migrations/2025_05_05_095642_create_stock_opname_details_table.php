<?php

use App\Enums\StatusStockOpnameDetailEnum;
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
        Schema::create('stock_opname_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stock_opname_id')->constrained()->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('unit_id')->constrained()->cascadeOnUpdate()->cascadeOnDelete();
            $table->integer('system_stock')->default(0); // 10 Botol
            $table->integer('system_stock_base')->default(0); // 500 Mililiter
            $table->integer('physical_stock')->default(0); // Kondisi Barang saat ini dalam  Botol
            $table->integer('physical_stock_base')->default(0); // Kondisi Barang saat ini dalam Mililiter
            $table->integer('difference_stock')->default(0); // Otomatis dalam Botol (Physical - System)
            $table->integer('difference_stock_base')->default(0); // Otomatis dalam Mililiter (Physical - System)
            $table->integer('price')->default(0); // Dalam Botolan
            $table->integer('subtotal')->default(0); // Dalam Botolan
            $table->enum('status', StatusStockOpnameDetailEnum::values())->default(StatusStockOpnameDetailEnum::MATCH->value);
            $table->string('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_opname_details');
    }
};
