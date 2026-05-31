<?php

namespace Tests\Feature\Security;

use App\Models\ShoppingItem;
use App\Models\ShoppingList;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ShoppingAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    private User $userA;
    private User $userB;
    private ShoppingList $list;

    protected function setUp(): void
    {
        parent::setUp();
        $this->userA = User::factory()->create();
        $this->userB = User::factory()->create();
        $this->list  = ShoppingList::forceCreate([
            'user_id'       => $this->userA->id,
            'name'          => 'Lista do userA',
            'shopping_date' => now()->toDateString(),
            'status'        => 'open',
        ]);
    }

    public function test_show_retorna_403_para_lista_alheia(): void
    {
        $this->actingAs($this->userB)
            ->get(route('lists.show', $this->list))
            ->assertStatus(403);
    }

    public function test_complete_retorna_403_para_lista_alheia(): void
    {
        $this->actingAs($this->userB)
            ->patch(route('lists.complete', $this->list), ['discount' => 0])
            ->assertStatus(403);
    }

    public function test_reopen_retorna_403_para_lista_alheia(): void
    {
        $this->list->update(['status' => 'completed']);

        $this->actingAs($this->userB)
            ->patch(route('lists.reopen', $this->list))
            ->assertStatus(403);
    }

    public function test_destroy_retorna_403_para_lista_alheia(): void
    {
        $this->actingAs($this->userB)
            ->delete(route('lists.destroy', $this->list))
            ->assertStatus(403);
    }

    public function test_store_item_retorna_403_para_lista_alheia(): void
    {
        $this->actingAs($this->userB)
            ->post(route('items.store', $this->list), ['name' => 'Item invasor', 'qty' => 1])
            ->assertStatus(403);
    }

    public function test_update_item_retorna_403_para_item_de_lista_alheia(): void
    {
        $item = ShoppingItem::forceCreate([
            'shopping_list_id' => $this->list->id,
            'name'             => 'Item',
            'qty'              => 1,
        ]);

        $this->actingAs($this->userB)
            ->patch(route('items.update', [$this->list, $item]), ['qty' => 5])
            ->assertStatus(403);
    }

    public function test_toggle_item_retorna_403_para_item_de_lista_alheia(): void
    {
        $item = ShoppingItem::forceCreate([
            'shopping_list_id' => $this->list->id,
            'name'             => 'Item',
            'qty'              => 1,
        ]);

        $this->actingAs($this->userB)
            ->patch(route('items.toggle', [$this->list, $item]))
            ->assertStatus(403);
    }

    public function test_destroy_item_retorna_403_para_item_de_lista_alheia(): void
    {
        $item = ShoppingItem::forceCreate([
            'shopping_list_id' => $this->list->id,
            'name'             => 'Item',
            'qty'              => 1,
        ]);

        $this->actingAs($this->userB)
            ->delete(route('items.destroy', [$this->list, $item]))
            ->assertStatus(403);
    }
}
