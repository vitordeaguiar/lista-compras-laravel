<?php

namespace Tests\Unit;

use App\Services\Receipt\SefazSpReceiptParser;
use Tests\TestCase;

class SefazSpReceiptParserTest extends TestCase
{
    private function parseFixture()
    {
        $html = file_get_contents(__DIR__ . '/../Fixtures/nfce-sp.html');
        return (new SefazSpReceiptParser())->parse($html);
    }

    public function test_extrai_todos_os_itens_com_qtd_unidade_e_preco(): void
    {
        $r = $this->parseFixture();

        $this->assertCount(3, $r->items);
        $this->assertSame(
            ['ARROZ TIO JOAO 5KG', 'FEIJAO CARIOCA 1KG', 'BANANA PRATA'],
            array_column($r->items, 'name'),
        );

        // primeiro item
        $this->assertEquals(1.0, $r->items[0]['qty']);
        $this->assertSame('UN', $r->items[0]['unit']);
        $this->assertEquals(24.90, $r->items[0]['price']);

        // item pesado (quantidade fracionada, unidade KG)
        $this->assertEquals(0.750, $r->items[2]['qty']);
        $this->assertSame('KG', $r->items[2]['unit']);
        $this->assertEquals(5.99, $r->items[2]['price']);
    }

    public function test_extrai_emitente_e_data(): void
    {
        $r = $this->parseFixture();

        $this->assertSame('SUPERMERCADO MODELO LTDA', $r->storeName);
        $this->assertSame('2026-06-28', $r->date);
    }
}
