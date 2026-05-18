<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Models\ShoppingList;
use App\Models\ShoppingItem;

class ShoppingItemController extends Controller
{
    public function store(Request $request, ShoppingList $list)
    {
        if ($list->user_id !== Auth::id()) {
            Log::warning('Tentativa não autorizada de adicionar item à lista', [
                'user_id' => Auth::id(),
                'list_id' => $list->id,
                'ip'      => request()->ip(),
                'at'      => now()->toIso8601String(),
            ]);
            abort(403);
        }
        abort_if($list->isCompleted(), 422, 'Lista já concluída.');

        $data = $request->validate([
            'name'  => 'required|string|max:255',
            'unit'  => 'nullable|string|max:50',
            'qty'   => 'nullable|numeric|min:0.001|max:9999',
            'price' => 'nullable|numeric|min:0|max:99999',
        ]);

        $data['name']             = strip_tags(trim($data['name']));
        $data['unit']             = isset($data['unit']) ? strip_tags(trim($data['unit'])) : null;
        $data['qty']              = $data['qty'] ?? 1;
        $data['shopping_list_id'] = $list->id;

        ShoppingItem::create($data);
        return back()->with('success', 'Item adicionado!');
    }

    public function update(Request $request, ShoppingList $list, ShoppingItem $item)
    {
        if ($list->user_id !== Auth::id() || $item->shopping_list_id !== $list->id) {
            Log::warning('Tentativa não autorizada de editar item', [
                'user_id' => Auth::id(),
                'list_id' => $list->id,
                'item_id' => $item->id,
                'ip'      => request()->ip(),
                'at'      => now()->toIso8601String(),
            ]);
            abort(403);
        }

        $data = $request->validate([
            'price' => 'nullable|numeric|min:0|max:99999',
            'qty'   => 'nullable|numeric|min:0.001|max:9999',
        ]);
        $item->update($data);
        return back();
    }

    public function toggle(Request $request, ShoppingList $list, ShoppingItem $item)
    {
        if ($list->user_id !== Auth::id() || $item->shopping_list_id !== $list->id) {
            Log::warning('Tentativa não autorizada de alternar item', [
                'user_id' => Auth::id(),
                'list_id' => $list->id,
                'item_id' => $item->id,
                'ip'      => request()->ip(),
                'at'      => now()->toIso8601String(),
            ]);
            abort(403);
        }

        $newPurchased = !$item->purchased;
        $updateData   = ['purchased' => $newPurchased];

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
        if ($list->user_id !== Auth::id() || $item->shopping_list_id !== $list->id) {
            Log::warning('Tentativa não autorizada de excluir item', [
                'user_id' => Auth::id(),
                'list_id' => $list->id,
                'item_id' => $item->id,
                'ip'      => request()->ip(),
                'at'      => now()->toIso8601String(),
            ]);
            abort(403);
        }

        $item->delete();
        return back()->with('success', 'Item removido.');
    }
}
