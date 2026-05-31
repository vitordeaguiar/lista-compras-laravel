<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FinancialInvestmentEntry extends Model
{
    protected $fillable = ['investment_id', 'month', 'amount'];
    protected $casts    = ['amount' => 'decimal:2'];

    public function investment()
    {
        return $this->belongsTo(FinancialInvestment::class, 'investment_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
