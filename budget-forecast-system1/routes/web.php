<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BudgetController;
use App\Http\Controllers\ForecastController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\CostCenterController;
use App\Http\Controllers\AccountController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// Redirect root URL
Route::get('/', function () {
    if (Auth::check()) {
        // Redirect logged-in users to the budget page (or dashboard if it exists)
        return redirect()->route('budget.index');
    } else {
        // Redirect guests to the login page
        return redirect()->route('login');
    }
});

// Breeze authentication routes (login, register, etc.)
require __DIR__.'/auth.php';

// Rotas protegidas por autenticação (usuários logados)
Route::middleware(["auth"])->group(function () {
    // Dashboard (if you want one, otherwise remove or redirect)
    // Route::get('/dashboard', function () {
    //     return view('dashboard');
    // })->name('dashboard');

    // Interface de Orçamento (acessível por Admin, Editor, Visualizador)
    Route::get("/budget", [BudgetController::class, "index"])->name("budget.index");
    Route::post("/budget/update", [BudgetController::class, "update"])->name("budget.update")->middleware("role:admin,editor"); // Apenas Admin e Editor podem atualizar

    // Interface de Previsão (acessível por Admin, Editor, Visualizador)
    Route::get("/forecast", [ForecastController::class, "index"])->name("forecast.index");
    Route::post("/forecast/update", [ForecastController::class, "update"])->name("forecast.update")->middleware("role:admin,editor"); // Apenas Admin e Editor podem atualizar

    // Comentários (acessível por todos os usuários logados)
    Route::get("/comments", [CommentController::class, "index"])->name("comments.index"); // Para buscar comentários via AJAX
    Route::post("/comments", [CommentController::class, "store"])->name("comments.store"); // Para salvar um novo comentário

    // Relatórios (acessível por todos os usuários logados)
    Route::get("/reports/comparison", [ReportController::class, "comparison"])->name("reports.comparison");
    Route::get("/reports/chart-data", [ReportController::class, "getChartData"])->name("reports.chartData");

});

// Rotas de administração (protegidas por autenticação e papel 'admin')
Route::middleware(["auth", "role:admin"])->prefix('admin')->name('admin.')->group(function () {
    Route::resource("users", UserController::class);
    Route::resource("cost-centers", CostCenterController::class);
    Route::resource("accounts", AccountController::class);
    // Add other admin routes here if needed
});

