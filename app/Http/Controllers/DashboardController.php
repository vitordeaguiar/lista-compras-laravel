<?php
namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $user  = Auth::user();
        $month = now()->format('Y-m');

        // LISTAS ABERTAS
        $openLists = \App\Models\ShoppingList::where('user_id', $user->id)
            ->where('status', 'open')
            ->with('items')
            ->orderByDesc('created_at')
            ->get();

        // FINANCEIRO DO MÊS
        $totalIncome = \App\Models\FinancialIncome::where('user_id', $user->id)
            ->where('month', $month)->sum('amount');

        $totalFixed = \App\Models\FinancialFixedPayment::where('user_id', $user->id)
            ->where('month', $month)->sum('amount');

        $totalVariable = \App\Models\FinancialVariableCost::where('user_id', $user->id)
            ->where('month', $month)->sum('amount');

        $totalSupermarket = \App\Models\ShoppingList::where('user_id', $user->id)
            ->where('status', 'completed')
            ->whereRaw("DATE_FORMAT(updated_at, '%Y-%m') = ?", [$month])
            ->sum('total');

        $totalInvestment = \App\Models\FinancialInvestmentEntry::where('user_id', $user->id)
            ->where('month', $month)->sum('amount');

        // CARTÕES — calcula fatura do mês a partir das parcelas ativas
        $monthDate   = Carbon::parse($month . '-01');
        $creditCards = \App\Models\CreditCard::where('user_id', $user->id)
            ->where('is_active', true)
            ->with(['installments' => fn($q) => $q->where('is_paid_off', false)])
            ->get();
        $totalCreditCard = (float) $creditCards->sum(function ($card) use ($monthDate) {
            return $card->installments
                ->filter(fn($inst) => $inst->isActiveInMonth($monthDate, $card))
                ->sum('installment_amount');
        });

        $totalOut  = $totalFixed + $totalVariable + $totalSupermarket + $totalInvestment + $totalCreditCard;
        $balance   = $totalIncome - $totalOut;
        $budgetPct = $totalIncome > 0 ? min(100, round(($totalOut / $totalIncome) * 100)) : 0;

        // CONTAS A VENCER (próximas, não pagas)
        $today = now()->day;
        $upcomingBills = \App\Models\FinancialFixedPayment::where('user_id', $user->id)
            ->where('month', $month)
            ->where('paid', false)
            ->with('fixedCost')
            ->get()
            ->map(function ($p) use ($today) {
                $due = $p->fixedCost->due_day ?? 1;
                $p->days_until  = $due - $today;
                $p->due_day_num = $due;
                return $p;
            })
            ->sortBy('days_until')
            ->take(3);

        // ATIVIDADE RECENTE
        $recentActivity = collect();
        \App\Models\ShoppingItem::whereHas('shoppingList', fn($q) =>
            $q->where('user_id', $user->id))
            ->where('purchased', true)
            ->orderByDesc('updated_at')->limit(2)->get()
            ->each(fn($i) => $recentActivity->push([
                'color' => 'accent',
                'text'  => $i->name . ' marcado como comprado',
                'value' => $i->price ? 'R$ ' . number_format($i->price, 2, ',', '.') : null,
                'time'  => $i->updated_at->diffForHumans(),
            ]));
        \App\Models\ShoppingList::where('user_id', $user->id)
            ->orderByDesc('created_at')->limit(2)->get()
            ->each(fn($l) => $recentActivity->push([
                'color' => 'blue',
                'text'  => 'Lista ' . $l->name . ' criada',
                'value' => null,
                'time'  => $l->created_at->diffForHumans(),
            ]));
        $upcomingBills->take(1)->each(fn($b) => $recentActivity->push([
            'color' => 'danger',
            'text'  => ($b->fixedCost->name ?? 'Conta') . ' vence em breve',
            'value' => 'R$ ' . number_format($b->amount, 2, ',', '.'),
            'time'  => 'dia ' . $b->due_day_num . '/' . now()->format('m'),
        ]));
        $recentActivity = $recentActivity->take(4)->values();

        // TOP GASTOS
        $allExpenses = collect();
        \App\Models\FinancialFixedPayment::where('user_id', $user->id)
            ->where('month', $month)->with('fixedCost')->get()
            ->each(fn($p) => $allExpenses->push([
                'name'   => $p->fixedCost->name ?? 'Custo',
                'amount' => (float) $p->amount,
                'icon'   => $p->fixedCost->icon ?? '💰',
                'type'   => 'Fixo',
            ]));
        \App\Models\FinancialVariableCost::where('user_id', $user->id)
            ->where('month', $month)->get()
            ->each(fn($v) => $allExpenses->push([
                'name'   => $v->name,
                'amount' => (float) $v->amount,
                'icon'   => '🎲',
                'type'   => ucfirst($v->category),
            ]));
        $topExpenses = $allExpenses->sortByDesc('amount')->take(3)->values()
            ->map(function ($e) use ($totalOut) {
                $e['pct'] = $totalOut > 0 ? round(($e['amount'] / $totalOut) * 100, 1) : 0;
                return $e;
            });

        // GRÁFICO FINANCEIRO — mensal (últimos 6 meses)
        $finMonthly = [];
        for ($i = 5; $i >= 0; $i--) {
            $m     = now()->subMonths($i)->format('Y-m');
            $label = now()->subMonths($i)->locale('pt_BR')->isoFormat('MMM');
            $mDate = Carbon::parse($m . '-01');
            $inc   = (float) \App\Models\FinancialIncome::where('user_id', $user->id)->where('month', $m)->sum('amount');
            $out   = (float) \App\Models\FinancialFixedPayment::where('user_id', $user->id)->where('month', $m)->sum('amount')
                   + (float) \App\Models\FinancialVariableCost::where('user_id', $user->id)->where('month', $m)->sum('amount')
                   + (float) $creditCards->sum(fn($card) => $card->installments
                       ->filter(fn($inst) => $inst->isActiveInMonth($mDate, $card))
                       ->sum('installment_amount'));
            $finMonthly[] = ['label' => $label, 'month' => $m, 'income' => $inc, 'expense' => $out];
        }

        // GRÁFICO MERCADO — mensal (últimos 6 meses)
        $mktMonthly = [];
        for ($i = 5; $i >= 0; $i--) {
            $m     = now()->subMonths($i)->format('Y-m');
            $label = now()->subMonths($i)->locale('pt_BR')->isoFormat('MMM');
            $val   = (float) \App\Models\ShoppingList::where('user_id', $user->id)
                ->where('status', 'completed')
                ->whereRaw("DATE_FORMAT(updated_at, '%Y-%m') = ?", [$m])
                ->sum('total');
            $mktMonthly[] = ['label' => $label, 'month' => $m, 'value' => $val];
        }

        // DADOS SEMANAIS — todos os meses para o JS
        $allFinWeekly = [];
        $allMktWeekly = [];
        for ($i = 5; $i >= 0; $i--) {
            $m = now()->subMonths($i)->format('Y-m');
            $allFinWeekly[$m] = $this->getWeeklyFinancial($user->id, $m);
            $allMktWeekly[$m] = $this->getWeeklyMarket($user->id, $m);
        }

        return view('dashboard.index', compact(
            'openLists', 'recentActivity',
            'totalIncome', 'totalFixed', 'totalVariable',
            'totalSupermarket', 'totalInvestment', 'totalCreditCard', 'totalOut',
            'balance', 'budgetPct',
            'upcomingBills', 'topExpenses',
            'finMonthly', 'mktMonthly',
            'allFinWeekly', 'allMktWeekly',
            'month'
        ));
    }

    private function getWeeklyFinancial($userId, $month)
    {
        $weeks = $this->getWeeksOfMonth($month);
        return array_map(function ($week) use ($userId) {
            $val = (float) \App\Models\FinancialFixedPayment::where('user_id', $userId)
                ->where('paid', true)
                ->whereBetween('paid_at', [$week['start'] . ' 00:00:00', $week['end'] . ' 23:59:59'])
                ->sum('amount');
            $val += (float) \App\Models\FinancialVariableCost::where('user_id', $userId)
                ->whereBetween('spent_at', [$week['start'], $week['end']])
                ->sum('amount');
            return ['label' => $week['label'], 'value' => $val];
        }, $weeks);
    }

    private function getWeeklyMarket($userId, $month)
    {
        $weeks = $this->getWeeksOfMonth($month);
        return array_map(function ($week) use ($userId) {
            $val = (float) \App\Models\ShoppingList::where('user_id', $userId)
                ->where('status', 'completed')
                ->whereBetween('updated_at', [$week['start'] . ' 00:00:00', $week['end'] . ' 23:59:59'])
                ->sum('total');
            return ['label' => $week['label'], 'value' => $val];
        }, $weeks);
    }

    private function getWeeksOfMonth($month)
    {
        $start   = Carbon::parse($month . '-01');
        $end     = $start->copy()->endOfMonth();
        $weeks   = [];
        $current = $start->copy()->startOfWeek(Carbon::MONDAY);
        if ($current->lt($start)) $current = $start->copy();
        while ($current->lte($end)) {
            $weekEnd = $current->copy()->endOfWeek(Carbon::SUNDAY);
            if ($weekEnd->gt($end)) $weekEnd = $end->copy();
            $weeks[] = [
                'start' => $current->format('Y-m-d'),
                'end'   => $weekEnd->format('Y-m-d'),
                'label' => $current->format('d') . '-' . $weekEnd->format('d'),
            ];
            $current = $weekEnd->copy()->addDay();
        }
        return $weeks;
    }
}
