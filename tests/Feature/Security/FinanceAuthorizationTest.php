<?php

namespace Tests\Feature\Security;

use App\Models\FinancialFixedCost;
use App\Models\FinancialFixedPayment;
use App\Models\FinancialIncome;
use App\Models\FinancialInvestment;
use App\Models\FinancialInvestmentEntry;
use App\Models\FinancialVariableCost;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FinanceAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    private User $userA;
    private User $userB;

    protected function setUp(): void
    {
        parent::setUp();
        $this->userA = User::factory()->create();
        $this->userB = User::factory()->create();
    }

    // --- openMonth IDOR ---

    public function test_open_month_nao_copia_income_de_outro_usuario(): void
    {
        $income = FinancialIncome::forceCreate([
            'user_id' => $this->userA->id,
            'month'   => '2024-01',
            'name'    => 'Salário userA',
            'amount'  => 5000,
        ]);

        $this->actingAs($this->userB)
            ->post(route('finance.open-month'), [
                'month'      => '2024-02',
                'income_ids' => [$income->id],
            ])
            ->assertRedirect();

        $this->assertDatabaseMissing('financial_incomes', [
            'user_id' => $this->userB->id,
            'name'    => 'Salário userA',
            'month'   => '2024-02',
        ]);
    }

    public function test_open_month_nao_copia_fixed_cost_de_outro_usuario(): void
    {
        $costA = FinancialFixedCost::forceCreate([
            'user_id'      => $this->userA->id,
            'name'         => 'Aluguel userA',
            'amount'       => 1500,
            'due_day'      => 10,
            'is_recurring' => true,
        ]);
        FinancialFixedPayment::forceCreate([
            'fixed_cost_id' => $costA->id,
            'user_id'       => $this->userA->id,
            'month'         => '2024-01',
            'amount'        => 1500,
            'paid'          => false,
        ]);

        $this->actingAs($this->userB)
            ->post(route('finance.open-month'), [
                'month'     => '2024-02',
                'fixed_ids' => [$costA->id],
            ])
            ->assertRedirect();

        $this->assertDatabaseMissing('financial_fixed_payments', [
            'user_id'       => $this->userB->id,
            'fixed_cost_id' => $costA->id,
            'month'         => '2024-02',
        ]);
    }

    // --- toggleFixed ---

    public function test_toggle_fixed_retorna_403_para_pagamento_alheio(): void
    {
        $cost = FinancialFixedCost::forceCreate([
            'user_id' => $this->userA->id, 'name' => 'Conta', 'amount' => 100, 'due_day' => 5, 'is_recurring' => true,
        ]);
        $payment = FinancialFixedPayment::forceCreate([
            'fixed_cost_id' => $cost->id, 'user_id' => $this->userA->id, 'month' => '2024-01', 'amount' => 100, 'paid' => false,
        ]);

        $this->actingAs($this->userB)
            ->patch(route('finance.fixed.toggle', $payment))
            ->assertStatus(403);
    }

    // --- updateFixed ---

    public function test_update_fixed_retorna_403_para_pagamento_alheio(): void
    {
        $cost = FinancialFixedCost::forceCreate([
            'user_id' => $this->userA->id, 'name' => 'Conta', 'amount' => 100, 'due_day' => 5, 'is_recurring' => true,
        ]);
        $payment = FinancialFixedPayment::forceCreate([
            'fixed_cost_id' => $cost->id, 'user_id' => $this->userA->id, 'month' => '2024-01', 'amount' => 100, 'paid' => false,
        ]);

        $this->actingAs($this->userB)
            ->patch(route('finance.fixed.update', $payment), ['amount' => 200])
            ->assertStatus(403);
    }

    // --- destroyFixed ---

    public function test_destroy_fixed_retorna_403_para_custo_alheio(): void
    {
        $cost = FinancialFixedCost::forceCreate([
            'user_id' => $this->userA->id, 'name' => 'Conta', 'amount' => 100, 'due_day' => 5, 'is_recurring' => true,
        ]);

        $this->actingAs($this->userB)
            ->delete(route('finance.fixed.destroy', $cost))
            ->assertStatus(403);
    }

    // --- toggleVariable ---

    public function test_toggle_variable_retorna_403_para_custo_alheio(): void
    {
        $variable = FinancialVariableCost::forceCreate([
            'user_id' => $this->userA->id, 'month' => '2024-01', 'name' => 'Mercado', 'category' => 'alimentacao', 'amount' => 300, 'paid' => false,
        ]);

        $this->actingAs($this->userB)
            ->patch(route('finance.variable.toggle', $variable))
            ->assertStatus(403);
    }

    // --- updateVariable ---

    public function test_update_variable_retorna_403_para_custo_alheio(): void
    {
        $variable = FinancialVariableCost::forceCreate([
            'user_id' => $this->userA->id, 'month' => '2024-01', 'name' => 'Mercado', 'category' => 'alimentacao', 'amount' => 300, 'paid' => false,
        ]);

        $this->actingAs($this->userB)
            ->patch(route('finance.variable.update', $variable), ['amount' => 500])
            ->assertStatus(403);
    }

    // --- destroyVariable ---

    public function test_destroy_variable_retorna_403_para_custo_alheio(): void
    {
        $variable = FinancialVariableCost::forceCreate([
            'user_id' => $this->userA->id, 'month' => '2024-01', 'name' => 'Mercado', 'category' => 'alimentacao', 'amount' => 300, 'paid' => false,
        ]);

        $this->actingAs($this->userB)
            ->delete(route('finance.variable.destroy', $variable))
            ->assertStatus(403);
    }

    // --- destroyIncome ---

    public function test_destroy_income_retorna_403_para_entrada_alheia(): void
    {
        $income = FinancialIncome::forceCreate([
            'user_id' => $this->userA->id, 'month' => '2024-01', 'name' => 'Salário', 'amount' => 5000,
        ]);

        $this->actingAs($this->userB)
            ->delete(route('finance.income.destroy', $income))
            ->assertStatus(403);
    }

    // --- updateInvestment ---

    public function test_update_investment_entry_retorna_403_para_entry_alheia(): void
    {
        $inv = FinancialInvestment::forceCreate([
            'user_id' => $this->userA->id, 'name' => 'Tesouro', 'category' => 'tesouro', 'started_at' => now(),
        ]);
        $entry = FinancialInvestmentEntry::forceCreate([
            'investment_id' => $inv->id, 'user_id' => $this->userA->id, 'month' => '2024-01', 'amount' => 500,
        ]);

        $this->actingAs($this->userB)
            ->patch(route('finance.investment.update', $entry), ['amount' => 999])
            ->assertStatus(403);
    }

    // --- destroyInvestment ---

    public function test_destroy_investment_retorna_403_para_investimento_alheio(): void
    {
        $inv = FinancialInvestment::forceCreate([
            'user_id' => $this->userA->id, 'name' => 'Tesouro', 'category' => 'tesouro', 'started_at' => now(),
        ]);

        $this->actingAs($this->userB)
            ->delete(route('finance.investment.destroy', $inv))
            ->assertStatus(403);
    }
}
