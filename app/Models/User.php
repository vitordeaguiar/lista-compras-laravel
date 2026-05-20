<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = ['name', 'email', 'password'];
    protected $hidden   = ['password', 'remember_token'];
    protected $casts    = ['password' => 'hashed'];

    public function shoppingLists()
    {
        return $this->hasMany(ShoppingList::class);
    }

    public function financialMonths()
    {
        return $this->hasMany(FinancialMonth::class);
    }

    public function financialIncomes()
    {
        return $this->hasMany(FinancialIncome::class);
    }

    public function financialFixedCosts()
    {
        return $this->hasMany(FinancialFixedCost::class);
    }

    public function financialFixedPayments()
    {
        return $this->hasMany(FinancialFixedPayment::class);
    }

    public function financialVariableCosts()
    {
        return $this->hasMany(FinancialVariableCost::class);
    }

    public function financialInvestments()
    {
        return $this->hasMany(FinancialInvestment::class);
    }

    public function financialInvestmentEntries()
    {
        return $this->hasMany(FinancialInvestmentEntry::class);
    }
}
