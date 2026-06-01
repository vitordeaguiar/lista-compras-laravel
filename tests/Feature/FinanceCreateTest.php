<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FinanceCreateTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_adiciona_entrada_com_user_id(): void
    {
        $this->actingAs($this->user)
            ->post(route('finance.income.store'), [
                'name'   => 'Salário',
                'amount' => 5000,
                'month'  => '2026-06',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('financial_incomes', [
            'name'    => 'Salário',
            'month'   => '2026-06',
            'user_id' => $this->user->id,
        ]);
    }

    public function test_adiciona_variavel_com_user_id(): void
    {
        $this->actingAs($this->user)
            ->post(route('finance.variable.store'), [
                'name'     => 'Cinema',
                'category' => 'lazer',
                'amount'   => 80,
                'month'    => '2026-06',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('financial_variable_costs', [
            'name'    => 'Cinema',
            'user_id' => $this->user->id,
        ]);
    }

    public function test_adiciona_fixo_com_user_id_e_pagamento(): void
    {
        $this->actingAs($this->user)
            ->post(route('finance.fixed.store'), [
                'name'    => 'Aluguel',
                'amount'  => 1500,
                'due_day' => 10,
                'month'   => '2026-06',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('financial_fixed_costs', [
            'name'    => 'Aluguel',
            'user_id' => $this->user->id,
        ]);
        $this->assertDatabaseHas('financial_fixed_payments', [
            'month'   => '2026-06',
            'user_id' => $this->user->id,
        ]);
    }

    public function test_adiciona_investimento_com_user_id(): void
    {
        $this->actingAs($this->user)
            ->post(route('finance.investment.store'), [
                'name'     => 'Tesouro Selic',
                'category' => 'tesouro',
                'amount'   => 300,
                'month'    => '2026-06',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('financial_investments', [
            'name'    => 'Tesouro Selic',
            'user_id' => $this->user->id,
        ]);
        $this->assertDatabaseHas('financial_investment_entries', [
            'month'   => '2026-06',
            'user_id' => $this->user->id,
        ]);
    }

    public function test_cria_lista_de_compras_com_user_id(): void
    {
        $this->actingAs($this->user)
            ->post(route('lists.store'), [
                'name'          => 'Mercado',
                'shopping_date' => '2026-06-01',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('shopping_lists', [
            'name'    => 'Mercado',
            'user_id' => $this->user->id,
        ]);
    }
}
