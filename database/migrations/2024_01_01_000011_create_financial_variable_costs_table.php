<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('financial_variable_costs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('month', 7);
            $table->string('name');
            $table->enum('category', ['lazer', 'delivery', 'compras', 'transporte', 'saude', 'educacao', 'outros']);
            $table->decimal('amount', 10, 2);
            $table->boolean('paid')->default(false);
            $table->date('spent_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('financial_variable_costs');
    }
};
