<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('credit_card_installments', function (Blueprint $table) {
            // null = parcelas pagas calculadas automaticamente pelo tempo;
            // valor = ajuste manual feito pelo usuário (avançar/voltar parcela)
            $table->integer('manual_paid_count')->nullable()->after('current_installment');
        });
    }

    public function down(): void
    {
        Schema::table('credit_card_installments', function (Blueprint $table) {
            $table->dropColumn('manual_paid_count');
        });
    }
};
