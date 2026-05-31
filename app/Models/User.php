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
    protected $casts    = ['password' => 'hashed', 'is_admin' => 'boolean'];

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

    public function creditCards()
    {
        return $this->hasMany(CreditCard::class);
    }

    public function settings()
    {
        return $this->hasOne(UserSetting::class)->withDefault([
            'theme'                  => 'dark',
            'accent_color'           => '#2dd4bf',
            'salary_day'             => 5,
            'monthly_budget'         => 0,
            'monthly_savings_goal'   => 0,
            'notify_due_days'        => 3,
            'notify_budget_alert'    => true,
            'notify_monthly_summary' => true,
            'notify_list_reminder'   => true,
            'notify_new_month'       => true,
            'notify_email'           => true,
            'notify_push'            => false,
            'auto_copy_fixed'        => true,
            'auto_copy_incomes'      => true,
            'auto_keep_investments'  => true,
            'layout_density'         => 'comfortable',
        ]);
    }
}
