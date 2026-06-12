<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class CreditCard extends Model
{
    protected $fillable = [
        'name', 'brand', 'credit_limit',
        'due_day', 'closing_day', 'color', 'is_active',
    ];

    protected $casts = ['is_active' => 'boolean', 'credit_limit' => 'decimal:2'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function installments()
    {
        return $this->hasMany(CreditCardInstallment::class);
    }

    public function payments()
    {
        return $this->hasMany(CreditCardPayment::class);
    }

    /**
     * Valor do cartão que deve entrar no total de saídas do mês.
     *
     * Regra:
     *  - fatura NÃO paga                → estimativa (soma das parcelas ativas no mês);
     *  - fatura paga e amount > 0       → valor REAL informado pelo usuário;
     *  - fatura paga mas amount 0/null  → cai de volta na estimativa (nunca zera indevidamente).
     *
     * Método puro: opera sobre a relação `installments` já carregada e o
     * $payment recebido por parâmetro — não faz query interna.
     */
    public function billedAmountForMonth(Carbon $monthDate, ?CreditCardPayment $payment = null): float
    {
        $estimate = (float) $this->installments
            ->filter(fn($inst) => $inst->isActiveInMonth($monthDate, $this))
            ->sum('installment_amount');

        if ($payment && $payment->paid && (float) $payment->amount > 0) {
            return (float) $payment->amount;
        }

        return $estimate;
    }
}
