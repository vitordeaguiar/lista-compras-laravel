<?php

namespace Tests\Feature;

use App\Models\CreditCard;
use App\Models\CreditCardInstallment;
use App\Models\CreditCardPayment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CreditCardTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    // ── index ──────────────────────────────────────────────────────────

    public function test_index_retorna_200_sem_cartoes(): void
    {
        $this->actingAs($this->user)
            ->get(route('creditcards.index'))
            ->assertOk();
    }

    public function test_index_retorna_200_com_cartao_e_cria_pagamento_automaticamente(): void
    {
        $card = CreditCard::forceCreate([
            'user_id'      => $this->user->id,
            'name'         => 'Nubank',
            'brand'        => 'mastercard',
            'credit_limit' => 5000,
            'due_day'      => 10,
            'closing_day'  => 3,
            'color'        => '#8b5cf6',
            'is_active'    => true,
        ]);

        $this->actingAs($this->user)
            ->get(route('creditcards.index'))
            ->assertOk();

        $this->assertDatabaseHas('credit_card_payments', [
            'credit_card_id' => $card->id,
            'user_id'        => $this->user->id,
        ]);
    }

    // ── store ──────────────────────────────────────────────────────────

    public function test_store_cria_cartao_com_user_id_correto(): void
    {
        $this->actingAs($this->user)
            ->post(route('creditcards.store'), [
                'name'         => 'Nubank Roxo',
                'brand'        => 'mastercard',
                'credit_limit' => '5000,00',
                'due_day'      => 10,
                'closing_day'  => 3,
                'color'        => '#8b5cf6',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('credit_cards', [
            'name'    => 'Nubank Roxo',
            'user_id' => $this->user->id,
        ]);
    }

    public function test_store_converte_limite_mascarado_em_decimal(): void
    {
        $this->actingAs($this->user)
            ->post(route('creditcards.store'), [
                'name'         => 'Inter',
                'brand'        => 'visa',
                'credit_limit' => '3.500,50',
                'due_day'      => 5,
                'closing_day'  => 28,
                'color'        => '#059669',
            ])
            ->assertRedirect();

        $card = CreditCard::where('name', 'Inter')->first();
        $this->assertNotNull($card);
        $this->assertEquals(3500.50, (float) $card->credit_limit);
    }

    public function test_store_nao_salva_cartao_de_outro_usuario(): void
    {
        $outro = User::factory()->create();

        $this->actingAs($this->user)
            ->post(route('creditcards.store'), [
                'name'         => 'Cartão X',
                'brand'        => 'visa',
                'credit_limit' => '1000,00',
                'due_day'      => 1,
                'closing_day'  => 20,
                'color'        => '#000000',
            ]);

        $this->assertDatabaseMissing('credit_cards', [
            'name'    => 'Cartão X',
            'user_id' => $outro->id,
        ]);
    }

    // ── storeInstallment ───────────────────────────────────────────────

    public function test_store_installment_cria_parcelamento_com_user_id_correto(): void
    {
        $card = CreditCard::forceCreate([
            'user_id'      => $this->user->id,
            'name'         => 'Nubank',
            'brand'        => 'mastercard',
            'credit_limit' => 5000,
            'due_day'      => 10,
            'closing_day'  => 3,
            'color'        => '#8b5cf6',
            'is_active'    => true,
        ]);

        $this->actingAs($this->user)
            ->post(route('creditcards.installments.store', $card), [
                'description'        => 'iPhone 15',
                'category'           => 'eletronico',
                'total_amount'       => '6.000,00',
                'total_installments' => 12,
                'purchase_date'      => now()->toDateString(),
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('credit_card_installments', [
            'credit_card_id' => $card->id,
            'user_id'        => $this->user->id,
            'description'    => 'iPhone 15',
        ]);
    }

    public function test_store_installment_calcula_valor_por_parcela_corretamente(): void
    {
        $card = CreditCard::forceCreate([
            'user_id'      => $this->user->id,
            'name'         => 'Nubank',
            'brand'        => 'mastercard',
            'credit_limit' => 5000,
            'due_day'      => 10,
            'closing_day'  => 3,
            'color'        => '#8b5cf6',
            'is_active'    => true,
        ]);

        $this->actingAs($this->user)
            ->post(route('creditcards.installments.store', $card), [
                'description'        => 'Notebook',
                'category'           => 'eletronico',
                'total_amount'       => '3.000,00',
                'total_installments' => 12,
                'purchase_date'      => now()->toDateString(),
            ]);

        $inst = CreditCardInstallment::where('description', 'Notebook')->first();
        $this->assertNotNull($inst);
        $this->assertEquals(250.00, (float) $inst->installment_amount);
        $this->assertEquals(3000.00, (float) $inst->total_amount);
    }
}
