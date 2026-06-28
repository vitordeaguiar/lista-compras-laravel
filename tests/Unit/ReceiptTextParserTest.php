<?php

namespace Tests\Unit;

use App\Services\Receipt\ReceiptTextParser;
use Tests\TestCase;

class ReceiptTextParserTest extends TestCase
{
    private function parse(string $text)
    {
        return (new ReceiptTextParser())->parse($text);
    }

    // ── extração de item ───────────────────────────────────────────────

    public function test_extrai_nome_qtd_unidade_e_preco_de_uma_linha_de_item(): void
    {
        $r = $this->parse('ARROZ TIO JOAO 5KG   1 UN X 24,90   24,90');

        $this->assertCount(1, $r->items);
        $this->assertSame('ARROZ TIO JOAO 5KG', $r->items[0]['name']);
        $this->assertEquals(1.0, $r->items[0]['qty']);
        $this->assertSame('UN', $r->items[0]['unit']);
        $this->assertEquals(24.90, $r->items[0]['price']);
    }

    public function test_converte_formatos_numericos_br(): void
    {
        // decimal com vírgula na quantidade (item pesado)
        $pesado = $this->parse('PICANHA PECA   1,250 KG X 79,90   99,88');
        $this->assertEquals(1.250, $pesado->items[0]['qty']);
        $this->assertSame('KG', $pesado->items[0]['unit']);
        $this->assertEquals(79.90, $pesado->items[0]['price']);

        // milhar com ponto + decimal com vírgula no preço
        $caro = $this->parse('GELADEIRA FROST   1 UN X 1.234,56   1.234,56');
        $this->assertEquals(1234.56, $caro->items[0]['price']);
    }

    public function test_ignora_linhas_de_ruido_e_extrai_so_os_itens(): void
    {
        $cupom = <<<TXT
        SUPERMERCADO BOM PRECO LTDA
        CNPJ 12.345.678/0001-90
        CUPOM FISCAL ELETRONICO - NFC-e
        ARROZ TIO JOAO 5KG   1 UN X 24,90   24,90
        FEIJAO CARIOCA 1KG   2 UN X 8,50   17,00
        COCA COLA 2L   1 UN X 9,99   9,99
        Qtde total de itens: 3
        VALOR TOTAL R\$ 51,89
        FORMA PAGAMENTO
        Cartao de Credito   51,89
        TROCO   0,00
        TXT;

        $r = $this->parse($cupom);

        $this->assertCount(3, $r->items);
        $this->assertSame(
            ['ARROZ TIO JOAO 5KG', 'FEIJAO CARIOCA 1KG', 'COCA COLA 2L'],
            array_column($r->items, 'name'),
        );
        $this->assertEquals(2.0, $r->items[1]['qty']);
        $this->assertEquals(8.50, $r->items[1]['price']);
    }

    // ── metadados (mercado e data) ─────────────────────────────────────

    public function test_detecta_nome_do_mercado_e_data_de_emissao(): void
    {
        $cupom = <<<TXT
        MERCADO SANTA LUZIA LTDA
        Rua das Flores, 100
        CNPJ 11.222.333/0001-44
        Emissao 28/06/2026 19:45:00
        PAO FRANCES   0,500 KG X 12,00   6,00
        TXT;

        $r = $this->parse($cupom);

        $this->assertSame('MERCADO SANTA LUZIA LTDA', $r->storeName);
        $this->assertSame('2026-06-28', $r->date);
    }

    public function test_sem_mercado_e_sem_data_deixa_metadados_nulos(): void
    {
        $r = $this->parse('PAO FRANCES   0,500 KG X 12,00   6,00');

        $this->assertNull($r->storeName);
        $this->assertNull($r->date);
    }

    public function test_texto_sem_itens_retorna_lista_vazia(): void
    {
        $r = $this->parse("OBRIGADO PELA PREFERENCIA\nVOLTE SEMPRE");

        $this->assertSame([], $r->items);
    }
}
