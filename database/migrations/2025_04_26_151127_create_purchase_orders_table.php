<?php

use App\Enums\StatusPOEnum;
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
        Schema::create('purchase_orders', function (Blueprint $table) {
            $table->id();
            $table->string('po_number')->unique();
            $table->string('slug');
            $table->foreignId('supplier_id')->constrained()->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnUpdate()->cascadeOnDelete();
            $table->date('po_date');
            $table->date('expected_date')->nullable();
            $table->foreignId('user_ack_id')->nullable()->constrained('users')->cascadeOnUpdate()->cascadeOnDelete();
            $table->date('ack_date')->nullable();
            $table->foreignId('user_reject_id')->nullable()->constrained('users')->cascadeOnUpdate()->cascadeOnDelete();
            $table->date('reject_date')->nullable();
            $table->bigInteger('subtotal')->default(0);
            $table->bigInteger('tax')->default(0);
            $table->bigInteger('discount')->default(0);
            $table->bigInteger('total_net')->default(0);
            $table->enum('status', StatusPOEnum::values())->default(StatusPOEnum::PROSESS);
            $table->text('notes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_orders');
    }
};
