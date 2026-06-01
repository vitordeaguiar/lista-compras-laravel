<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ShoppingListController;
use App\Http\Controllers\ShoppingItemController;
use App\Http\Controllers\HistoryController;
use App\Http\Controllers\FinanceController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\CreditCardController;

Route::middleware('guest')->group(function () {
    Route::get('/login',               [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login',              [AuthController::class, 'login'])->middleware('throttle:login');
    Route::get('/register',            [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register/send-code', [AuthController::class, 'sendCode'])->name('register.send-code')->middleware('throttle:send-code');
    Route::get('/register/verify',     [AuthController::class, 'showVerify'])->name('register.verify');
    Route::post('/register/verify',    [AuthController::class, 'verifyCode'])->name('register.verify.post')->middleware('throttle:verify-code');
    Route::post('/register/complete',  [AuthController::class, 'register'])->name('register.complete')->middleware('throttle:verify-code');
});

Route::middleware('auth')->group(function () {
    Route::get('/',          fn() => redirect()->route('dashboard'));
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::post('/logout',   [AuthController::class, 'logout'])->name('logout');

    // Lists
    Route::get('/listas',                   [ShoppingListController::class, 'index'])->name('lists.index');
    Route::post('/listas',                  [ShoppingListController::class, 'store'])->name('lists.store');
    Route::get('/listas/sugestoes',         [ShoppingItemController::class, 'suggestions'])->name('items.suggestions');
    Route::get('/listas/{list}',            [ShoppingListController::class, 'show'])->name('lists.show');
    Route::patch('/listas/{list}/concluir', [ShoppingListController::class, 'complete'])->name('lists.complete');
    Route::patch('/listas/{list}/reabrir',  [ShoppingListController::class, 'reopen'])->name('lists.reopen');
    Route::delete('/listas/{list}',         [ShoppingListController::class, 'destroy'])->name('lists.destroy');

    // Items
    Route::post('/listas/{list}/itens',                [ShoppingItemController::class, 'store'])->name('items.store');
    Route::patch('/listas/{list}/itens/{item}',        [ShoppingItemController::class, 'update'])->name('items.update');
    Route::patch('/listas/{list}/itens/{item}/toggle', [ShoppingItemController::class, 'toggle'])->name('items.toggle');
    Route::delete('/listas/{list}/itens/{item}',       [ShoppingItemController::class, 'destroy'])->name('items.destroy');

    // History
    Route::get('/historico', [HistoryController::class, 'index'])->name('history.index');

    // Finance
    Route::get('/financeiro',                                 [FinanceController::class, 'index'])->name('finance.index');
    Route::post('/financeiro/fixos',                          [FinanceController::class, 'storeFixed'])->name('finance.fixed.store');
    Route::patch('/financeiro/fixos/{payment}/toggle',        [FinanceController::class, 'toggleFixed'])->name('finance.fixed.toggle');
    Route::patch('/financeiro/fixos/{payment}/update',        [FinanceController::class, 'updateFixed'])->name('finance.fixed.update');
    Route::post('/financeiro/variaveis',                      [FinanceController::class, 'storeVariable'])->name('finance.variable.store');
    Route::patch('/financeiro/variaveis/{variable}/toggle',   [FinanceController::class, 'toggleVariable'])->name('finance.variable.toggle');
    Route::patch('/financeiro/variaveis/{variable}/update',   [FinanceController::class, 'updateVariable'])->name('finance.variable.update');
    Route::post('/financeiro/entradas',                       [FinanceController::class, 'storeIncome'])->name('finance.income.store');
    Route::delete('/financeiro/entradas/{income}',            [FinanceController::class, 'destroyIncome'])->name('finance.income.destroy');
    Route::delete('/financeiro/fixos/{cost}',                 [FinanceController::class, 'destroyFixed'])->name('finance.fixed.destroy');
    Route::delete('/financeiro/variaveis/{variable}',         [FinanceController::class, 'destroyVariable'])->name('finance.variable.destroy');
    Route::post('/financeiro/investimentos',                  [FinanceController::class, 'storeInvestment'])->name('finance.investment.store');
    Route::patch('/financeiro/investimentos/{entry}/update',      [FinanceController::class, 'updateInvestment'])->name('finance.investment.update');
    Route::patch('/financeiro/investimentos/{investment}/initial', [FinanceController::class, 'updateInvestmentInitial'])->name('finance.investment.initial');
    Route::delete('/financeiro/investimentos/{investment}',       [FinanceController::class, 'destroyInvestment'])->name('finance.investment.destroy');
    Route::post('/financeiro/abrir-mes',                      [FinanceController::class, 'openMonth'])->name('finance.open-month');

    // Credit Cards
    Route::prefix('cartoes')->name('creditcards.')->group(function () {
        Route::get('/', [CreditCardController::class, 'index'])->name('index');
        Route::post('/', [CreditCardController::class, 'store'])->name('store');
        Route::patch('/{card}', [CreditCardController::class, 'update'])->name('update');
        Route::delete('/{card}', [CreditCardController::class, 'destroy'])->name('destroy');

        Route::post('/{card}/parcelamentos', [CreditCardController::class, 'storeInstallment'])->name('installments.store');
        Route::patch('/parcelamentos/{installment}', [CreditCardController::class, 'updateInstallment'])->name('installments.update');
        Route::post('/parcelamentos/{installment}/quitar', [CreditCardController::class, 'payOffInstallment'])->name('installments.payoff');
        Route::post('/parcelamentos/{installment}/avancar', [CreditCardController::class, 'advanceInstallment'])->name('installments.advance');
        Route::post('/parcelamentos/{installment}/voltar', [CreditCardController::class, 'regressInstallment'])->name('installments.regress');
        Route::delete('/parcelamentos/{installment}', [CreditCardController::class, 'destroyInstallment'])->name('installments.destroy');

        Route::post('/pagamentos/{payment}/toggle', [CreditCardController::class, 'togglePayment'])->name('payments.toggle');
        Route::patch('/pagamentos/{payment}/valor', [CreditCardController::class, 'updatePaymentAmount'])->name('payments.amount');
    });

    // Profile & Settings
    Route::get('/perfil',                                 [ProfileController::class, 'index'])->name('profile.index');
    Route::put('/perfil',                                 [ProfileController::class, 'updateProfile'])->name('profile.update');
    Route::put('/perfil/senha',                           [ProfileController::class, 'updatePassword'])->name('profile.password');
    Route::put('/perfil/configuracoes',                   [ProfileController::class, 'updateSettings'])->name('profile.settings');
    Route::delete('/perfil/sessoes/todas',                [ProfileController::class, 'destroyAllSessions'])->name('profile.sessions.destroy-all');
    Route::delete('/perfil/sessoes',                      [ProfileController::class, 'destroySession'])->name('profile.session.destroy');

    // Admin
    Route::middleware('admin')->prefix('admin')->name('admin.')->group(function () {
        Route::get('/',                              [AdminController::class, 'index'])->name('index');
        Route::get('/users/{user}',                  [AdminController::class, 'show'])->name('users.show');
        Route::delete('/users/{user}',               [AdminController::class, 'destroy'])->name('users.destroy');
        Route::post('/users/{user}/toggle-admin',    [AdminController::class, 'toggleAdmin'])->name('users.toggle-admin');
    });
});
