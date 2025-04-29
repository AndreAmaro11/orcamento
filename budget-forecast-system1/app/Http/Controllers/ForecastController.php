<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\ForecastEntry;
use App\Models\CostCenter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ForecastController extends Controller
{
    /**
     * Display the forecast interface.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $selectedYear = $request->input("year", Carbon::now()->year);
        $selectedCostCenterId = $request->input("cost_center_id");

        // Determine accessible cost centers
        if ($user->hasRole("admin")) {
            $accessibleCostCenters = CostCenter::orderBy("name")->get();
            // If no cost center selected by admin, default to the first one or show a selection prompt
            if (!$selectedCostCenterId && $accessibleCostCenters->isNotEmpty()) {
                $selectedCostCenterId = $accessibleCostCenters->first()->id;
            }
        } else {
            $accessibleCostCenters = $user->costCenters()->orderBy("name")->get();
            // If user has access to only one, select it by default
            if ($accessibleCostCenters->count() === 1) {
                $selectedCostCenterId = $accessibleCostCenters->first()->id;
            }
            // If a specific cost center is requested, check access
            if ($selectedCostCenterId && !$user->hasCostCenterAccess($selectedCostCenterId)) {
                abort(403, "Acesso não autorizado a este centro de custo.");
            }
            // If no cost center selected and user has multiple, prompt selection (or handle as needed)
            if (!$selectedCostCenterId && $accessibleCostCenters->count() > 1) {
                 // For now, don't load data, let the view handle the prompt/selection
                 $selectedCostCenterId = null; 
            }
        }

        $accounts = Account::orderBy("code")->get();
        $forecastData = [];
        $currentMonth = Carbon::now()->month;
        $currentYear = Carbon::now()->year;

        if ($selectedCostCenterId) {
            // Fetch forecast entries for the selected year and cost center
            $entries = ForecastEntry::where("cost_center_id", $selectedCostCenterId)
                                  ->where("year", $selectedYear)
                                  ->get()
                                  ->keyBy(function ($item) {
                                      return $item->account_id . "_" . $item->month;
                                  });

            // Prepare data for the view (pivot table like structure)
            foreach ($accounts as $account) {
                $forecastData[$account->id] = [];
                for ($month = 1; $month <= 12; $month++) {
                    $key = $account->id . "_" . $month;
                    $forecastData[$account->id][$month] = $entries->get($key)->value ?? null; // Use null or 0 as default?
                }
            }
        }
        
        // Determine if the user can edit based on role and month/year
        $canEdit = $user->hasRole("admin") || $user->hasRole("editor");

        return view("forecast.index", compact(
            "accounts", 
            "forecastData", 
            "selectedYear", 
            "selectedCostCenterId", 
            "accessibleCostCenters",
            "canEdit",
            "currentMonth",
            "currentYear"
        ));
    }

    /**
     * Update a specific forecast entry.
     */
    public function update(Request $request)
    {
        $user = Auth::user();
        $currentMonth = Carbon::now()->month;
        $currentYear = Carbon::now()->year;
        
        $validated = $request->validate([
            "year" => ["required", "integer", "min:2000"],
            "month" => ["required", "integer", "min:1", "max:12"],
            "cost_center_id" => ["required", "integer", "exists:cost_centers,id"],
            "account_id" => ["required", "integer", "exists:accounts,id"],
            "value" => ["nullable", "numeric"],
        ]);

        // Authorization Check 1: Role (Admin or Editor)
        // Middleware 'role:admin,editor' already handles this for the route

        // Authorization Check 2: Cost Center Access
        if (!$user->hasCostCenterAccess($validated["cost_center_id"])) {
            return response()->json(["message" => "Acesso não autorizado a este centro de custo."], 403);
        }

        // Business Rule Check: Editing Period for Forecast
        // Para a Previsão, o usuário só poderá editar o mês atual e os meses futuros do ano vigente
        if ($validated["year"] < $currentYear || 
            ($validated["year"] == $currentYear && $validated["month"] < $currentMonth)) {
            return response()->json(["message" => "Não é permitido editar meses passados na previsão."], 403);
        }

        try {
            ForecastEntry::updateOrCreate(
                [
                    "cost_center_id" => $validated["cost_center_id"],
                    "account_id" => $validated["account_id"],
                    "year" => $validated["year"],
                    "month" => $validated["month"],
                ],
                ["value" => $validated["value"] ?? 0] // Store 0 if value is null/empty
            );

            return response()->json(["message" => "Previsão atualizada com sucesso."]);

        } catch (\Exception $e) {
            // Log error $e->getMessage()
            return response()->json(["message" => "Erro ao atualizar a previsão."], 500);
        }
    }
}
