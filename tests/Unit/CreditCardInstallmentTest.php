<?php

namespace Tests\Unit;

use App\Models\CreditCard;
use App\Models\CreditCardInstallment;
use Carbon\Carbon;
use Tests\TestCase;

class CreditCardInstallmentTest extends TestCase
{
    private function card(int $closingDay, int $dueDay): CreditCard
    {
        return new CreditCard([
            'closing_day' => $closingDay,
            'due_day'     => $dueDay,
        ]);
    }

    private function installment(array $attrs = []): CreditCardInstallment
    {
        return new CreditCardInstallment(array_merge([
            'total_amount'       => 1000,
            'installment_amount' => 100,
            'total_installments' => 10,
            'is_recurring'       => false,
            'purchase_date'      => '2026-01-01',
            'is_paid_off'        => false,
        ], $attrs));
    }

    // ── firstDueMonth (regra real de cartão) ───────────────────────────

    public function test_compra_depois_do_fechamento_cai_na_fatura_do_mes_seguinte(): void
    {
        // fecha dia 3, vence dia 10; compra 31/05 (após o fechamento) → 1ª parcela em junho
        $card = $this->card(closingDay: 3, dueDay: 10);
        $inst = $this->installment(['purchase_date' => '2026-05-31']);

        $this->assertEquals('2026-06', $inst->firstDueMonth($card)->format('Y-m'));
    }

    public function test_compra_a_vista_1x_apos_fechamento_cai_no_mes_seguinte(): void
    {
        // compra 28/05 em 1x num cartão que fecha dia 3 → entra só na fatura de junho
        $card = $this->card(closingDay: 3, dueDay: 10);
        $inst = $this->installment(['purchase_date' => '2026-05-28', 'total_installments' => 1]);

        $this->assertEquals('2026-06', $inst->firstDueMonth($card)->format('Y-m'));
        $this->assertFalse($inst->isActiveInMonth(Carbon::parse('2026-05-01'), $card));
        $this->assertTrue($inst->isActiveInMonth(Carbon::parse('2026-06-01'), $card));
    }

    public function test_compra_antes_do_fechamento_cai_na_fatura_do_mes_corrente(): void
    {
        // fecha dia 10; compra 05/05 (antes do fechamento) → 1ª parcela em maio
        $card = $this->card(closingDay: 10, dueDay: 20);
        $inst = $this->installment(['purchase_date' => '2026-05-05']);

        $this->assertEquals('2026-05', $inst->firstDueMonth($card)->format('Y-m'));
    }

    public function test_vencimento_antes_do_fechamento_empurra_mais_um_mes(): void
    {
        // fecha dia 25, vence dia 5; compra 31/05 → fecha em jun, vence em jul
        $card = $this->card(closingDay: 25, dueDay: 5);
        $inst = $this->installment(['purchase_date' => '2026-05-31']);

        $this->assertEquals('2026-07', $inst->firstDueMonth($card)->format('Y-m'));
    }

    // ── isActiveInMonth ────────────────────────────────────────────────

    public function test_parcela_aparece_apenas_nos_meses_corretos(): void
    {
        // compra 31/05, fecha 3, vence 10, 2x → ativa em jun e jul apenas
        $card = $this->card(closingDay: 3, dueDay: 10);
        $inst = $this->installment(['purchase_date' => '2026-05-31', 'total_installments' => 2]);

        $this->assertFalse($inst->isActiveInMonth(Carbon::parse('2026-05-01'), $card));
        $this->assertTrue($inst->isActiveInMonth(Carbon::parse('2026-06-01'), $card));
        $this->assertTrue($inst->isActiveInMonth(Carbon::parse('2026-07-01'), $card));
        $this->assertFalse($inst->isActiveInMonth(Carbon::parse('2026-08-01'), $card));
    }

    public function test_recorrente_aparece_em_qualquer_mes(): void
    {
        $card = $this->card(closingDay: 3, dueDay: 10);
        $inst = $this->installment(['is_recurring' => true]);

        $this->assertTrue($inst->isActiveInMonth(Carbon::parse('2030-01-01'), $card));
    }

    // ── paidInstallmentsCount (automático pelo tempo) ──────────────────

