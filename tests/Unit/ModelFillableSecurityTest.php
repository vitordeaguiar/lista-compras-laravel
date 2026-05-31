<?php

namespace Tests\Unit;

use App\Models\CreditCard;
use App\Models\CreditCardInstallment;
use App\Models\CreditCardPayment;
use App\Models\FinancialFixedCost;
use App\Models\FinancialFixedPayment;
use App\Models\FinancialIncome;
use App\Models\FinancialInvestment;
use App\Models\FinancialInvestmentEntry;
use App\Models\FinancialMonth;
use App\Models\FinancialVariableCost;
use App\Models\ShoppingList;
use App\Models\User;
use App\Models\UserSetting;
use PHPUnit\Framework\TestCase;

class ModelFillableSecurityTest extends TestCase
{
    private array $forbidden = ['user_id', 'is_admin', 'password', 'email'];

    private function assertNoForbiddenFields(object $model): void
    {
        $fillable = $model->getFillable();
        foreach ($this->forbidden as $field) {
            $this->assertNotContains(
                $field,
                $fillable,
                get_class($model) . " não deve ter '{$field}' no \$fillable"
            );
        }
    }

    public function test_shopping_list_nao_expoe_user_id(): void
    {
        $this->assertNoForbiddenFields(new ShoppingList());
    }

    public function test_financial_income_nao_expoe_user_id(): void
    {
        $this->assertNoForbiddenFields(new FinancialIncome());
    }

    public function test_financial_fixed_cost_nao_expoe_user_id(): void
    {
        $this->assertNoForbiddenFields(new FinancialFixedCost());
    }

    public function test_financial_fixed_payment_nao_expoe_user_id(): void
    {
        $this->assertNoForbiddenFields(new FinancialFixedPayment());
    }

    public function test_financial_variable_cost_nao_expoe_user_id(): void
    {
        $this->assertNoForbiddenFields(new FinancialVariableCost());
    }

    public function test_financial_investment_nao_expoe_user_id(): void
    {
        $this->assertNoForbiddenFields(new FinancialInvestment());
    }

    public function test_financial_investment_entry_nao_expoe_user_id(): void
    {
        $this->assertNoForbiddenFields(new FinancialInvestmentEntry());
    }

    public function test_financial_month_nao_expoe_user_id(): void
    {
        $this->assertNoForbiddenFields(new FinancialMonth());
    }

    public function test_credit_card_nao_expoe_user_id(): void
    {
        $this->assertNoForbiddenFields(new CreditCard());
    }

    public function test_credit_card_installment_nao_expoe_user_id(): void
    {
        $this->assertNoForbiddenFields(new CreditCardInstallment());
    }

    public function test_credit_card_payment_nao_expoe_user_id(): void
    {
        $this->assertNoForbiddenFields(new CreditCardPayment());
    }

    public function test_user_setting_nao_expoe_user_id(): void
    {
        $this->assertNoForbiddenFields(new UserSetting());
    }

    public function test_user_nao_expoe_is_admin(): void
    {
        // email e password são legítimos no User; o perigo é is_admin (escalonamento)
        $this->assertNotContains('is_admin', (new User())->getFillable());
    }
}
