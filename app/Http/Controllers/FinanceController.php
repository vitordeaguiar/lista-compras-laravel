<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class FinanceController extends Controller
{
    public function index(Request $request)
    {
        $dateFrom = $request->date_from ?? now()->startOfMonth()->toDateString();
        $dateTo   = $request->date_to   ?? now()->toDateString();

        // All completed lists in period
        $lists = Auth::user()->shoppingLists()
            ->where('status', 'completed')
            ->whereDate('shopping_date', '>=', $dateFrom)
            ->whereDate('shopping_date', '<=', $dateTo)
            ->with('items')
            ->get();

        $listIds = $lists->pluck('id');

        // Total spent
        $totalGasto = $lists->sum('total');

        // Items most purchased (by times appeared across lists)
        $topItems = DB::table('shopping_items')
            ->whereIn('shopping_list_id', $listIds)
            ->where('purchased', true)
            ->select(
                DB::raw('LOWER(name) as item_name'),
                DB::raw('COUNT(*) as vezes'),
                DB::raw('SUM(qty) as total_qty'),
                DB::raw('SUM(COALESCE(price * qty, 0)) as total_gasto'),
                DB::raw('AVG(price) as preco_medio')
            )
            ->groupBy(DB::raw('LOWER(name)'))
            ->orderByDesc('vezes')
            ->limit(20)
            ->get();

        // Spending per list (for chart)
        $gastosPorLista = $lists->sortBy('shopping_date')->map(fn($l) => [
            'label' => $l->shopping_date->format('d/m'),
            'name'  => $l->name,
            'total' => $l->total ?? 0,
        ])->values();

        // Spending per month summary
        $gastosPorMes = Auth::user()->shoppingLists()
            ->where('status', 'completed')
            ->whereDate('shopping_date', '>=', $dateFrom)
            ->whereDate('shopping_date', '<=', $dateTo)
            ->select(
                DB::raw("TO_CHAR(shopping_date, 'MM/YYYY') as mes"),
                DB::raw('SUM(total) as total'),
                DB::raw('COUNT(*) as num_listas')
            )
            ->groupBy(DB::raw("TO_CHAR(shopping_date, 'MM/YYYY')"))
            ->orderBy(DB::raw("MIN(shopping_date)"))
            ->get();

        return view('finance.index', compact(
            'topItems', 'totalGasto', 'gastosPorLista',
            'gastosPorMes', 'dateFrom', 'dateTo', 'listIds'
        ));
    }
}
