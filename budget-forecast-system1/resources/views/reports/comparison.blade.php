<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __("Relatório Comparativo: Orçamento vs. Previsão") }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-full mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">

                    {{-- Filtros --}}
                    <form method="GET" action="{{ route("reports.comparison") }}" class="mb-6 flex flex-wrap items-end space-x-0 sm:space-x-4 space-y-4 sm:space-y-0">
                        {{-- Filtro Ano --}}
                        <div class="flex-shrink-0">
                            <label for="year" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Ano</label>
                            <select id="year" name="year" class="mt-1 block w-full sm:w-auto pl-3 pr-10 py-2 text-base border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                                @for ($year = Carbon\Carbon::now()->year + 1; $year >= Carbon\Carbon::now()->year - 5; $year--)
                                    <option value="{{ $year }}" {{ $selectedYear == $year ? "selected" : "" }}>{{ $year }}</option>
                                @endfor
                            </select>
                        </div>

                        {{-- Filtro Centro de Custo --}}
                        <div class="flex-grow">
                            <label for="cost_center_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Centro de Custo</label>
                            <select id="cost_center_id" name="cost_center_id" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                                @if ($accessibleCostCenters->count() > 1 || Auth::user()->hasRole("admin"))
                                    <option value="">-- Selecione um Centro de Custo --</option>
                                @endif
                                @foreach ($accessibleCostCenters as $costCenter)
                                    <option value="{{ $costCenter->id }}" {{ $selectedCostCenterId == $costCenter->id ? "selected" : "" }}>
                                        {{ $costCenter->name }} ({{ $costCenter->code }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Botão Filtrar --}}
                        <div class="flex-shrink-0">
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-gray-800 dark:bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-white dark:text-gray-800 uppercase tracking-widest hover:bg-gray-700 dark:hover:bg-white focus:bg-gray-700 dark:focus:bg-white active:bg-gray-900 dark:active:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                                Gerar Relatório
                            </button>
                        </div>
                    </form>

                    {{-- Gráfico Comparativo --}}
                    @if ($selectedCostCenterId)
                        <div class="mb-8 p-4 border dark:border-gray-700 rounded-lg">
                            <h3 class="text-lg font-medium mb-4">Comparativo Anual (Gráfico)</h3>
                            <canvas id="comparisonChart"></canvas>
                        </div>
                    @endif

                    {{-- Tabela Comparativa --}}
                    @if ($selectedCostCenterId)
                        <div class="overflow-x-auto">
                            <h3 class="text-lg font-medium mb-4">Comparativo Detalhado (Tabela)</h3>
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 border border-gray-200 dark:border-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-700">
                                    <tr>
                                        <th scope="col" rowspan="2" class="sticky left-0 z-10 bg-gray-50 dark:bg-gray-700 px-3 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider border-r dark:border-gray-600 align-bottom">Conta</th>
                                        <th scope="col" rowspan="2" class="sticky left-[150px] z-10 bg-gray-50 dark:bg-gray-700 px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider border-r dark:border-gray-600 align-bottom">Nome da Conta</th>
                                        @php $months = ["Jan", "Fev", "Mar", "Abr", "Mai", "Jun", "Jul", "Ago", "Set", "Out", "Nov", "Dez"]; @endphp
                                        @foreach ($months as $month)
                                            <th scope="col" colspan="4" class="px-3 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider border-l dark:border-gray-600">{{ $month }}</th>
                                        @endforeach
                                        <th scope="col" colspan="4" class="px-3 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider border-l dark:border-gray-600">Total Ano</th>
                                    </tr>
                                    <tr>
                                        @for ($i = 0; $i < 13; $i++)
                                            <th scope="col" class="px-2 py-2 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider border-l dark:border-gray-600">Orç.</th>
                                            <th scope="col" class="px-2 py-2 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Prev.</th>
                                            <th scope="col" class="px-2 py-2 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Var. R$</th>
                                            <th scope="col" class="px-2 py-2 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Var. %</th>
                                        @endfor
                                    </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                    @forelse ($comparisonData as $accountData)
                                        <tr>
                                            <td class="sticky left-0 z-10 bg-white dark:bg-gray-800 px-3 py-2 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100 border-r dark:border-gray-600">{{ $accountData["account_code"] }}</td>
                                            <td class="sticky left-[150px] z-10 bg-white dark:bg-gray-800 px-6 py-2 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300 border-r dark:border-gray-600">{{ $accountData["account_name"] }}</td>
                                            @foreach ($accountData["monthly_data"] as $monthData)
                                                <td class="px-2 py-2 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300 text-right border-l dark:border-gray-600">{{ number_format($monthData["budget"], 2, ",", ".") }}</td>
                                                <td class="px-2 py-2 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300 text-right">{{ number_format($monthData["forecast"], 2, ",", ".") }}</td>
                                                <td class="px-2 py-2 whitespace-nowrap text-sm text-right {{ $monthData["variance"] < 0 ? "text-red-600 dark:text-red-400" : "text-green-600 dark:text-green-400" }}">{{ number_format($monthData["variance"], 2, ",", ".") }}</td>
                                                <td class="px-2 py-2 whitespace-nowrap text-sm text-right {{ $monthData["variance"] < 0 ? "text-red-600 dark:text-red-400" : "text-green-600 dark:text-green-400" }}">
                                                    {{ $monthData["variance_percent"] !== null ? number_format($monthData["variance_percent"], 1, ",", ".") . "%" : "-" }}
                                                </td>
                                            @endforeach
                                            {{-- Totals --}}
                                            <td class="px-2 py-2 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300 text-right font-medium border-l dark:border-gray-600">{{ number_format($accountData["budget_total"], 2, ",", ".") }}</td>
                                            <td class="px-2 py-2 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300 text-right font-medium">{{ number_format($accountData["forecast_total"], 2, ",", ".") }}</td>
                                            <td class="px-2 py-2 whitespace-nowrap text-sm text-right font-medium {{ $accountData["variance_total"] < 0 ? "text-red-600 dark:text-red-400" : "text-green-600 dark:text-green-400" }}">{{ number_format($accountData["variance_total"], 2, ",", ".") }}</td>
                                            <td class="px-2 py-2 whitespace-nowrap text-sm text-right font-medium {{ $accountData["variance_total"] < 0 ? "text-red-600 dark:text-red-400" : "text-green-600 dark:text-green-400" }}">
                                                {{ $accountData["variance_percent_total"] !== null ? number_format($accountData["variance_percent_total"], 1, ",", ".") . "%" : "-" }}
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="{{ 2 + (12 * 4) + 4 }}" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300 text-center">
                                                Nenhum dado encontrado para os filtros selecionados.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-center text-gray-500 dark:text-gray-400">Por favor, selecione um ano e um centro de custo para gerar o relatório.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Include Chart.js --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const ctx = document.getElementById("comparisonChart");
            if (ctx && {{ $selectedCostCenterId ? "true" : "false" }}) {
                // Fetch chart data via AJAX
                const params = new URLSearchParams({
                    year: {{ $selectedYear }},
                    cost_center_id: {{ $selectedCostCenterId }},
                }).toString();

                fetch(`{{ route("reports.chartData") }}?${params}`, {
                    headers: {
                        "Accept": "application/json",
                        "X-CSRF-TOKEN": document.querySelector("meta[name=\"csrf-token\"]")?.getAttribute("content") // Optional for GET, but good practice
                    }
                })
                .then(response => {
                    if (!response.ok) throw new Error("Erro ao carregar dados do gráfico.");
                    return response.json();
                })
                .then(data => {
                    new Chart(ctx, {
                        type: "line", // Or "bar"
                        data: {
                            labels: data.labels,
                            datasets: data.datasets
                        },
                        options: {
                            responsive: true,
                            plugins: {
                                legend: {
                                    position: "top",
                                },
                                title: {
                                    display: true,
                                    text: `Comparativo Orçamento vs. Previsão - {{ $selectedYear }}`
                                }
                            },
                            scales: {
                                y: {
                                    beginAtZero: true
                                }
                            }
                        }
                    });
                })
                .catch(error => {
                    console.error("Error fetching chart data:", error);
                    ctx.parentElement.innerHTML = `<p class="text-red-500">Erro ao carregar o gráfico: ${error.message}</p>`;
                });
            }
        });

        // Add CSRF token meta tag if not already present in the main layout
        if (!document.querySelector("meta[name=\"csrf-token\"]")) {
            let meta = document.createElement("meta");
            meta.name = "csrf-token";
            meta.content = "{{ csrf_token() }}";
            document.getElementsByTagName("head")[0].appendChild(meta);
        }
    </script>
</x-app-layout>

