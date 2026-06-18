<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Models\ShoppingList;

class ShoppingListController extends Controller
{
    public function index()
    {
        $openLists = Auth::user()->shoppingLists()
            ->where('status', 'open')
            ->with('items')
            ->orderBy('shopping_date', 'asc')
            ->get();

        return view('lists.index', compact('openLists'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'          => 'required|string|max:255',
            'shopping_date' => 'required|date',
            'notes'         => 'nullable|string|max:500',
        ]);

        $data['name']  = strip_tags(trim($data['name']));
        $data['notes'] = isset($data['notes']) ? strip_tags(trim($data['notes'])) : null;

        $list = new ShoppingList($data);
        $list->user_id = Auth::id();
        $list->save();

        return redirect()->route('lists.show', $list)->with('success', 'Lista criada!');
    }

    public function show(ShoppingList $list)
    {
        if ($list->user_id !== Auth::id()) {
            Log::warning('Acesso não autorizado a lista', [
                'user_id' => Auth::id(),
                'list_id' => $list->id,
                'ip'      => request()->ip(),
                'at'      => now()->toIso8601String(),
            ]);
            abort(403);
        }

        $list->load('items');
        return view('lists.show', compact('list'));
    }

    public function complete(Request $request, ShoppingList $list)
    {
        if ($list->user_id !== Auth::id()) {
            Log::warning('Tentativa não autorizada de concluir lista', [
                'user_id' => Auth::id(),
                'list_id' => $list->id,
                'ip'      => request()->ip(),
                'at'      => now()->toIso8601String(),
            ]);
            abort(403);
        }
        abort_if($list->isCompleted(), 422);

        // JS já converte "1.234,56" → "1234.56" antes do submit; em branco → null
        $data = $request->validate(['total' => 'nullable|numeric|min:0']);

        // Soma dos itens marcados como comprados (preço × qtd)
        $sumItems = $list->items()
            ->where('purchased', true)
            ->get()
            ->sum(fn($item) => ($item->price ?? 0) * $item->qty);

        // Valor total informado pelo usuário; em branco, usa a soma dos itens.
        $total = isset($data['total']) ? max(0, (float) $data['total']) : (float) $sumItems;

        // Se o total ficou abaixo da soma dos itens, a diferença foi desconto.
        // Se ficou acima (preços incompletos ou erro), não há desconto.
        $discount = $total < $sumItems ? round($sumItems - $total, 2) : 0;

        $list->update([
            'status'       => 'completed',
            'total'        => $total,
            'discount'     => $discount,
            'completed_at' => now(),
        ]);

        return redirect()->route('lists.index')
            ->with('success', 'Lista concluída! Total: R$ ' . number_format($total, 2, ',', '.'));
    }

    public function reopen(ShoppingList $list)
    {
        if ($list->user_id !== Auth::id()) {
            Log::warning('Tentativa não autorizada de reabrir lista', [
                'user_id' => Auth::id(),
                'list_id' => $list->id,
                'ip'      => request()->ip(),
                'at'      => now()->toIso8601String(),
            ]);
            abort(403);
        }
        abort_if($list->isOpen(), 422);

        $list->update([
            'status'       => 'open',
            'total'        => null,
            'discount'     => 0,
            'completed_at' => null,
        ]);

        return redirect()->route('lists.show', $list)
            ->with('success', 'Lista reaberta! Você pode continuar editando.');
    }

    public function destroy(ShoppingList $list)
    {
        if ($list->user_id !== Auth::id()) {
            Log::warning('Tentativa não autorizada de excluir lista', [
                'user_id' => Auth::id(),
                'list_id' => $list->id,
                'ip'      => request()->ip(),
                'at'      => now()->toIso8601String(),
            ]);
            abort(403);
        }

        $list->delete();
        return redirect()->route('lists.index')->with('success', 'Lista removida.');
    }
}
