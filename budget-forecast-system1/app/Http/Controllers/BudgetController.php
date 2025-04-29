<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\BudgetEntry;
use App\Models\CostCenter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class BudgetController extends Controller
{
    /**
     * Display the budget interface.
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
        $budgetData = [];

        if ($selectedCostCenterId) {
            // Fetch budget entries for the selected year and cost center
            $entries = BudgetEntry::where("cost_center_id", $selectedCostCenterId)
                                  ->where("year", $selectedYear)
                                  ->get()
                                  ->keyBy(function ($item) {
                                      return $item->account_id . "_" . $item->month;
                                  });

            // Prepare data for the view (pivot table like structure)
            foreach ($accounts as $account) {
                $budgetData[$account->id] = [];
                for ($month = 1; $month <= 12; $month++) {
                    $key = $account->id . "_" . $month;
                    $budgetData[$account->id][$month] = $entries->get($key)->value ?? null; // Use null or 0 as default?
                }
            }
        }
        
        // Determine if the user can edit based on role (further checks for month/year will be in update/JS)
        $canEdit = $user->hasRole("admin") || $user->hasRole("editor");

        return view("budget.index", compact(
            "accounts", 
            "budgetData", 
            "selectedYear", 
            "selectedCostCenterId", 
            "accessibleCostCenters",
            "canEdit"
        ));
    }

    /**
     * Update a specific budget entry.
     */
    public function update(Request $request)
    {
        $user = Auth::user();
        $currentMonth = Carbon::now()->month;

        // Business Rule Check: Budget Editing Period (October to November)
        if (!in_array($currentMonth, [10, 11])) {
            return response()->json(["message" => "A edição do orçamento só é permitida entre Outubro e Novembro."], 403);
        }
        
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

        // Note: The validation above checks the *current* month for editing permission.
        // It assumes the budget being edited is for a future year planned during Oct/Nov.
        // If the rule meant editing the *target* month/year during Oct/Nov, the logic would need adjustment.

        try {
            BudgetEntry::updateOrCreate(
                [
                    "cost_center_id" => $validated["cost_center_id"],
                    "account_id" => $validated["account_id"],
                    "year" => $validated["year"],
                    "month" => $validated["month"],
                ],
                ["value" => $validated["value"] ?? 0] // Store 0 if value is null/empty
            );

            return response()->json(["message" => "Orçamento atualizado com sucesso."]);

        } catch (\Exception $e) {
            // Log error $e->getMessage()
            return response()->json(["message" => "Erro ao atualizar o orçamento."], 500);
        }
    }
}
