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
            // fatura deste mês = soma das parcelas ativas no mês
            $card->month_amount = (float) $card->installments
                ->filter(fn($inst) => $inst->isActiveInMonth($monthDate, $card))
                ->sum('installment_amount');

            // limite ocupado = saldo devedor de todas as compras (parcelas não pagas)
            $card->used_limit = (float) $card->installments
                ->sum(fn($inst) => $inst->getRemainingAmount($card));

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

            $projection = [];
            for ($i = 0; $i < 6; $i++) {
                $m     = $monthDate->copy()->addMonths($i);
                $total = (float) $card->installments
                    ->filter(fn($inst) => $inst->isActiveInMonth($m, $card))
                    ->sum('installment_amount');
                $projection[] = [
                    'label' => $m->locale('pt_BR')->isoFormat('MMM/YY'),
                    'month' => $m->format('Y-m'),
                    'value' => $total,
                ];
            }
            $card->projection = $projection;
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
                    ->filter(fn($inst) => $inst->isActiveInMonth($m, $card))
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
        $data = $this->validateCard($request);
        $data['credit_limit'] = $this->parseMoney($data['credit_limit']);

        $card = new CreditCard($data);
        $card->user_id = Auth::id();
        $card->save();

        return back()->with('success', 'Cartão adicionado!');
    }

    public function update(Request $request, CreditCard $card)
    {
        $this->authorizeOwner($card->user_id);

        $data = $this->validateCard($request);
        $data['credit_limit'] = $this->parseMoney($data['credit_limit']);

        $card->update($data);

        return back()->with('success', 'Cartão atualizado!');
    }

    public function destroy(CreditCard $card)
    {
        $this->authorizeOwner($card->user_id);
        $card->update(['is_active' => false]);
        return back()->with('success', 'Cartão removido.');
    }

    public function storeInstallment(Request $request, CreditCard $card)
    {
        $this->authorizeOwner($card->user_id);

        $data = $this->validateInstallment($request);
        $totalAmount  = $this->parseMoney($data['total_amount']);
        $installments = (int) $data['total_installments'];

        $installment = new CreditCardInstallment([
            'credit_card_id'     => $card->id,
            'description'        => $data['description'],
            'category'           => $data['category'],
            'total_amount'       => $totalAmount,
            'installment_amount' => round($totalAmount / $installments, 2),
            'total_installments' => $installments,
            'is_recurring'       => $request->boolean('is_recurring'),
            'purchase_date'      => $data['purchase_date'],
            'is_paid_off'        => false,
        ]);
        $installment->user_id = Auth::id();
        $installment->save();

        return back()->with('success', 'Parcelamento adicionado!');
    }

    public function updateInstallment(Request $request, CreditCardInstallment $installment)
    {
        $this->authorizeOwner($installment->user_id);

        $data = $this->validateInstallment($request);
        $totalAmount  = $this->parseMoney($data['total_amount']);
        $installments = (int) $data['total_installments'];

        $installment->update([
            'description'        => $data['description'],
            'category'           => $data['category'],
            'total_amount'       => $totalAmount,
            'installment_amount' => round($totalAmount / $installments, 2),
            'total_installments' => $installments,
            'is_recurring'       => $request->boolean('is_recurring'),
            'purchase_date'      => $data['purchase_date'],
            'manual_paid_count'  => null, // volta ao cálculo automático
        ]);

        return back()->with('success', 'Parcelamento atualizado!');
    }

    public function payOffInstallment(CreditCardInstallment $installment)
    {
        $this->authorizeOwner($installment->user_id);
        $installment->update(['is_paid_off' => true]);
        return back()->with('success', 'Parcelamento marcado como quitado!');
    }

    public function destroyInstallment(CreditCardInstallment $installment)
    {
        $this->authorizeOwner($installment->user_id);
        $installment->delete();
        return back()->with('success', 'Parcelamento removido.');
    }

    public function togglePayment(CreditCardPayment $payment)
    {
        $this->authorizeOwner($payment->user_id);
        $nowPaid = !$payment->paid;
        $payment->update([
            'paid'    => $nowPaid,
            'paid_at' => $nowPaid ? now() : null,
        ]);
        return back();
    }

    public function updatePaymentAmount(Request $request, CreditCardPayment $payment)
    {
        $this->authorizeOwner($payment->user_id);
        $payment->update(['amount' => $this->parseMoney($request->amount)]);
        return back();
    }

    public function advanceInstallment(CreditCardInstallment $installment)
    {
        $this->authorizeOwner($installment->user_id);

        $card = $installment->creditCard;
        $paid = $installment->paidInstallmentsCount($card);
        if ($paid < $installment->total_installments) {
            $installment->update(['manual_paid_count' => $paid + 1]);
        }

        return back();
    }

    public function regressInstallment(CreditCardInstallment $installment)
    {
        $this->authorizeOwner($installment->user_id);

        $card = $installment->creditCard;
        $paid = $installment->paidInstallmentsCount($card);
        $installment->update(['manual_paid_count' => max(0, $paid - 1)]);

        return back();
    }

    // ── Helpers ────────────────────────────────────────────────────────

    private function authorizeOwner(?int $ownerId): void
    {
        if ($ownerId !== Auth::id()) {
            Log::warning('Acesso negado', [
                'user_id' => Auth::id(),
                'url'     => request()->fullUrl(),
                'ip'      => request()->ip(),
                'at'      => now()->toIso8601String(),
            ]);
            abort(403);
        }
    }

    private function parseMoney($value): float
    {
        return (float) str_replace(['.', ','], ['', '.'], (string) $value);
    }

    private function validateCard(Request $request): array
    {
        return $request->validate([
            'name'         => 'required|string|max:255',
            'brand'        => 'required|in:visa,mastercard,elo,amex,outro',
            'credit_limit' => 'required|string',
            'due_day'      => 'required|integer|min:1|max:31',
            'closing_day'  => 'required|integer|min:1|max:31',
            'color'        => 'required|string|max:50',
        ]);
    }

    private function validateInstallment(Request $request): array
    {
        return $request->validate([
            'description'        => 'required|string|max:255',
            'category'           => 'required|in:compras,assinatura,eletronico,casa,saude,carro,comida,outros',
            'total_amount'       => 'required|string',
            'total_installments' => 'required|integer|min:1|max:72',
            'is_recurring'       => 'nullable|boolean',
            'purchase_date'      => 'required|date',
        ]);
    }
}
