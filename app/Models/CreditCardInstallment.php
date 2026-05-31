<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class CreditCardInstallment extends Model
{
    protected $fillable = [
        'credit_card_id', 'description', 'category',
        'total_amount', 'installment_amount', 'total_installments',
        'current_installment', 'is_recurring', 'purchase_date', 'is_paid_off',
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

    public function isActiveInMonth(Carbon $month): bool
    {
        if ($this->is_recurring) {
            return true;
        }

        $start = Carbon::parse($this->purchase_date)->startOfMonth();
        $end   = Carbon::parse($this->purchase_date)->addMonths($this->total_installments - 1)->endOfMonth();

        return $month->between($start, $end);
    }

    public function getRemainingAmount(): float
    {
        $paid = max(0, $this->current_installment - 1);
        return (float) round(($this->total_installments - $paid) * $this->installment_amount, 2);
    }

    public function getLastInstallmentMonth(): string
    {
        return Carbon::parse($this->purchase_date)
            ->addMonths($this->total_installments - 1)
            ->locale('pt_BR')
            ->isoFormat('MMM/YYYY');
    }
}
