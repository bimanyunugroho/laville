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
        Schema::create('stock_cards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnUpdate()->cascadeOnDelete();
            $table->bigInteger('beginning_balance')->default(0);
            $table->bigInteger('in_balance')->default(0);
            $table->bigInteger('out_balance')->default(0);
            $table->bigInteger('ending_balance')->default(0);
            $table->bigInteger('beginning_base_balance')->default(0);
            $table->bigInteger('in_base_balance')->default(0);
            $table->bigInteger('out_base_balance')->default(0);
            $table->bigInteger('ending_base_balance')->default(0);
            $table->string('slug');
            $table->unsignedTinyInteger('month');
            $table->year('year');
            $table->enum('status_running', StatusRunningCurrentStockEnum::values())->default(StatusRunningCurrentStockEnum::SEDANG_PROSES->value);
            $table->timestamps();
            $table->softDeletes();
            $table->index(['product_id', 'month', 'year', 'status_running']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_cards');
    }
};
