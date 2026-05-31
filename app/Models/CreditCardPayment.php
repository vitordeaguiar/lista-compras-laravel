<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CreditCardPayment extends Model
{
    protected $fillable = [
        'credit_card_id', 'month', 'amount', 'paid', 'paid_at',
    ];

    protected $casts = [
        'paid'    => 'boolean',
        'paid_at' => 'datetime',
        'amount'  => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function creditCard()
    {
        return $this->belongsTo(CreditCard::class);
    }
}
