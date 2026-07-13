<?php

namespace Tests\Feature;

use App\Models\ShoppingItem;
use App\Models\ShoppingList;
use App\Models\User;
use App\Models\UserSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ListItemOrderTest extends TestCase
{
    use RefreshDatabase;

    private function listWithItems(User $user): ShoppingList
    {
        $list = ShoppingList::forceCreate([
            'user_id'       => $user->id,
            'name'          => 'Lista',
            'shopping_date' => now()->toDateString(),
            'status'        => 'open',
        ]);

        // Inseridos nesta ordem (ids crescentes): Uva, Abacaxi, Manga
        // (nomes que não aparecem em placeholders/menus da página)
        foreach (['Uva', 'Abacaxi', 'Manga'] as $name) {
            ShoppingItem::forceCreate([
                'shopping_list_id' => $list->id,
                'name'             => $name,
                'qty'              => 1,
            ]);
        }

        return $list;
    }

    /** @test */
    public function por_padrao_mostra_itens_na_ordem_de_adicao(): void
    {
        $user = User::factory()->create();
        $list = $this->listWithItems($user);

        $this->actingAs($user)
            ->get(route('lists.show', $list))
            ->assertSeeInOrder(['Uva', 'Abacaxi', 'Manga']);
    }

    /** @test */
    public function ordena_alfabeticamente_quando_configurado(): void
    {
        $user = User::factory()->create();
        UserSetting::forceCreate([
            'user_id'         => $user->id,
            'list_item_order' => 'alphabetical',
        ]);
        $list = $this->listWithItems($user);

        $this->actingAs($user)
            ->get(route('lists.show', $list))
            ->assertSeeInOrder(['Abacaxi', 'Manga', 'Uva']);
    }
}
