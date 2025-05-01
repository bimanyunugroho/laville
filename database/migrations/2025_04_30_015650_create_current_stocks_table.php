<?php

use App\Enums\StatusRunningCurrentStockEnum;
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
        Schema::create('current_stocks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('unit_id')->constrained()->cascadeOnUpdate()->cascadeOnDelete();
            $table->bigInteger('quantity')->default(0); // Berdasarkan conversi mililiter
            $table->bigInteger('base_quantity')->default(0); // Berdsasarkan conversi 1 botol base unit
            $table->string('slug');
            $table->unsignedTinyInteger('month');
            $table->year('year');
            $table->enum('status_running', StatusRunningCurrentStockEnum::values())->default(StatusRunningCurrentStockEnum::SEDANG_PROSES->value);
            $table->timestamps();
            $table->softDeletes();
            $table->index(['product_id', 'unit_id', 'month', 'year', 'status_running']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('current_stocks');
    }
};
