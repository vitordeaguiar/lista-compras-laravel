<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('credit_cards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->enum('brand', ['visa', 'mastercard', 'elo', 'amex', 'outro']);
            $table->decimal('credit_limit', 10, 2)->default(0);
            $table->integer('due_day');
            $table->integer('closing_day');
            $table->string('color', 50)->default('#7c3aed');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('credit_cards');
    }
};
