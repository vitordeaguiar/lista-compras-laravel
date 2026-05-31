<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->get('search');
        $users = User::query()
            ->when($search, fn($q) => $q->where('name', 'like', "%$search%")
                ->orWhere('email', 'like', "%$search%"))
            ->withCount([
                'shoppingLists',
                'shoppingLists as completed_lists_count' => fn($q) => $q->where('status', 'completed'),
            ])
            ->orderByDesc('created_at')
            ->paginate(20);
        return view('admin.index', compact('users', 'search'));
    }

    public function show(User $user)
    {
        $stats = [
            'lists'     => \App\Models\ShoppingList::where('user_id', $user->id)->count(),
            'completed' => \App\Models\ShoppingList::where('user_id', $user->id)->where('status', 'completed')->count(),
            'items'     => \App\Models\ShoppingItem::whereHas('shoppingList', fn($q) => $q->where('user_id', $user->id))->count(),
            'income'    => \App\Models\FinancialIncome::where('user_id', $user->id)->sum('amount'),
            'fixed'     => \App\Models\FinancialFixedPayment::where('user_id', $user->id)->sum('amount'),
            'variable'  => \App\Models\FinancialVariableCost::where('user_id', $user->id)->sum('amount'),
        ];
        return view('admin.show', compact('user', 'stats'));
    }

    public function destroy(User $user)
    {
        if ($user->id === Auth::id()) {
            return back()->with('error', 'Você não pode excluir sua própria conta por aqui.');
        }

        \App\Models\ShoppingItem::whereHas('shoppingList', fn($q) =>
            $q->where('user_id', $user->id))->delete();

        \App\Models\ShoppingList::where('user_id', $user->id)->delete();

        \App\Models\FinancialFixedPayment::where('user_id', $user->id)->delete();
        \App\Models\FinancialFixedCost::where('user_id', $user->id)->delete();
        \App\Models\FinancialVariableCost::where('user_id', $user->id)->delete();
        \App\Models\FinancialInvestmentEntry::where('user_id', $user->id)->delete();
        \App\Models\FinancialInvestment::where('user_id', $user->id)->delete();
        \App\Models\FinancialIncome::where('user_id', $user->id)->delete();

        \App\Models\UserSetting::where('user_id', $user->id)->delete();
        \Illuminate\Support\Facades\DB::table('sessions')
            ->where('user_id', $user->id)->delete();
        \Illuminate\Support\Facades\DB::table('email_verifications')
            ->where('email', $user->email)->delete();

        $user->delete();

        return back()->with('success', "Usuário {$user->name} excluído com sucesso.");
    }

    public function toggleAdmin(User $user)
    {
        if ($user->id === Auth::id()) {
            return back()->with('error', 'Você não pode alterar seu próprio status.');
        }
        // is_admin não está no $fillable (proteção contra mass assignment) — atribuição direta
        $user->is_admin = !$user->is_admin;
        $user->save();
        return back()->with('success', 'Status de admin atualizado.');
    }
}
