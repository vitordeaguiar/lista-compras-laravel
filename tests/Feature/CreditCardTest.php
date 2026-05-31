<?php

namespace Tests\Feature;

use App\Models\CreditCard;
use App\Models\CreditCardInstallment;
use App\Models\CreditCardPayment;
use App\Models\User;
use Carbon\Carbon;
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

    public function test_index_renderiza_projecao_sem_modificacao_indireta(): void
    {
        CreditCard::forceCreate([
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
            ->assertOk()
            ->assertSee('Nubank');
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

    // ── update card ────────────────────────────────────────────────────

    public function test_update_altera_dados_e_cor_do_cartao(): void
    {
        $card = $this->makeCard(['color' => '#8b5cf6', 'name' => 'Nubank']);

        $this->actingAs($this->user)
            ->patch(route('creditcards.update', $card), [
                'name'         => 'Nubank Editado',
                'brand'        => 'visa',
                'credit_limit' => '8.000,00',
                'due_day'      => 15,
                'closing_day'  => 8,
                'color'        => '#dc2626',
            ])
            ->assertRedirect();

        $card->refresh();
        $this->assertEquals('Nubank Editado', $card->name);
        $this->assertEquals('#dc2626', $card->color);
        $this->assertEquals(8000.00, (float) $card->credit_limit);
        $this->assertEquals(15, $card->due_day);
        $this->assertEquals(8, $card->closing_day);
    }

    // ── update installment ─────────────────────────────────────────────

    public function test_update_installment_altera_dados_e_recalcula_parcela(): void
    {
        $card = $this->makeCard();
        $inst = CreditCardInstallment::forceCreate([
            'user_id'             => $this->user->id,
            'credit_card_id'      => $card->id,
            'description'         => 'TV',
            'category'            => 'eletronico',
            'total_amount'        => 1000,
            'installment_amount'  => 100,
            'total_installments'  => 10,
            'current_installment' => 1,
            'is_recurring'        => false,
            'purchase_date'       => '2026-01-01',
            'is_paid_off'         => false,
        ]);

        $this->actingAs($this->user)
            ->patch(route('creditcards.installments.update', $inst), [
                'description'        => 'TV nova',
                'category'           => 'casa',
                'total_amount'       => '2.000,00',
                'total_installments' => 4,
                'purchase_date'      => '2026-03-01',
            ])
            ->assertRedirect();

        $inst->refresh();
        $this->assertEquals('TV nova', $inst->description);
        $this->assertEquals('casa', $inst->category);
        $this->assertEquals(500.00, (float) $inst->installment_amount); // 2000 / 4
        $this->assertEquals(4, $inst->total_installments);
    }

    // ── advance / regress (override manual) ────────────────────────────

    public function test_advance_installment_grava_override_manual(): void
    {
        $this->travelTo(Carbon::parse('2026-05-31'));
        $card = $this->makeCard(['closing_day' => 3, 'due_day' => 10]);
        $inst = CreditCardInstallment::forceCreate([
            'user_id'             => $this->user->id,
            'credit_card_id'      => $card->id,
            'description'         => 'Compra',
            'category'            => 'compras',
            'total_amount'        => 1000,
            'installment_amount'  => 100,
            'total_installments'  => 10,
            'current_installment' => 1,
            'is_recurring'        => false,
            'purchase_date'       => '2026-05-31', // 1ª parcela em junho → 0 pagas em maio
            'is_paid_off'         => false,
        ]);

        $this->actingAs($this->user)
            ->post(route('creditcards.installments.advance', $inst));

        $inst->refresh();
        $this->assertEquals(1, $inst->manual_paid_count);
    }

    public function test_regress_installment_nao_passa_de_zero(): void
    {
        $card = $this->makeCard(['closing_day' => 3, 'due_day' => 10]);
        $inst = CreditCardInstallment::forceCreate([
            'user_id'             => $this->user->id,
            'credit_card_id'      => $card->id,
            'description'         => 'Compra',
            'category'            => 'compras',
            'total_amount'        => 1000,
            'installment_amount'  => 100,
            'total_installments'  => 10,
            'current_installment' => 1,
            'manual_paid_count'   => 0,
            'is_recurring'        => false,
            'purchase_date'       => '2026-05-31',
            'is_paid_off'         => false,
        ]);

        $this->actingAs($this->user)
            ->post(route('creditcards.installments.regress', $inst));

        $inst->refresh();
        $this->assertEquals(0, $inst->manual_paid_count);
    }

    // ── index renderiza com parcelamento (saldo devedor, datas) ────────

    public function test_index_renderiza_com_parcelamento_sem_erro(): void
    {
        $card = $this->makeCard(['closing_day' => 3, 'due_day' => 10]);
        CreditCardInstallment::forceCreate([
            'user_id'             => $this->user->id,
            'credit_card_id'      => $card->id,
            'description'         => 'Geladeira',
            'category'            => 'casa',
            'total_amount'        => 1200,
            'installment_amount'  => 100,
            'total_installments'  => 12,
            'current_installment' => 1,
            'is_recurring'        => false,
            'purchase_date'       => '2026-05-31',
            'is_paid_off'         => false,
        ]);

        $this->actingAs($this->user)
            ->get(route('creditcards.index'))
            ->assertOk()
            ->assertSee('Geladeira');
    }

    private function makeCard(array $attrs = []): CreditCard
    {
        return CreditCard::forceCreate(array_merge([
            'user_id'      => $this->user->id,
            'name'         => 'Nubank',
            'brand'        => 'mastercard',
            'credit_limit' => 5000,
            'due_day'      => 10,
            'closing_day'  => 3,
            'color'        => '#8b5cf6',
            'is_active'    => true,
        ], $attrs));
    }
}
