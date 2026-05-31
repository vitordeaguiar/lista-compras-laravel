<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class CreditCardInstallment extends Model
{
    protected $fillable = [
        'credit_card_id', 'description', 'category',
        'total_amount', 'installment_amount', 'total_installments',
        'current_installment', 'manual_paid_count', 'is_recurring',
        'purchase_date', 'is_paid_off',
    ];

    protected $casts = [
        'is_recurring'  => 'boolean',
        'is_paid_off'   => 'boolean',
        'purchase_date' => 'date',
        'total_amount'          => 'decimal:2',
        'installment_amount'    => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function creditCard()
    {
        return $this->belongsTo(CreditCard::class);
    }

    /**
     * Mês (1º dia) em que a 1ª parcela vence, pela regra real de cartão:
     * - compra feita DEPOIS do dia de fechamento entra na fatura do mês seguinte;
     * - se o vencimento é antes do fechamento, a fatura vence no mês seguinte ao fechamento.
     */
    public function firstDueMonth(CreditCard $card): Carbon
    {
        $purchase = Carbon::parse($this->purchase_date);

        // mês em que a compra fecha
        $closeMonth = $purchase->day > $card->closing_day
            ? $purchase->copy()->addMonth()
            : $purchase->copy();

        // mês de vencimento dessa fatura
        $dueMonth = $card->due_day >= $card->closing_day
            ? $closeMonth
            : $closeMonth->copy()->addMonth();

        return $dueMonth->startOfMonth();
    }

    /**
     * A parcela está presente na fatura deste mês?
     * Recorrente: sempre. Parcelada: entre a 1ª e a última parcela.
     */
    public function isActiveInMonth(Carbon $month, CreditCard $card): bool
    {
        if ($this->is_recurring) {
            return true;
        }

        $first = $this->firstDueMonth($card);
        $start = $first->copy()->startOfMonth();
        $end   = $first->copy()->addMonths($this->total_installments - 1)->endOfMonth();

        return $month->between($start, $end);
    }

    /**
     * Quantas parcelas já foram pagas.
     * - Override manual (manual_paid_count) tem prioridade.
     * - Senão, conta automaticamente as faturas cujo vencimento já passou,
     *   considerando o DIA de vencimento (a fatura do mês corrente já conta
     *   como paga se hoje já passou do dia de vencimento).
     */
    public function paidInstallmentsCount(CreditCard $card): int
    {
        if ($this->manual_paid_count !== null) {
            return max(0, min((int) $this->manual_paid_count, $this->total_installments));
        }

        if ($this->is_recurring) {
            return 0;
        }

        $first = $this->firstDueMonth($card);
        $now   = now();

        // meses inteiros entre a 1ª fatura e o mês atual (faturas de meses já passados)
        $monthsDiff = ($now->year - $first->year) * 12 + ($now->month - $first->month);

        if ($monthsDiff < 0) {
            return 0; // a 1ª fatura ainda nem chegou
        }

        // a fatura do mês corrente já venceu? (dia de vencimento já passou)
        $dueDayThisMonth  = min($card->due_day, $now->daysInMonth);
        $currentDuePassed = $now->day >= $dueDayThisMonth ? 1 : 0;

        $paid = $monthsDiff + $currentDuePassed;

        return max(0, min($paid, $this->total_installments));
    }

    /**
     * Número da parcela atual (a que está sendo paga este mês).
     */
    public function currentInstallment(CreditCard $card): int
    {
        if ($this->is_recurring) {
            return 0;
        }

        return min($this->paidInstallmentsCount($card) + 1, $this->total_installments);
    }

    /**
     * Parcelamento totalmente pago (manualmente quitado ou pelo tempo).
     */
    public function isFullyPaid(CreditCard $card): bool
    {
        if ($this->is_paid_off) {
            return true;
        }

        return !$this->is_recurring
            && $this->paidInstallmentsCount($card) >= $this->total_installments;
    }

    /**
     * Saldo devedor que ainda ocupa o limite do cartão.
     * Parcelado: parcelas não pagas × valor da parcela (volta ao limite conforme paga).
     * Recorrente: o valor de um ciclo mensal.
     */
    public function getRemainingAmount(CreditCard $card): float
    {
        if ($this->is_paid_off) {
            return 0.0;
        }

        if ($this->is_recurring) {
            return (float) $this->installment_amount;
        }

        $paid = $this->paidInstallmentsCount($card);

        if ($paid >= $this->total_installments) {
            return 0.0;
        }

        // base no valor total da compra (volta ao limite a cada parcela paga)
        $remaining = (float) $this->total_amount - ($paid * (float) $this->installment_amount);

        return round(max(0, $remaining), 2);
    }

    /**
     * Mês/ano da última parcela (rótulo pt-BR).
     */
    public function getLastInstallmentMonth(CreditCard $card): string
    {
        return $this->firstDueMonth($card)
            ->addMonths($this->total_installments - 1)
            ->locale('pt_BR')
            ->isoFormat('MMM/YYYY');
    }
}
