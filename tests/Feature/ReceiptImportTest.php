<?php

namespace Tests\Feature;

use App\Models\ShoppingList;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ReceiptImportTest extends TestCase
{
    use RefreshDatabase;

    private const ITEM_LINE = 'ARROZ TIO JOAO 5KG   1 UN X 24,90   24,90';

    private function fixtureHtml(): string
    {
        return file_get_contents(__DIR__ . '/../Fixtures/nfce-sp.html');
    }

    // ── autenticação ───────────────────────────────────────────────────

    public function test_rotas_de_cupom_exigem_autenticacao(): void
    {
        $this->get('/listas/cupom')->assertRedirect('/login');
        $this->post('/listas/cupom/extrair', ['source' => 'ocr', 'text' => 'x'])->assertRedirect('/login');
        $this->post('/listas/cupom', [])->assertRedirect('/login');
    }

    // ── extração ───────────────────────────────────────────────────────

    public function test_extrai_itens_a_partir_de_texto_ocr(): void
    {
        $user = User::factory()->create();

        $json = $this->actingAs($user)
            ->postJson('/listas/cupom/extrair', ['source' => 'ocr', 'text' => self::ITEM_LINE])
            ->assertOk()
            ->json();

        $this->assertSame('ARROZ TIO JOAO 5KG', $json['items'][0]['name']);
        $this->assertSame('UN', $json['items'][0]['unit']);
        $this->assertEquals(1, $json['items'][0]['qty']);
        $this->assertEquals(24.90, $json['items'][0]['price']);
    }

    public function test_extrai_itens_a_partir_do_qr_da_sefaz_sp(): void
    {
        Http::fake(['*' => Http::response($this->fixtureHtml(), 200)]);
        $user = User::factory()->create();

        $this->actingAs($user)
            ->postJson('/listas/cupom/extrair', [
                'source' => 'qr',
                'url'    => 'https://www.nfce.fazenda.sp.gov.br/NFCeConsultaPublica/Paginas/ConsultaQRCode.aspx?p=CHAVE|2|1|1|HASH',
            ])
            ->assertOk()
            ->assertJsonCount(3, 'items')
            ->assertJsonPath('name', 'SUPERMERCADO MODELO LTDA')
            ->assertJsonPath('date', '2026-06-28');
    }

    public function test_qr_de_host_fora_da_sefaz_sp_e_rejeitado(): void
    {
        Http::fake();
        $user = User::factory()->create();

        $this->actingAs($user)
            ->postJson('/listas/cupom/extrair', [
                'source' => 'qr',
                'url'    => 'https://malicioso.example.com/consulta',
            ])
            ->assertStatus(422);

        Http::assertNothingSent(); // anti-SSRF: nem chega a fazer a requisição
    }

    public function test_cupom_sem_itens_retorna_422(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->postJson('/listas/cupom/extrair', ['source' => 'ocr', 'text' => 'OBRIGADO PELA PREFERENCIA'])
            ->assertStatus(422);
    }

    // ── criação da lista ───────────────────────────────────────────────

    public function test_cria_lista_aberta_com_itens_comprados(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/listas/cupom', [
            'name'          => 'SUPERMERCADO MODELO LTDA',
            'shopping_date' => '2026-06-28',
            'items'         => [
                ['name' => 'Arroz Tio Joao 5kg', 'unit' => 'UN', 'qty' => 1, 'price' => 24.90],
                ['name' => 'Feijao Carioca 1kg', 'unit' => 'UN', 'qty' => 2, 'price' => 8.50],
            ],
        ]);

        $list = ShoppingList::where('user_id', $user->id)->firstOrFail();
        $response->assertRedirect(route('lists.show', $list));

        $this->assertSame('open', $list->status);
        $this->assertCount(2, $list->items);

        $arroz = $list->items->firstWhere('name', 'Arroz Tio Joao 5kg');
        $this->assertTrue($arroz->purchased);
        $this->assertEquals(24.90, $arroz->price);
        $this->assertEquals(1.0, $arroz->qty);
    }

    public function test_nao_cria_lista_sem_itens(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post('/listas/cupom', ['name' => 'Mercado', 'shopping_date' => '2026-06-28'])
            ->assertSessionHasErrors('items');

        $this->assertSame(0, ShoppingList::count());
    }
}
