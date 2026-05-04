<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ShoppingList;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class ShoppingListController extends Controller
{
    public function index()
    {
        $openLists = Auth::user()->shoppingLists()
            ->where('status', 'open')
            ->with('items')
            ->orderBy('shopping_date', 'asc')
            ->get();

        $totalGasto = $openLists->sum(fn ($list) => $list->computed_total);

        return view('lists.index', compact('openLists', 'totalGasto'));
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
        $list->update(['status' => 'completed', 'total' => $total, 'completed_at' => now()]);

        return redirect()->route('lists.index')->with('success', 'Lista concluída! Veja o histórico para acompanhar.');
    }

    public function destroy(Request $request, ShoppingList $list)
    {
        abort_if($list->user_id !== Auth::id(), 403);

        $showUrl = route('lists.show', $list);
        $list->delete();

        $previous = url()->previous();
        $appUrl = rtrim((string) config('app.url'), '/');

        if ($this->sameHttpPath($previous, $showUrl)) {
            return redirect()->route('lists.index')->with('success', 'Lista removida.');
        }

        if ($appUrl !== '' && Str::startsWith($previous, $appUrl)) {
            return redirect()->to($previous)->with('success', 'Lista removida.');
        }

        return redirect()->route('lists.index')->with('success', 'Lista removida.');
    }

    private function sameHttpPath(string $a, string $b): bool
    {
        $pa = rtrim((string) parse_url($a, PHP_URL_PATH), '/') ?: '';
        $pb = rtrim((string) parse_url($b, PHP_URL_PATH), '/') ?: '';

        return $pa !== '' && $pa === $pb;
    }
}
