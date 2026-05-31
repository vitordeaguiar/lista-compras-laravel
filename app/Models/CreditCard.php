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

    public function getCurrentMonthAmount(string $month): float
    {
        $monthDate = Carbon::parse($month . '-01');

        return (float) $this->installments()
            ->where('is_paid_off', false)
            ->get()
            ->filter(fn($inst) => $inst->isActiveInMonth($monthDate))
            ->sum('installment_amount');
    }

    public function getProjection(int $months = 6, string $fromMonth = null): array
    {
        $from = Carbon::parse(($fromMonth ?? now()->format('Y-m')) . '-01');
        $installments = $this->installments()->where('is_paid_off', false)->get();
        $projection = [];

        for ($i = 0; $i < $months; $i++) {
            $m = $from->copy()->addMonths($i);
            $total = (float) $installments
                ->filter(fn($inst) => $inst->isActiveInMonth($m))
                ->sum('installment_amount');

            $projection[] = [
                'label' => $m->locale('pt_BR')->isoFormat('MMM/YY'),
                'month' => $m->format('Y-m'),
                'value' => $total,
            ];
        }

        return $projection;
    }
}
