<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FinancialFixedPayment extends Model
{
    protected $fillable = ['fixed_cost_id', 'month', 'amount', 'paid', 'paid_at'];
    protected $casts    = ['paid' => 'boolean', 'paid_at' => 'datetime', 'amount' => 'decimal:2'];

    public function fixedCost()
    {
        return $this->belongsTo(FinancialFixedCost::class, 'fixed_cost_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
