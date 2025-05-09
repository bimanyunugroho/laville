<?php

use App\Enums\StatusClosedPeriod;
use App\Enums\StatusReceiptEnum;
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
        Schema::create('closed_periods', function (Blueprint $table) {
            $table->id();
            $table->string('no_closed')->unique();
            $table->string('slug');
            $table->unsignedTinyInteger('month');
            $table->year('year');
            $table->foreignId('user_id')->constrained()->cascadeOnUpdate()->cascadeOnDelete();
            $table->timestamp('closed_date');
            $table->foreignId('user_ack_id')->nullable()->constrained('users')->cascadeOnUpdate()->cascadeOnDelete();
            $table->timestamp('ack_date')->nullable();
            $table->foreignId('user_reject_id')->nullable()->constrained('users')->cascadeOnUpdate()->cascadeOnDelete();
            $table->timestamp('reject_date')->nullable();
            $table->enum('status_period', StatusClosedPeriod::values()); // OPEN | RUNNING | CLOSED
            $table->enum('status_confirm', StatusReceiptEnum::values());
            $table->boolean('status')->default(true);
            $table->timestamps();
            $table->softDeletes();
            $table->index(['no_closed', 'month', 'year']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('closed_periods');
    }
};
