<?php

namespace Tests\Feature;

use App\Models\ShoppingItem;
use App\Models\ShoppingList;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ItemSuggestionsTest extends TestCase
{
    use RefreshDatabase;

    // ── helpers ──────────────────────────────────────────────────────────────

    private function makeCompletedList(User $user): ShoppingList
    {
        return ShoppingList::forceCreate([
            'user_id'       => $user->id,
            'name'          => 'Lista',
            'shopping_date' => now()->toDateString(),
            'status'        => 'completed',
        ]);
    }

    private function makeItem(ShoppingList $list, string $name, float $price = null, string $unit = null): ShoppingItem
    {
        return ShoppingItem::forceCreate([
            'shopping_list_id' => $list->id,
            'name'             => $name,
            'qty'              => 1,
            'price'            => $price,
            'unit'             => $unit,
        ]);
    }

    // ── testes ───────────────────────────────────────────────────────────────

    /** @test */
    public function nao_autenticado_e_redirecionado_para_login(): void
    {
        $this->get('/listas/sugestoes?q=arroz')
             ->assertRedirect('/login');
    }

    /** @test */
    public function query_menor_que_2_caracteres_retorna_array_vazio(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
             ->getJson('/listas/sugestoes?q=a')
             ->assertExactJson([]);
    }

    /** @test */
    public function sem_query_retorna_array_vazio(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
             ->getJson('/listas/sugestoes')
             ->assertExactJson([]);
    }

    /** @test */
    public function sem_historico_retorna_array_vazio(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
             ->getJson('/listas/sugestoes?q=arroz')
             ->assertExactJson([]);
    }

    /** @test */
    public function ignora_itens_de_listas_abertas(): void
    {
        $user = User::factory()->create();
        $list = ShoppingList::forceCreate([
            'user_id'       => $user->id,
            'name'          => 'Lista aberta',
            'shopping_date' => now()->toDateString(),
            'status'        => 'open',
        ]);
        $this->makeItem($list, 'Arroz', 10.00);

        $this->actingAs($user)
             ->getJson('/listas/sugestoes?q=Arroz')
             ->assertExactJson([]);
    }

    /** @test */
    public function retorna_apenas_dados_do_proprio_usuario(): void
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();

        $listB = $this->makeCompletedList($userB);
        $this->makeItem($listB, 'Arroz', 10.00);

        $this->actingAs($userA)
             ->getJson('/listas/sugestoes?q=Arroz')
             ->assertExactJson([]);
    }

    /** @test */
    public function retorna_no_maximo_5_sugestoes(): void
    {
        $user = User::factory()->create();
        $list = $this->makeCompletedList($user);

        foreach (['Item A', 'Item B', 'Item C', 'Item D', 'Item E', 'Item F'] as $name) {
            $this->makeItem($list, $name);
        }

        $response = $this->actingAs($user)->getJson('/listas/sugestoes?q=Item');
        $this->assertCount(5, $response->json());
    }

    /** @test */
    public function ordena_por_frequencia_decrescente(): void
    {
        $user = User::factory()->create();

        // 'Banana' aparece em 3 listas, 'Batata' em 2 → Banana deve vir primeiro
        for ($i = 0; $i < 3; $i++) {
            $list = $this->makeCompletedList($user);
            $this->makeItem($list, 'Banana');
            if ($i < 2) {
                $this->makeItem($list, 'Batata');
            }
        }

        $result = $this->actingAs($user)->getJson('/listas/sugestoes?q=ba')->json();

        $this->assertCount(2, $result);
        $this->assertEquals('Banana', $result[0]['name']);
        $this->assertEquals('Batata', $result[1]['name']);
    }

    /** @test */
    public function calcula_preco_medio_e_frequencia_corretamente(): void
    {
        $user   = User::factory()->create();
        $prices = [10.00, 12.00, 14.00]; // média = 12.00

        foreach ($prices as $price) {
            $list = $this->makeCompletedList($user);
            $this->makeItem($list, 'Leite', $price, 'L');
        }

        $result = $this->actingAs($user)->getJson('/listas/sugestoes?q=leite')->json();

        $this->assertCount(1, $result);
        $this->assertEquals('Leite', $result[0]['name']);
        $this->assertEquals('L',     $result[0]['unit']);
        $this->assertEquals(12.0,    $result[0]['avg_price']);
        $this->assertEquals(3,       $result[0]['freq']);
    }

    /** @test */
    public function avg_price_e_null_quando_nenhum_item_tem_preco(): void
    {
        $user = User::factory()->create();
        $list = $this->makeCompletedList($user);
        $this->makeItem($list, 'Sal', null); // sem preço

        $result = $this->actingAs($user)->getJson('/listas/sugestoes?q=sal')->json();

        $this->assertCount(1, $result);
        $this->assertNull($result[0]['avg_price']);
    }
}
