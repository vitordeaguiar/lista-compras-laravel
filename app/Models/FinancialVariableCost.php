<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FinancialVariableCost extends Model
{
    protected $fillable = ['user_id', 'month', 'name', 'category', 'amount', 'paid', 'spent_at'];
    protected $casts    = ['paid' => 'boolean', 'spent_at' => 'date', 'amount' => 'decimal:2'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
