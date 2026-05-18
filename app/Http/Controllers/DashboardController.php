<?php
namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $user         = Auth::user();
        $now          = now();
        $startOfMonth = $now->copy()->startOfMonth();
        $endOfMonth   = $now->copy()->endOfMonth();

        $listasAbertas = $user->shoppingLists()->where('status', 'open')->count();

        $concluidasNoMes = $user->shoppingLists()
            ->where('status', 'completed')
            ->whereBetween('completed_at', [$startOfMonth, $endOfMonth])
            ->count();

        $gastoNoMes = (float) $user->shoppingLists()
            ->where('status', 'completed')
            ->whereBetween('completed_at', [$startOfMonth, $endOfMonth])
            ->sum('total');

        $itensNoMes = DB::table('shopping_items')
            ->join('shopping_lists', 'shopping_items.shopping_list_id', '=', 'shopping_lists.id')
            ->where('shopping_lists.user_id', $user->id)
            ->where('shopping_items.purchased', true)
            ->whereBetween('shopping_items.updated_at', [$startOfMonth, $endOfMonth])
            ->count();

        $listasEmAndamento = $user->shoppingLists()
            ->where('status', 'open')
            ->with('items')
            ->orderBy('shopping_date')
            ->take(5)
            ->get();

        $listasRecentesRaw = $user->shoppingLists()
            ->orderByDesc('updated_at')
            ->take(10)
            ->get()
            ->map(fn($l) => [
                'icon'    => $l->isCompleted() ? '✅' : '📋',
                'text'    => $l->isCompleted() ? "Lista concluída: {$l->name}" : "Lista criada: {$l->name}",
                'subtext' => '',
                'at'      => $l->isCompleted() ? ($l->completed_at ?? $l->updated_at) : $l->created_at,
            ]);

        $itensRecentesRaw = DB::table('shopping_items')
            ->join('shopping_lists', 'shopping_items.shopping_list_id', '=', 'shopping_lists.id')
            ->where('shopping_lists.user_id', $user->id)
            ->where('shopping_items.purchased', true)
            ->select('shopping_items.name', 'shopping_lists.name as list_name', 'shopping_items.updated_at')
            ->orderByDesc('shopping_items.updated_at')
            ->limit(10)
            ->get()
            ->map(fn($i) => [
                'icon'    => '✓',
                'text'    => "Comprado: {$i->name}",
                'subtext' => $i->list_name,
                'at'      => Carbon::parse($i->updated_at),
            ]);

        $atividadeRecente = $listasRecentesRaw->concat($itensRecentesRaw)
            ->sortByDesc('at')
            ->take(5)
            ->values();

        $gastosPorMes = collect();
        for ($i = 5; $i >= 0; $i--) {
            $mes   = now()->subMonths($i);
            $total = (float) $user->shoppingLists()
                ->where('status', 'completed')
                ->whereYear('completed_at', $mes->year)
                ->whereMonth('completed_at', $mes->month)
                ->sum('total');
            $gastosPorMes->push([
                'label' => $mes->locale('pt_BR')->isoFormat('MMM'),
                'total' => $total,
            ]);
        }
        $maxGasto = max(0.01, $gastosPorMes->max('total'));

        return view('dashboard.index', compact(
            'listasAbertas', 'concluidasNoMes', 'gastoNoMes', 'itensNoMes',
            'listasEmAndamento', 'atividadeRecente', 'gastosPorMes', 'maxGasto'
        ));
    }
}
