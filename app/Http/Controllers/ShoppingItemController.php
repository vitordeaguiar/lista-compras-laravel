<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ShoppingList;
use App\Models\ShoppingItem;
use Illuminate\Support\Facades\Auth;

class ShoppingItemController extends Controller
{
    public function store(Request $request, ShoppingList $list)
    {
        abort_if($list->user_id !== Auth::id(), 403);
        abort_if($list->isCompleted(), 422, 'Lista já concluída.');

        $data = $request->validate([
            'name'  => 'required|string|max:255',
            'unit'  => 'nullable|string|max:50',
            'qty'   => 'nullable|numeric|min:0.001|max:9999',
            'price' => 'nullable|numeric|min:0|max:99999',
        ]);
        $data['shopping_list_id'] = $list->id;
        $data['qty'] = $data['qty'] ?? 1;

        ShoppingItem::create($data);
        return back()->with('success', 'Item adicionado!');
    }

    public function update(Request $request, ShoppingList $list, ShoppingItem $item)
    {
        abort_if($list->user_id !== Auth::id(), 403);
        abort_if($item->shopping_list_id !== $list->id, 403);

        $data = $request->validate([
            'price' => 'nullable|numeric|min:0|max:99999',
            'qty'   => 'nullable|numeric|min:0.001|max:9999',
        ]);
        $item->update($data);
        return back();
    }

    // Toggle purchase status, optionally saving price at same time
    public function toggle(Request $request, ShoppingList $list, ShoppingItem $item)
    {
        abort_if($list->user_id !== Auth::id(), 403);
        abort_if($item->shopping_list_id !== $list->id, 403);

        $newPurchased = !$item->purchased;
        $updateData   = ['purchased' => $newPurchased];

        // If a price was sent together with the toggle, save it
        if ($request->filled('price')) {
            $price = (float) str_replace(',', '.', $request->price);
            if ($price >= 0) {
                $updateData['price'] = $price;
            }
        }

        $item->update($updateData);
        return back();
    }

    public function destroy(ShoppingList $list, ShoppingItem $item)
    {
        abort_if($list->user_id !== Auth::id(), 403);
        abort_if($item->shopping_list_id !== $list->id, 403);

        $item->delete();
        return back()->with('success', 'Item removido.');
    }
}
