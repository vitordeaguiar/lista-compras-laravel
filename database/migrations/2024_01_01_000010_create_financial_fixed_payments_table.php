<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('financial_fixed_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fixed_cost_id')->constrained('financial_fixed_costs')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('month', 7);
            $table->decimal('amount', 10, 2);
            $table->boolean('paid')->default(false);
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
            $table->unique(['fixed_cost_id', 'month', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('financial_fixed_payments');
    }
};
