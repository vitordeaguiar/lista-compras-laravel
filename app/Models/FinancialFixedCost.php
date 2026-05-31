<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FinancialFixedCost extends Model
{
    protected $fillable = ['name', 'amount', 'due_day', 'is_recurring', 'icon'];
    protected $casts    = ['is_recurring' => 'boolean', 'amount' => 'decimal:2'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function payments()
    {
        return $this->hasMany(FinancialFixedPayment::class, 'fixed_cost_id');
    }
}
