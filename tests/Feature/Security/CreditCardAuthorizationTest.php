<?php

namespace Tests\Feature\Security;

use App\Models\CreditCard;
use App\Models\CreditCardInstallment;
use App\Models\CreditCardPayment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CreditCardAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    private User $userA;
    private User $userB;
    private CreditCard $card;

    protected function setUp(): void
    {
        parent::setUp();
        $this->userA = User::factory()->create();
        $this->userB = User::factory()->create();
        $this->card  = CreditCard::forceCreate([
            'user_id'      => $this->userA->id,
            'name'         => 'Nubank userA',
            'brand'        => 'mastercard',
            'credit_limit' => 5000,
            'due_day'      => 10,
            'closing_day'  => 3,
            'color'        => '#8b5cf6',
            'is_active'    => true,
        ]);
    }

    public function test_destroy_card_retorna_403_para_cartao_alheio(): void
    {
        $this->actingAs($this->userB)
            ->delete(route('creditcards.destroy', $this->card))
            ->assertStatus(403);
    }

    public function test_update_card_retorna_403_para_cartao_alheio(): void
    {
        $this->actingAs($this->userB)
            ->patch(route('creditcards.update', $this->card), [
                'name'         => 'Hackeado',
                'brand'        => 'visa',
                'credit_limit' => '1.000,00',
                'due_day'      => 5,
                'closing_day'  => 1,
                'color'        => '#000000',
            ])
            ->assertStatus(403);
    }

    public function test_store_installment_retorna_403_para_cartao_alheio(): void
    {
        $this->actingAs($this->userB)
            ->post(route('creditcards.installments.store', $this->card), [
                'description'        => 'Notebook',
                'category'           => 'eletronico',
                'total_amount'       => '3.000,00',
                'total_installments' => 12,
                'purchase_date'      => now()->toDateString(),
            ])
            ->assertStatus(403);
    }

    public function test_payoff_installment_retorna_403_para_parcelamento_alheio(): void
    {
        $installment = CreditCardInstallment::forceCreate([
            'user_id'             => $this->userA->id,
            'credit_card_id'      => $this->card->id,
            'description'         => 'Notebook',
            'category'            => 'eletronico',
            'total_amount'        => 3000,
            'installment_amount'  => 250,
            'total_installments'  => 12,
            'current_installment' => 1,
            'is_recurring'        => false,
            'purchase_date'       => now()->toDateString(),
            'is_paid_off'         => false,
        ]);

        $this->actingAs($this->userB)
            ->post(route('creditcards.installments.payoff', $installment))
            ->assertStatus(403);
    }

    public function test_destroy_installment_retorna_403_para_parcelamento_alheio(): void
    {
        $installment = CreditCardInstallment::forceCreate([
            'user_id'             => $this->userA->id,
            'credit_card_id'      => $this->card->id,
            'description'         => 'Notebook',
            'category'            => 'eletronico',
            'total_amount'        => 3000,
            'installment_amount'  => 250,
            'total_installments'  => 12,
            'current_installment' => 1,
            'is_recurring'        => false,
            'purchase_date'       => now()->toDateString(),
            'is_paid_off'         => false,
        ]);

        $this->actingAs($this->userB)
            ->delete(route('creditcards.installments.destroy', $installment))
            ->assertStatus(403);
    }

    public function test_toggle_payment_retorna_403_para_pagamento_alheio(): void
    {
        $payment = CreditCardPayment::forceCreate([
            'user_id'        => $this->userA->id,
            'credit_card_id' => $this->card->id,
            'month'          => '2024-01',
            'amount'         => 250,
            'paid'           => false,
        ]);

        $this->actingAs($this->userB)
            ->post(route('creditcards.payments.toggle', $payment))
            ->assertStatus(403);
    }

    public function test_update_payment_amount_retorna_403_para_pagamento_alheio(): void
    {
        $payment = CreditCardPayment::forceCreate([
            'user_id'        => $this->userA->id,
            'credit_card_id' => $this->card->id,
            'month'          => '2024-01',
            'amount'         => 250,
            'paid'           => false,
        ]);

        $this->actingAs($this->userB)
            ->patch(route('creditcards.payments.amount', $payment), ['amount' => '999,00'])
            ->assertStatus(403);
    }

    public function test_advance_installment_retorna_403_para_parcelamento_alheio(): void
    {
        $installment = CreditCardInstallment::forceCreate([
            'user_id'             => $this->userA->id,
            'credit_card_id'      => $this->card->id,
            'description'         => 'Notebook',
            'category'            => 'eletronico',
            'total_amount'        => 3000,
            'installment_amount'  => 250,
            'total_installments'  => 12,
            'current_installment' => 1,
            'is_recurring'        => false,
            'purchase_date'       => now()->toDateString(),
            'is_paid_off'         => false,
        ]);

        $this->actingAs($this->userB)
            ->post(route('creditcards.installments.advance', $installment))
            ->assertStatus(403);
    }

    public function test_update_installment_retorna_403_para_parcelamento_alheio(): void
    {
        $installment = CreditCardInstallment::forceCreate([
            'user_id'             => $this->userA->id,
            'credit_card_id'      => $this->card->id,
            'description'         => 'Notebook',
            'category'            => 'eletronico',
            'total_amount'        => 3000,
            'installment_amount'  => 250,
            'total_installments'  => 12,
            'current_installment' => 1,
            'is_recurring'        => false,
            'purchase_date'       => now()->toDateString(),
            'is_paid_off'         => false,
        ]);

        $this->actingAs($this->userB)
            ->patch(route('creditcards.installments.update', $installment), [
                'description'        => 'Roubado',
                'category'           => 'outros',
                'total_amount'       => '1,00',
                'total_installments' => 1,
                'purchase_date'      => now()->toDateString(),
            ])
            ->assertStatus(403);
    }

    public function test_regress_installment_retorna_403_para_parcelamento_alheio(): void
    {
        $installment = CreditCardInstallment::forceCreate([
            'user_id'             => $this->userA->id,
            'credit_card_id'      => $this->card->id,
            'description'         => 'Notebook',
            'category'            => 'eletronico',
            'total_amount'        => 3000,
            'installment_amount'  => 250,
            'total_installments'  => 12,
            'current_installment' => 1,
            'is_recurring'        => false,
            'purchase_date'       => now()->toDateString(),
            'is_paid_off'         => false,
        ]);

        $this->actingAs($this->userB)
            ->post(route('creditcards.installments.regress', $installment))
            ->assertStatus(403);
    }
}
