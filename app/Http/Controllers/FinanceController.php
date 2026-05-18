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

        $lists = Auth::user()->shoppingLists()
            ->where('status', 'completed')
            ->whereDate('shopping_date', '>=', $dateFrom)
            ->whereDate('shopping_date', '<=', $dateTo)
            ->with('items')
            ->get();

        $listIds    = $lists->pluck('id');
        $totalGasto = $lists->sum('total');

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

        $topItemName = $topItems->isNotEmpty() ? ucfirst($topItems->first()->item_name) : '—';

        $itensUnicos = $listIds->isNotEmpty()
            ? (int) DB::table('shopping_items')
                ->whereIn('shopping_list_id', $listIds)
                ->where('purchased', true)
                ->selectRaw('COUNT(DISTINCT LOWER(name)) as cnt')
                ->value('cnt')
            : 0;

        $gastosPorLista = $lists->sortBy('shopping_date')->map(fn($l) => [
            'label' => $l->shopping_date->format('d/m'),
            'name'  => $l->name,
            'total' => $l->total ?? 0,
        ])->values();

        $gastosPorMes = Auth::user()->shoppingLists()
            ->where('status', 'completed')
            ->whereDate('shopping_date', '>=', $dateFrom)
            ->whereDate('shopping_date', '<=', $dateTo)
            ->select(
                DB::raw("DATE_FORMAT(shopping_date, '%m/%Y') as mes"),
                DB::raw('SUM(total) as total'),
                DB::raw('COUNT(*) as num_listas')
            )
            ->groupBy(DB::raw("DATE_FORMAT(shopping_date, '%m/%Y')"))
            ->orderBy(DB::raw("MIN(shopping_date)"))
            ->get();

        return view('finance.index', compact(
            'topItems', 'totalGasto', 'gastosPorLista',
            'gastosPorMes', 'dateFrom', 'dateTo', 'listIds',
            'topItemName', 'itensUnicos'
        ));
    }
}
