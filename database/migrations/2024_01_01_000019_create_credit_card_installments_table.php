<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('credit_card_installments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('credit_card_id')->constrained()->cascadeOnDelete();
            $table->string('description');
            $table->enum('category', ['compras', 'assinatura', 'eletronico', 'casa', 'saude', 'outros']);
            $table->decimal('total_amount', 10, 2);
            $table->decimal('installment_amount', 10, 2);
            $table->integer('total_installments')->default(1);
            $table->integer('current_installment')->default(1);
            $table->boolean('is_recurring')->default(false);
            $table->date('purchase_date');
            $table->boolean('is_paid_off')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('credit_card_installments');
    }
};
