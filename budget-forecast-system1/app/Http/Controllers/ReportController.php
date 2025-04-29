<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\BudgetEntry;
use App\Models\ForecastEntry;
use App\Models\CostCenter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ReportController extends Controller
{
    /**
     * Display the budget vs forecast comparison report.
     */
    public function comparison(Request $request)
    {
        $user = Auth::user();
        $selectedYear = $request->input("year", Carbon::now()->year);
        $selectedCostCenterId = $request->input("cost_center_id");

        // Determine accessible cost centers (same logic as Budget/Forecast controllers)
        if ($user->hasRole("admin")) {
            $accessibleCostCenters = CostCenter::orderBy("name")->get();
            if (!$selectedCostCenterId && $accessibleCostCenters->isNotEmpty()) {
                $selectedCostCenterId = $accessibleCostCenters->first()->id;
            }
        } else {
            $accessibleCostCenters = $user->costCenters()->orderBy("name")->get();
            if ($accessibleCostCenters->count() === 1) {
                $selectedCostCenterId = $accessibleCostCenters->first()->id;
            }
            if ($selectedCostCenterId && !$user->hasCostCenterAccess($selectedCostCenterId)) {
                abort(403, "Acesso não autorizado a este centro de custo.");
            }
             if (!$selectedCostCenterId && $accessibleCostCenters->count() > 1) {
                 $selectedCostCenterId = null; 
            }
        }

        $accounts = Account::orderBy("code")->get();
        $comparisonData = [];

        if ($selectedCostCenterId) {
            // Fetch budget entries
            $budgetEntries = BudgetEntry::where("cost_center_id", $selectedCostCenterId)
                                      ->where("year", $selectedYear)
                                      ->get()
                                      ->keyBy(function ($item) {
                                          return $item->account_id . "_" . $item->month;
                                      });

            // Fetch forecast entries
            $forecastEntries = ForecastEntry::where("cost_center_id", $selectedCostCenterId)
                                          ->where("year", $selectedYear)
                                          ->get()
                                          ->keyBy(function ($item) {
                                              return $item->account_id . "_" . $item->month;
                                          });

            // Prepare comparison data
            foreach ($accounts as $account) {
                $comparisonData[$account->id] = [
                    "account_code" => $account->code,
                    "account_name" => $account->name,
                    "monthly_data" => [],
                    "budget_total" => 0,
                    "forecast_total" => 0,
                ];
                for ($month = 1; $month <= 12; $month++) {
                    $budgetKey = $account->id . "_" . $month;
                    $forecastKey = $account->id . "_" . $month;
                    
                    $budgetValue = $budgetEntries->get($budgetKey)->value ?? 0;
                    $forecastValue = $forecastEntries->get($forecastKey)->value ?? 0;
                    $variance = $forecastValue - $budgetValue;
                    $variancePercent = ($budgetValue != 0) ? ($variance / $budgetValue) * 100 : null; // Avoid division by zero

                    $comparisonData[$account->id]["monthly_data"][$month] = [
                        "budget" => $budgetValue,
                        "forecast" => $forecastValue,
                        "variance" => $variance,
                        "variance_percent" => $variancePercent,
                    ];
                    $comparisonData[$account->id]["budget_total"] += $budgetValue;
                    $comparisonData[$account->id]["forecast_total"] += $forecastValue;
                }
                 $comparisonData[$account->id]["variance_total"] = $comparisonData[$account->id]["forecast_total"] - $comparisonData[$account->id]["budget_total"];
                 $comparisonData[$account->id]["variance_percent_total"] = ($comparisonData[$account->id]["budget_total"] != 0) ? ($comparisonData[$account->id]["variance_total"] / $comparisonData[$account->id]["budget_total"]) * 100 : null;
            }
        }

        return view("reports.comparison", compact(
            "comparisonData", 
            "selectedYear", 
            "selectedCostCenterId", 
            "accessibleCostCenters"
        ));
    }

    /**
     * Provide data for charts (via AJAX).
     */
    public function getChartData(Request $request)
    {
        $user = Auth::user();
        $validated = $request->validate([
            "year" => ["required", "integer"],
            "cost_center_id" => ["required", "integer", "exists:cost_centers,id"],
            // Add optional account_id filter if needed for specific account charts
            // "account_id" => ["nullable", "integer", "exists:accounts,id"],
        ]);

        // Check cost center access
        if (!$user->hasCostCenterAccess($validated["cost_center_id"])) {
            return response()->json(["message" => "Acesso não autorizado a este centro de custo."], 403);
        }

        // Fetch monthly aggregated budget data
        $budgetTotals = BudgetEntry::where("cost_center_id", $validated["cost_center_id"])
                                   ->where("year", $validated["year"])
                                   // ->when($request->account_id, function ($query, $accountId) {
                                   //     return $query->where("account_id", $accountId);
                                   // })
                                   ->selectRaw("month, SUM(value) as total_budget")
                                   ->groupBy("month")
                                   ->orderBy("month")
                                   ->pluck("total_budget", "month")
                                   ->all();

        // Fetch monthly aggregated forecast data
        $forecastTotals = ForecastEntry::where("cost_center_id", $validated["cost_center_id"])
                                     ->where("year", $validated["year"])
                                     // ->when($request->account_id, function ($query, $accountId) {
                                     //     return $query->where("account_id", $accountId);
                                     // })
                                     ->selectRaw("month, SUM(value) as total_forecast")
                                     ->groupBy("month")
                                     ->orderBy("month")
                                     ->pluck("total_forecast", "month")
                                     ->all();

        $months = range(1, 12);
        $budgetData = [];
        $forecastData = [];

        foreach ($months as $month) {
            $budgetData[] = $budgetTotals[$month] ?? 0;
            $forecastData[] = $forecastTotals[$month] ?? 0;
        }

        return response()->json([
            "labels" => ["Jan", "Fev", "Mar", "Abr", "Mai", "Jun", "Jul", "Ago", "Set", "Out", "Nov", "Dez"],
            "datasets" => [
                [
                    "label" => "Orçamento",
                    "data" => $budgetData,
                    "borderColor" => "rgb(54, 162, 235)",
                    "backgroundColor" => "rgba(54, 162, 235, 0.5)",
                ],
                [
                    "label" => "Previsão",
                    "data" => $forecastData,
                    "borderColor" => "rgb(255, 99, 132)",
                    "backgroundColor" => "rgba(255, 99, 132, 0.5)",
                ]
            ]
        ]);
    }
}
