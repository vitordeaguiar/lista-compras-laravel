<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ShoppingList;
use Illuminate\Support\Facades\Auth;

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
        $data['user_id'] = Auth::id();

        $list = ShoppingList::create($data);
        return redirect()->route('lists.show', $list)->with('success', 'Lista criada!');
    }

    public function show(ShoppingList $list)
    {
        abort_if($list->user_id !== Auth::id(), 403);
        $list->load('items');
        return view('lists.show', compact('list'));
    }

    public function complete(ShoppingList $list)
    {
        abort_if($list->user_id !== Auth::id(), 403);
        abort_if($list->isCompleted(), 422);

        $total = $list->items->sum(fn($i) => $i->subtotal ?? 0);
        $list->update([
            'status'       => 'completed',
            'total'        => $total,
            'completed_at' => now(),
        ]);

        return redirect()->route('lists.index')
            ->with('success', 'Lista concluída! Veja o histórico para acompanhar.');
    }

    // Reopen a completed list
    public function reopen(ShoppingList $list)
    {
        abort_if($list->user_id !== Auth::id(), 403);
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
        abort_if($list->user_id !== Auth::id(), 403);
        $list->delete();
        return back()->with('success', 'Lista removida.');
    }
}
