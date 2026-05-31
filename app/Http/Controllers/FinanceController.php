<?php
namespace App\Http\Controllers;

use App\Models\FinancialIncome;
use App\Models\FinancialFixedCost;
use App\Models\FinancialFixedPayment;
use App\Models\FinancialVariableCost;
use App\Models\FinancialInvestment;
use App\Models\FinancialInvestmentEntry;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class FinanceController extends Controller
{
    public function index(Request $request)
    {
        $month = $request->get('month', now()->format('Y-m'));
        $user  = Auth::user();

        // Entradas
        $incomes     = FinancialIncome::where('user_id', $user->id)->where('month', $month)->get();
        $totalIncome = $incomes->sum('amount');

        // Custos fixos — cria payments do mês se não existirem
        $fixedCosts = FinancialFixedCost::where('user_id', $user->id)->get();
        foreach ($fixedCosts as $cost) {
            FinancialFixedPayment::firstOrCreate(
                ['fixed_cost_id' => $cost->id, 'month' => $month, 'user_id' => $user->id],
                ['amount' => $cost->amount, 'paid' => false]
            );
        }
        $fixedPayments = FinancialFixedPayment::where('user_id', $user->id)
            ->where('month', $month)->with('fixedCost')->get();
        $totalFixed = $fixedPayments->sum('amount');
        $paidFixed  = $fixedPayments->where('paid', true)->count();

        // Variáveis
        $variables     = FinancialVariableCost::where('user_id', $user->id)->where('month', $month)->get();
        $totalVariable = $variables->sum('amount');

        // Supermercado (das listas concluídas)
        $supermarket = \App\Models\ShoppingList::where('user_id', $user->id)
            ->where('status', 'completed')
            ->whereRaw("DATE_FORMAT(completed_at, '%Y-%m') = ?", [$month])
            ->sum('total');

        // Investimentos
        $investments = FinancialInvestment::where('user_id', $user->id)
            ->with(['entries' => fn($q) => $q->where('month', $month)])
            ->withSum('entries', 'amount')
            ->get();
        $totalInvestment = FinancialInvestmentEntry::where('user_id', $user->id)
            ->where('month', $month)->sum('amount');

        // Cartões de crédito
        $creditCardsTotal = \App\Models\CreditCardPayment::where('user_id', $user->id)
            ->where('month', $month)->sum('amount');

        // Saldo
        $balance = $totalIncome - $totalFixed - $totalVariable - $supermarket - $totalInvestment - $creditCardsTotal;

        // Gráfico rosca — por categoria
        $chartDonut = [
            ['label' => 'Moradia',       'value' => $fixedPayments->whereIn('fixedCost.icon', ['🏠','💧','⚡'])->sum('amount'), 'color' => '#6366f1'],
            ['label' => 'Cartão',        'value' => $fixedPayments->filter(fn($p) => str_contains($p->fixedCost->name ?? '', 'atura') || str_contains($p->fixedCost->name ?? '', 'artão'))->sum('amount'), 'color' => '#ef4444'],
            ['label' => 'Supermercado',  'value' => $supermarket,      'color' => '#2dd4bf'],
            ['label' => 'Variáveis',     'value' => $totalVariable,    'color' => '#f59e0b'],
            ['label' => 'Investimentos', 'value' => $totalInvestment,  'color' => '#818cf8'],
        ];

        // Gráfico barras — últimos 6 meses
        $chartBars = [];
        for ($i = 5; $i >= 0; $i--) {
            $m     = now()->subMonths($i)->format('Y-m');
            $label = now()->subMonths($i)->locale('pt_BR')->isoFormat('MMM');
            $inc   = FinancialIncome::where('user_id', $user->id)->where('month', $m)->sum('amount');
            $out   = FinancialFixedPayment::where('user_id', $user->id)->where('month', $m)->sum('amount')
                   + FinancialVariableCost::where('user_id', $user->id)->where('month', $m)->sum('amount');
            $chartBars[] = ['label' => $label, 'income' => (float) $inc, 'expense' => (float) $out];
        }

        // Mês atual para "novo mês inteligente" (será copiado para o próximo)
        $prevFixedPayments = FinancialFixedPayment::where('user_id', $user->id)
            ->where('month', $month)->with('fixedCost')->get();

        return view('finance.index', compact(
            'month', 'incomes', 'totalIncome',
            'fixedCosts', 'fixedPayments', 'totalFixed', 'paidFixed',
            'variables', 'totalVariable',
            'supermarket', 'investments', 'totalInvestment',
            'balance', 'chartDonut', 'chartBars',
            'prevFixedPayments', 'creditCardsTotal'
        ));
    }

    public function toggleFixed(FinancialFixedPayment $payment)
    {
        if ($payment->user_id !== Auth::id()) {
            Log::warning('Acesso negado', ['user_id' => Auth::id(), 'url' => request()->fullUrl(), 'ip' => request()->ip(), 'at' => now()->toIso8601String()]);
            abort(403);
        }
        $payment->update([
            'paid'    => !$payment->paid,
            'paid_at' => !$payment->paid ? now() : null,
        ]);
        return back();
    }

    public function updateFixed(Request $request, FinancialFixedPayment $payment)
    {
        if ($payment->user_id !== Auth::id()) {
            Log::warning('Acesso negado', ['user_id' => Auth::id(), 'url' => request()->fullUrl(), 'ip' => request()->ip(), 'at' => now()->toIso8601String()]);
            abort(403);
        }
        $payment->update(['amount' => $request->validate(['amount' => 'required|numeric|min:0'])['amount']]);
        return back();
    }

    public function toggleVariable(FinancialVariableCost $variable)
    {
        if ($variable->user_id !== Auth::id()) {
            Log::warning('Acesso negado', ['user_id' => Auth::id(), 'url' => request()->fullUrl(), 'ip' => request()->ip(), 'at' => now()->toIso8601String()]);
            abort(403);
        }
        $variable->update(['paid' => !$variable->paid]);
        return back();
    }

    public function storeVariable(Request $request)
    {
        $data = $request->validate([
            'name'     => 'required|string|max:255',
            'category' => 'required|string',
            'amount'   => 'required|numeric|min:0',
            'month'    => 'required|string',
        ]);
        $data['user_id'] = Auth::id();
        FinancialVariableCost::create($data);
        return back();
    }

    public function updateVariable(Request $request, FinancialVariableCost $variable)
    {
        if ($variable->user_id !== Auth::id()) {
            Log::warning('Acesso negado', ['user_id' => Auth::id(), 'url' => request()->fullUrl(), 'ip' => request()->ip(), 'at' => now()->toIso8601String()]);
            abort(403);
        }
        $variable->update($request->validate(['amount' => 'required|numeric|min:0', 'name' => 'sometimes|string']));
        return back();
    }

    public function storeFixed(Request $request)
    {
        $data = $request->validate([
            'name'    => 'required|string|max:255',
            'amount'  => 'required|numeric|min:0',
            'due_day' => 'required|integer|min:1|max:31',
        ]);
        $data['user_id'] = Auth::id();
        $cost = FinancialFixedCost::create($data);
        FinancialFixedPayment::create([
            'fixed_cost_id' => $cost->id,
            'user_id'       => Auth::id(),
            'month'         => $request->month,
            'amount'        => $cost->amount,
            'paid'          => false,
        ]);
        return back();
    }

    public function storeIncome(Request $request)
    {
        $data = $request->validate([
            'name'   => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'month'  => 'required|string',
        ]);
        $data['user_id'] = Auth::id();
        FinancialIncome::create($data);
        return back();
    }

    public function storeInvestment(Request $request)
    {
        $data = $request->validate([
            'name'     => 'required|string|max:255',
            'category' => 'required|string',
            'amount'   => 'required|numeric|min:0',
            'month'    => 'required|string',
        ]);
        $investment = FinancialInvestment::firstOrCreate(
            ['user_id' => Auth::id(), 'name' => $data['name']],
            ['category' => $data['category'], 'started_at' => now()]
        );
        FinancialInvestmentEntry::create([
            'investment_id' => $investment->id,
            'user_id'       => Auth::id(),
            'month'         => $data['month'],
            'amount'        => $data['amount'],
        ]);
        return back();
    }

    public function updateInvestment(Request $request, FinancialInvestmentEntry $entry)
    {
        if ($entry->user_id !== Auth::id()) {
            Log::warning('Acesso negado', ['user_id' => Auth::id(), 'url' => request()->fullUrl(), 'ip' => request()->ip(), 'at' => now()->toIso8601String()]);
            abort(403);
        }
        $entry->update(['amount' => $request->validate(['amount' => 'required|numeric|min:0'])['amount']]);
        return back();
    }

    public function updateInvestmentInitial(Request $request, FinancialInvestment $investment)
    {
        if ($investment->user_id !== Auth::id()) {
            Log::warning('Acesso negado', ['user_id' => Auth::id(), 'url' => request()->fullUrl(), 'ip' => request()->ip(), 'at' => now()->toIso8601String()]);
            abort(403);
        }
        $investment->update(['initial_amount' => $request->validate(['initial_amount' => 'required|numeric|min:0'])['initial_amount']]);
        return back();
    }

    public function destroyFixed(FinancialFixedCost $cost)
    {
        if ($cost->user_id !== Auth::id()) {
            Log::warning('Acesso negado', ['user_id' => Auth::id(), 'url' => request()->fullUrl(), 'ip' => request()->ip(), 'at' => now()->toIso8601String()]);
            abort(403);
        }
        $cost->delete();
        return back();
    }

    public function destroyVariable(FinancialVariableCost $variable)
    {
        if ($variable->user_id !== Auth::id()) {
            Log::warning('Acesso negado', ['user_id' => Auth::id(), 'url' => request()->fullUrl(), 'ip' => request()->ip(), 'at' => now()->toIso8601String()]);
            abort(403);
        }
        $variable->delete();
        return back();
    }

    public function destroyIncome(FinancialIncome $income)
    {
        if ($income->user_id !== Auth::id()) {
            Log::warning('Acesso negado', ['user_id' => Auth::id(), 'url' => request()->fullUrl(), 'ip' => request()->ip(), 'at' => now()->toIso8601String()]);
            abort(403);
        }
        $income->delete();
        return back();
    }

    public function destroyInvestment(FinancialInvestment $investment)
    {
        if ($investment->user_id !== Auth::id()) {
            Log::warning('Acesso negado', ['user_id' => Auth::id(), 'url' => request()->fullUrl(), 'ip' => request()->ip(), 'at' => now()->toIso8601String()]);
            abort(403);
        }
        $investment->delete();
        return back();
    }

    public function openMonth(Request $request)
    {
        $month     = $request->validate(['month' => 'required|string'])['month'];
        $user      = Auth::user();
        $prevMonth = \Carbon\Carbon::parse($month.'-01')->subMonth()->format('Y-m');

        // Copia fixos selecionados (apenas os que pertencem ao usuário)
        foreach ($request->input('fixed_ids', []) as $fixedId) {
            $prev = FinancialFixedPayment::where('user_id', $user->id)
                ->where('fixed_cost_id', $fixedId)
                ->where('month', $prevMonth)->first();
            if (!$prev) continue;
            FinancialFixedPayment::firstOrCreate(
                ['fixed_cost_id' => $fixedId, 'month' => $month, 'user_id' => $user->id],
                ['amount' => $prev->amount, 'paid' => false]
            );
        }

        // Copia entradas selecionadas (apenas as que pertencem ao usuário)
        foreach ($request->input('income_ids', []) as $incId) {
            $prev = FinancialIncome::where('user_id', $user->id)->find($incId);
            if (!$prev) continue;
            FinancialIncome::firstOrCreate(
                ['user_id' => $user->id, 'month' => $month, 'name' => $prev->name],
                ['amount' => $prev->amount]
            );
        }

        return redirect()->route('finance.index', ['month' => $month])
            ->with('success', 'Mês aberto com sucesso!');
    }
}