    public function test_conta_parcelas_pagas_automaticamente_pelo_tempo(): void
    {
        // compra 01/01/2026 fecha 3 vence 10 (1ª parcela jan); hoje 31/05/2026
        $this->travelTo(Carbon::parse('2026-05-31'));

        $card = $this->card(closingDay: 3, dueDay: 10);
        $inst = $this->installment(['purchase_date' => '2026-01-01', 'total_installments' => 10]);

        // jan, fev, mar, abr e maio (venc 10/05 já passou) = 5 pagas; junho é a atual
        $this->assertEquals(5, $inst->paidInstallmentsCount($card));
        $this->assertEquals(6, $inst->currentInstallment($card));
    }

    public function test_fatura_do_mes_corrente_ja_vencida_conta_como_paga(): void
    {
        // compra 02/05, fecha 15, vence 22, 3x; hoje 31/05 (fatura de maio venceu dia 22)
        $this->travelTo(Carbon::parse('2026-05-31'));

        $card = $this->card(closingDay: 15, dueDay: 22);
        $inst = $this->installment(['purchase_date' => '2026-05-02', 'total_installments' => 3]);

        $this->assertEquals(1, $inst->paidInstallmentsCount($card)); // P1 (venc 22/05) já paga
        $this->assertEquals(2, $inst->currentInstallment($card));    // está na 2ª parcela
    }

    public function test_fatura_do_mes_corrente_ainda_nao_vencida_nao_conta(): void
    {
        // mesma compra, mas hoje 20/05 (antes do vencimento dia 22)
        $this->travelTo(Carbon::parse('2026-05-20'));

        $card = $this->card(closingDay: 15, dueDay: 22);
        $inst = $this->installment(['purchase_date' => '2026-05-02', 'total_installments' => 3]);

        $this->assertEquals(0, $inst->paidInstallmentsCount($card)); // 22/05 ainda não chegou
        $this->assertEquals(1, $inst->currentInstallment($card));    // está na 1ª parcela
    }

    public function test_override_manual_tem_prioridade_sobre_o_tempo(): void
    {
        $this->travelTo(Carbon::parse('2026-05-31'));

        $card = $this->card(closingDay: 3, dueDay: 10);
        $inst = $this->installment(['purchase_date' => '2026-01-01', 'manual_paid_count' => 7]);

        $this->assertEquals(7, $inst->paidInstallmentsCount($card));
        $this->assertEquals(8, $inst->currentInstallment($card));
    }

    public function test_nao_passa_do_total_de_parcelas(): void
    {
        $this->travelTo(Carbon::parse('2030-01-01'));

        $card = $this->card(closingDay: 3, dueDay: 10);
        $inst = $this->installment(['purchase_date' => '2026-01-01', 'total_installments' => 10]);

        $this->assertEquals(10, $inst->paidInstallmentsCount($card));
        $this->assertTrue($inst->isFullyPaid($card));
    }

    // ── getRemainingAmount (saldo devedor = limite ocupado) ────────────

    public function test_saldo_devedor_desconta_parcelas_pagas(): void
    {
        $this->travelTo(Carbon::parse('2026-05-31'));

        $card = $this->card(closingDay: 3, dueDay: 10);
        // 1000 em 10x de 100; 5 pagas (jan–mai) → restam 5 parcelas = 500
        $inst = $this->installment(['purchase_date' => '2026-01-01', 'total_installments' => 10]);

        $this->assertEquals(500.0, $inst->getRemainingAmount($card));
    }

    public function test_saldo_devedor_inicial_e_o_valor_total_da_compra(): void
    {
        // antes de qualquer parcela vencer, o limite ocupado é o valor total da compra
        $card = $this->card(closingDay: 3, dueDay: 10);
        $inst = $this->installment([
            'total_amount'       => 1000,
            'installment_amount' => 100,
            'total_installments' => 10,
            'manual_paid_count'  => 0,
        ]);

        $this->assertEquals(1000.0, $inst->getRemainingAmount($card));
    }

    public function test_saldo_devedor_zero_quando_quitado(): void
    {
        $card = $this->card(closingDay: 3, dueDay: 10);
        $inst = $this->installment(['is_paid_off' => true]);

        $this->assertEquals(0.0, $inst->getRemainingAmount($card));
    }

    public function test_ultima_parcela_calculada_a_partir_da_primeira_fatura(): void
    {
        // compra 31/05 fecha 3 vence 10 (1ª jun) 10x → última = mar/2027
        $card = $this->card(closingDay: 3, dueDay: 10);
        $inst = $this->installment(['purchase_date' => '2026-05-31', 'total_installments' => 10]);

        $this->assertStringContainsStringIgnoringCase('2027', $inst->getLastInstallmentMonth($card));
    }
}
