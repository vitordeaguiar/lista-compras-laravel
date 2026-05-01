<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ShoppingListController;
use App\Http\Controllers\ShoppingItemController;

Route::middleware('guest')->group(function () {
    Route::get('/login',     [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login',    [AuthController::class, 'login']);
    Route::get('/register',  [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
});

Route::middleware('auth')->group(function () {
    Route::get('/',                                          fn() => redirect()->route('lists.index'));
    Route::post('/logout',                                   [AuthController::class, 'logout'])->name('logout');

    // Lists
    Route::get('/listas',                                    [ShoppingListController::class, 'index'])->name('lists.index');
    Route::post('/listas',                                   [ShoppingListController::class, 'store'])->name('lists.store');
    Route::get('/listas/{list}',                             [ShoppingListController::class, 'show'])->name('lists.show');
    Route::patch('/listas/{list}/concluir',                  [ShoppingListController::class, 'complete'])->name('lists.complete');
    Route::delete('/listas/{list}',                          [ShoppingListController::class, 'destroy'])->name('lists.destroy');

    // Items (nested under list)
    Route::post('/listas/{list}/itens',                      [ShoppingItemController::class, 'store'])->name('items.store');
    Route::patch('/listas/{list}/itens/{item}',              [ShoppingItemController::class, 'update'])->name('items.update');
    Route::patch('/listas/{list}/itens/{item}/toggle',       [ShoppingItemController::class, 'toggle'])->name('items.toggle');
    Route::delete('/listas/{list}/itens/{item}',             [ShoppingItemController::class, 'destroy'])->name('items.destroy');
});
