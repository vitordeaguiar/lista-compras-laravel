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

        $data['name']    = strip_tags(trim($data['name']));
        $data['notes']   = isset($data['notes']) ? strip_tags(trim($data['notes'])) : null;
        $data['user_id'] = Auth::id();

        $list = ShoppingList::create($data);
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

        // JS já converte "1.234,56" → "1234.56" antes do submit; basta cast direto
        $discount = (float) $request->input('discount', 0);

        $total = $list->items()
            ->where('purchased', true)
            ->get()
            ->sum(fn($item) => ($item->price ?? 0) * $item->qty);

        $finalTotal = max(0, $total - $discount);

        $list->update([
            'status'       => 'completed',
            'total'        => $finalTotal,
            'discount'     => $discount,
            'completed_at' => now(),
        ]);

        return redirect()->route('lists.index')
            ->with('success', 'Lista concluída! Total: R$ ' . number_format($finalTotal, 2, ',', '.'));
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
        return back()->with('success', 'Lista removida.');
    }
}
