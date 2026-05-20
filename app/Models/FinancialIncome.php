<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FinancialIncome extends Model
{
    protected $fillable = ['user_id', 'month', 'name', 'amount', 'received_at'];
    protected $casts    = ['received_at' => 'date', 'amount' => 'decimal:2'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
