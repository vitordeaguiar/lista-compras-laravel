<?php
namespace App\Http\Controllers;

use App\Models\CreditCard;
use App\Models\CreditCardInstallment;
use App\Models\CreditCardPayment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class CreditCardController extends Controller
{
    public function index(Request $request)
    {
        $month = $request->get('month', now()->format('Y-m'));
        $user  = Auth::user();

        $cards = CreditCard::where('user_id', $user->id)
            ->where('is_active', true)
            ->with(['installments' => fn($q) => $q->where('is_paid_off', false)])
            ->get();

        $monthDate = Carbon::parse($month . '-01');

        $cards->each(function ($card) use ($month, $monthDate, $user) {
            $card->month_amount = (float) $card->installments
                ->filter(fn($inst) => $inst->isActiveInMonth($monthDate))
                ->sum('installment_amount');

            $payment = CreditCardPayment::where([
                'credit_card_id' => $card->id,
                'month'          => $month,
                'user_id'        => $user->id,
            ])->first();

            if (!$payment) {
                $payment = new CreditCardPayment([
                    'credit_card_id' => $card->id,
                    'month'          => $month,
                    'amount'         => $card->month_amount,
                    'paid'           => false,
                ]);
                $payment->user_id = $user->id;
                $payment->save();
            }

            $card->current_payment = $payment;

            $card->projection = [];
            for ($i = 0; $i < 6; $i++) {
                $m     = $monthDate->copy()->addMonths($i);
                $total = (float) $card->installments
                    ->filter(fn($inst) => $inst->isActiveInMonth($m))
                    ->sum('installment_amount');
                $card->projection[] = [
                    'label' => $m->locale('pt_BR')->isoFormat('MMM/YY'),
                    'month' => $m->format('Y-m'),
                    'value' => $total,
                ];
            }
        });

        $totalFatura       = $cards->sum('month_amount');
        $totalParcelamentos = CreditCardInstallment::where('user_id', $user->id)
            ->where('is_paid_off', false)->count();
        $faturasPagas      = $cards->filter(fn($c) => $c->current_payment->paid)->count();

        $globalProjection = [];
        for ($i = 0; $i < 6; $i++) {
            $m     = $monthDate->copy()->addMonths($i);
            $total = 0;
            foreach ($cards as $card) {
                $total += (float) $card->installments
                    ->filter(fn($inst) => $inst->isActiveInMonth($m))
                    ->sum('installment_amount');
            }
            $globalProjection[] = [
                'label' => $m->locale('pt_BR')->isoFormat('MMM/YY'),
                'month' => $m->format('Y-m'),
                'value' => $total,
            ];
        }

        $futureCommitment = collect($globalProjection)->skip(1)->sum('value');

        return view('creditcards.index', compact(
            'cards', 'month', 'totalFatura',
            'totalParcelamentos', 'globalProjection', 'futureCommitment', 'faturasPagas'
        ));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'         => 'required|string|max:255',
            'brand'        => 'required|in:visa,mastercard,elo,amex,outro',
            'credit_limit' => 'required|string',
            'due_day'      => 'required|integer|min:1|max:31',
            'closing_day'  => 'required|integer|min:1|max:31',
            'color'        => 'required|string|max:50',
        ]);
        $data['credit_limit'] = (float) str_replace(['.', ','], ['', '.'], $data['credit_limit']);

        $card = new CreditCard($data);
        $card->user_id = Auth::id();
        $card->save();

        return back()->with('success', 'Cartão adicionado!');
    }

    public function destroy(CreditCard $card)
    {
        if ($card->user_id !== Auth::id()) {
            Log::warning('Acesso negado', ['user_id' => Auth::id(), 'url' => request()->fullUrl(), 'ip' => request()->ip(), 'at' => now()->toIso8601String()]);
            abort(403);
        }
        $card->update(['is_active' => false]);
        return back()->with('success', 'Cartão removido.');
    }

    public function storeInstallment(Request $request, CreditCard $card)
    {
        if ($card->user_id !== Auth::id()) {
            Log::warning('Acesso negado', ['user_id' => Auth::id(), 'url' => request()->fullUrl(), 'ip' => request()->ip(), 'at' => now()->toIso8601String()]);
            abort(403);
        }

        $data = $request->validate([
            'description'        => 'required|string|max:255',
            'category'           => 'required|in:compras,assinatura,eletronico,casa,saude,outros',
            'total_amount'       => 'required|string',
            'total_installments' => 'required|integer|min:1|max:72',
            'is_recurring'       => 'nullable|boolean',
            'purchase_date'      => 'required|date',
        ]);

        $totalAmount = (float) str_replace(['.', ','], ['', '.'], $data['total_amount']);
        $installments = (int) $data['total_installments'];

        $installment = new CreditCardInstallment([
            'credit_card_id'      => $card->id,
            'description'         => $data['description'],
            'category'            => $data['category'],
            'total_amount'        => $totalAmount,
            'installment_amount'  => round($totalAmount / $installments, 2),
            'total_installments'  => $installments,
            'current_installment' => 1,
            'is_recurring'        => $request->boolean('is_recurring'),
            'purchase_date'       => $data['purchase_date'],
            'is_paid_off'         => false,
        ]);
        $installment->user_id = Auth::id();
        $installment->save();

        return back()->with('success', 'Parcelamento adicionado!');
    }

    public function payOffInstallment(CreditCardInstallment $installment)
    {
        if ($installment->user_id !== Auth::id()) {
            Log::warning('Acesso negado', ['user_id' => Auth::id(), 'url' => request()->fullUrl(), 'ip' => request()->ip(), 'at' => now()->toIso8601String()]);
            abort(403);
        }
        $installment->update(['is_paid_off' => true]);
        return back()->with('success', 'Parcelamento marcado como quitado!');
    }

    public function destroyInstallment(CreditCardInstallment $installment)
    {
        if ($installment->user_id !== Auth::id()) {
            Log::warning('Acesso negado', ['user_id' => Auth::id(), 'url' => request()->fullUrl(), 'ip' => request()->ip(), 'at' => now()->toIso8601String()]);
            abort(403);
        }
        $installment->delete();
        return back()->with('success', 'Parcelamento removido.');
    }

    public function togglePayment(CreditCardPayment $payment)
    {
        if ($payment->user_id !== Auth::id()) {
            Log::warning('Acesso negado', ['user_id' => Auth::id(), 'url' => request()->fullUrl(), 'ip' => request()->ip(), 'at' => now()->toIso8601String()]);
            abort(403);
        }
        $nowPaid = !$payment->paid;
        $payment->update([
            'paid'    => $nowPaid,
            'paid_at' => $nowPaid ? now() : null,
        ]);
        return back();
    }

    public function updatePaymentAmount(Request $request, CreditCardPayment $payment)
    {
        if ($payment->user_id !== Auth::id()) {
            Log::warning('Acesso negado', ['user_id' => Auth::id(), 'url' => request()->fullUrl(), 'ip' => request()->ip(), 'at' => now()->toIso8601String()]);
            abort(403);
        }
        $amount = (float) str_replace(['.', ','], ['', '.'], $request->amount);
        $payment->update(['amount' => $amount]);
        return back();
    }

    public function advanceInstallment(CreditCardInstallment $installment)
    {
        if ($installment->user_id !== Auth::id()) {
            Log::warning('Acesso negado', ['user_id' => Auth::id(), 'url' => request()->fullUrl(), 'ip' => request()->ip(), 'at' => now()->toIso8601String()]);
            abort(403);
        }

        if ($installment->current_installment < $installment->total_installments) {
            $next = $installment->current_installment + 1;
            $installment->update([
                'current_installment' => $next,
                'is_paid_off'         => $next >= $installment->total_installments,
            ]);
        }

        return back();
    }
}
