<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HistoryController extends Controller
{
    public function index(Request $request)
    {
        $query = Auth::user()->shoppingLists()
            ->where('status', 'completed')
            ->with('items');

        if ($request->filled('date_from')) {
            $query->whereDate('shopping_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('shopping_date', '<=', $request->date_to);
        }
        if ($request->filled('search')) {
            $query->where('name', 'ilike', '%' . $request->search . '%');
        }

        $lists = $query->orderBy('shopping_date', 'desc')->paginate(15)->withQueryString();

        $totalGasto = $query->sum('total');

        return view('history.index', compact('lists', 'totalGasto'));
    }
}
