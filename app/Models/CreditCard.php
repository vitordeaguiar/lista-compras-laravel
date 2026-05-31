<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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
}
