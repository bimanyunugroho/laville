<?php

use App\Enums\StatusStockOpnameEnum;
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
        Schema::create('stock_opnames', function (Blueprint $table) {
            $table->id();
            $table->string('so_number')->unique();
            $table->unsignedTinyInteger('month');
            $table->year('year');
            $table->string('slug');
            $table->foreignId('user_id')->constrained()->cascadeOnUpdate()->cascadeOnDelete();
            $table->timestamp('so_date');
            $table->foreignId('user_validator_id')->nullable()->constrained('users')->cascadeOnUpdate()->cascadeOnDelete();
            $table->timestamp('validator_date')->nullable();
            $table->foreignId('user_ack_id')->nullable()->constrained('users')->cascadeOnUpdate()->cascadeOnDelete();
            $table->timestamp('ack_date')->nullable();
            $table->foreignId('user_reject_id')->nullable()->constrained('users')->cascadeOnUpdate()->cascadeOnDelete();
            $table->timestamp('reject_date')->nullable();
            $table->integer('subtotal')->default(0);
            $table->integer('tax')->default(0);
            $table->integer('discount')->default(0);
            $table->integer('total_net')->default(0);
            $table->enum('status', StatusStockOpnameEnum::values())->default(StatusStockOpnameEnum::DRAFT->value);
            $table->text('notes')->nullable();
            $table->boolean('is_locked')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
            $table->index(['so_number']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_opnames');
    }
};
