<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // converte o enum de categoria em string para permitir novas categorias
        // (carro, comida, ...) sem precisar alterar o schema a cada inclusão.
        // A validação dos valores aceitos fica no CreditCardController.
        Schema::table('credit_card_installments', function (Blueprint $table) {
            $table->string('category', 50)->change();
        });
    }

    public function down(): void
    {
        Schema::table('credit_card_installments', function (Blueprint $table) {
            $table->enum('category', ['compras', 'assinatura', 'eletronico', 'casa', 'saude', 'outros'])->change();
        });
    }
};
