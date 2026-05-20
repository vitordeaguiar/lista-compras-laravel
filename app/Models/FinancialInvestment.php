<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FinancialInvestment extends Model
{
    protected $fillable = ['user_id', 'name', 'category', 'started_at'];
    protected $casts    = ['started_at' => 'date'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function entries()
    {
        return $this->hasMany(FinancialInvestmentEntry::class, 'investment_id');
    }
}
