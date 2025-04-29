<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __("Orçamento") }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-full mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">

                    {{-- Filtros --}}
                    <form method="GET" action="{{ route("budget.index") }}" class="mb-6 flex flex-wrap items-end space-x-0 sm:space-x-4 space-y-4 sm:space-y-0">
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
                                Filtrar
                            </button>
                        </div>
                    </form>

                    {{-- Tabela de Orçamento --}}
                    @if ($selectedCostCenterId)
                        <div class="overflow-x-auto" x-data="budgetTable({{ $selectedYear }}, {{ $selectedCostCenterId }})">
                            <div x-show="message" :class="messageType === "success" ? "mb-4 p-4 bg-green-100 dark:bg-green-900 text-green-700 dark:text-green-300 rounded" : "mb-4 p-4 bg-red-100 dark:bg-red-900 text-red-700 dark:text-red-300 rounded" x-text="message" x-transition></div>
                            
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 border border-gray-200 dark:border-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-700">
                                    <tr>
                                        <th scope="col" class="sticky left-0 z-10 bg-gray-50 dark:bg-gray-700 px-3 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider border-r dark:border-gray-600">Conta</th>
                                        <th scope="col" class="sticky left-[150px] z-10 bg-gray-50 dark:bg-gray-700 px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider border-r dark:border-gray-600">Nome da Conta</th>
                                        @php $months = ["Jan", "Fev", "Mar", "Abr", "Mai", "Jun", "Jul", "Ago", "Set", "Out", "Nov", "Dez"]; @endphp
                                        @foreach ($months as $month)
                                            <th scope="col" class="px-3 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">{{ $month }}</th>
                                        @endforeach
                                        <th scope="col" class="px-3 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider border-l dark:border-gray-600">Total</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                    @forelse ($accounts as $account)
                                        @php $rowTotal = 0; @endphp
                                        <tr>
                                            <td class="sticky left-0 z-10 bg-white dark:bg-gray-800 px-3 py-2 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100 border-r dark:border-gray-600">{{ $account->code }}</td>
                                            <td class="sticky left-[150px] z-10 bg-white dark:bg-gray-800 px-6 py-2 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300 border-r dark:border-gray-600">{{ $account->name }}</td>
                                            @for ($month = 1; $month <= 12; $month++)
                                                @php 
                                                    $value = $budgetData[$account->id][$month] ?? null;
                                                    $rowTotal += $value ?? 0;
                                                    $cellId = "budget-".$selectedYear."-".$month."-".$selectedCostCenterId."-".$account->id;
                                                @endphp
                                                <td class="px-1 py-1 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300 text-center relative group">
                                                    @if ($canEdit)
                                                        <input type="number" step="0.01" 
                                                               id="{{ $cellId }}"
                                                               class="w-24 p-1 text-right border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm text-sm"
                                                               x-on:change="updateValue({{ $month }}, {{ $account->id }}, $event.target.value)"
                                                               value="{{ $value !== null ? number_format($value, 2, ".", "") : "" }}">
                                                    @else
                                                        <span id="{{ $cellId }}" class="block w-24 p-1 text-right">
                                                            {{ $value !== null ? number_format($value, 2, ",", ".") : "-" }}
                                                        </span>
                                                    @endif
                                                    {{-- Comment Button --}}
                                                    <button @click="openComments({{ $month }}, {{ $account->id }}, "budget")" 
                                                            class="absolute top-0 right-0 -mt-1 -mr-1 p-0.5 bg-blue-100 dark:bg-blue-900 rounded-full text-blue-600 dark:text-blue-300 opacity-0 group-hover:opacity-100 focus:opacity-100 transition-opacity">
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" viewBox="0 0 20 20" fill="currentColor">
                                                            <path fill-rule="evenodd" d="M18 10c0 3.866-3.582 7-8 7s-8-3.134-8-7c0-3.866 3.582-7 8-7s8 3.134 8 7zM7 9H5v2h2V9zm8 0h-2v2h2V9zm-4 0H9v2h2V9z" clip-rule="evenodd" />
                                                        </svg>
                                                    </button>
                                                </td>
                                            @endfor
                                            <td class="px-3 py-2 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300 text-right font-medium border-l dark:border-gray-600">{{ number_format($rowTotal, 2, ",", ".") }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="15" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300 text-center">
                                                Nenhuma conta encontrada.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        {{-- Comment Modal --}}
                        <div x-show="showCommentModal" 
                             x-transition:enter="ease-out duration-300" 
                             x-transition:enter-start="opacity-0" 
                             x-transition:enter-end="opacity-100" 
                             x-transition:leave="ease-in duration-200" 
                             x-transition:leave-start="opacity-100" 
                             x-transition:leave-end="opacity-0" 
                             class="fixed inset-0 z-50 overflow-y-auto" 
                             aria-labelledby="modal-title" 
                             role="dialog" 
                             aria-modal="true"
                             style="display: none;" {{-- Prevent initial flash --}}>
                            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                                {{-- Background overlay --}}
                                <div @click="showCommentModal = false" class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>

                                {{-- Modal panel --}}
                                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                                <div class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                                    <div class="bg-white dark:bg-gray-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                                        <div class="sm:flex sm:items-start">
                                            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                                                <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-gray-100" id="modal-title">
                                                    Comentários (<span x-text="commentContext.accountCode"></span> - <span x-text="commentContext.monthName"></span> <span x-text="commentContext.year"></span>)
                                                </h3>
                                                <div class="mt-4 h-64 overflow-y-auto border dark:border-gray-600 rounded p-2 space-y-2">
                                                    <template x-if="loadingComments">
                                                        <p class="text-gray-500 dark:text-gray-400">Carregando comentários...</p>
                                                    </template>
                                                    <template x-if="!loadingComments && comments.length === 0">
                                                        <p class="text-gray-500 dark:text-gray-400">Nenhum comentário para esta célula.</p>
                                                    </template>
                                                    <template x-for="comment in comments" :key="comment.id">
                                                        <div class="text-sm border-b dark:border-gray-700 pb-1">
                                                            <p class="text-gray-800 dark:text-gray-200" x-text="comment.comment"></p>
                                                            <p class="text-xs text-gray-500 dark:text-gray-400">- <span x-text="comment.user.name"></span> em <span x-text="new Date(comment.created_at).toLocaleString()"></span></p>
                                                        </div>
                                                    </template>
                                                </div>
                                                <div class="mt-4">
                                                    <label for="new_comment" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Novo Comentário</label>
                                                    <textarea x-model="newCommentText" id="new_comment" rows="3" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm"></textarea>
                                                    <div x-show="commentError" class="mt-1 text-sm text-red-600 dark:text-red-400" x-text="commentError"></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="bg-gray-50 dark:bg-gray-700 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                                        <button @click="submitComment()" :disabled="submittingComment || !newCommentText.trim()" type="button" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm disabled:opacity-50">
                                            <span x-show="!submittingComment">Salvar Comentário</span>
                                            <span x-show="submittingComment">Salvando...</span>
                                        </button>
                                        <button @click="showCommentModal = false" type="button" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 dark:border-gray-600 shadow-sm px-4 py-2 bg-white dark:bg-gray-800 text-base font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                                            Cancelar
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                    @else
                        <p class="text-center text-gray-500 dark:text-gray-400">Por favor, selecione um centro de custo para visualizar o orçamento.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <script>
        const monthNames = ["", "Jan", "Fev", "Mar", "Abr", "Mai", "Jun", "Jul", "Ago", "Set", "Out", "Nov", "Dez"];

        function budgetTable(selectedYear, selectedCostCenterId) {
            return {
                message: "",
                messageType: "",
                showCommentModal: false,
                comments: [],
                loadingComments: false,
                submittingComment: false,
                newCommentText: "",
                commentError: "",
                commentContext: { year: null, month: null, costCenterId: null, accountId: null, entryType: null, accountCode: "", monthName: "" },
                selectedYear: selectedYear,
                selectedCostCenterId: selectedCostCenterId,

                updateValue(month, accountId, value) {
                    this.message = "Salvando...";
                    this.messageType = "info";

                    fetch("{{ route("budget.update") }}", {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/json",
                            "X-CSRF-TOKEN": document.querySelector("meta[name=\"csrf-token\"]").getAttribute("content"),
                            "Accept": "application/json",
                        },
                        body: JSON.stringify({
                            year: this.selectedYear,
                            month: month,
                            cost_center_id: this.selectedCostCenterId,
                            account_id: accountId,
                            value: value === "" ? null : value, // Send null if input is empty
                        }),
                    })
                    .then(response => response.json().then(data => ({ status: response.status, body: data })))
                    .then(({ status, body }) => {
                        this.message = body.message;
                        if (status === 200) {
                            this.messageType = "success";
                        } else {
                            this.messageType = "error";
                            // Revert value on error?
                            // document.getElementById(`budget-${this.selectedYear}-${month}-${this.selectedCostCenterId}-${accountId}`).value = ... // Need original value
                        }
                        setTimeout(() => this.message = "", 3000);
                    })
                    .catch(error => {
                        console.error("Error:", error);
                        this.message = "Erro de comunicação ao salvar.";
                        this.messageType = "error";
                        setTimeout(() => this.message = "", 3000);
                    });
                },

                openComments(month, accountId, entryType) {
                    this.commentContext.year = this.selectedYear;
                    this.commentContext.month = month;
                    this.commentContext.costCenterId = this.selectedCostCenterId;
                    this.commentContext.accountId = accountId;
                    this.commentContext.entryType = entryType;
                    this.commentContext.monthName = monthNames[month];
                    // Find account code (assuming accounts data is available or fetchable)
                    // This is a simplification; ideally, pass account code/name directly or fetch it
                    this.commentContext.accountCode = document.querySelector(`#budget-${this.selectedYear}-${month}-${this.selectedCostCenterId}-${accountId}`)?.closest("tr")?.querySelector("td:first-child")?.textContent || "Conta"; 

                    this.showCommentModal = true;
                    this.loadComments();
                },

                loadComments() {
                    this.loadingComments = true;
                    this.comments = [];
                    this.commentError = "";
                    const params = new URLSearchParams({
                        year: this.commentContext.year,
                        month: this.commentContext.month,
                        cost_center_id: this.commentContext.costCenterId,
                        account_id: this.commentContext.accountId,
                        entry_type: this.commentContext.entryType,
                    }).toString();

                    fetch(`{{ route("comments.index") }}?${params}`, {
                        headers: {
                            "Accept": "application/json",
                        }
                    })
                    .then(response => {
                        if (!response.ok) throw new Error("Erro ao carregar comentários.");
                        return response.json();
                    })
                    .then(data => {
                        this.comments = data;
                    })
                    .catch(error => {
                        console.error("Error loading comments:", error);
                        this.commentError = error.message || "Não foi possível carregar os comentários.";
                    })
                    .finally(() => {
                        this.loadingComments = false;
                    });
                },

                submitComment() {
                    if (!this.newCommentText.trim()) return;
                    this.submittingComment = true;
                    this.commentError = "";

                    fetch("{{ route("comments.store") }}", {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/json",
                            "X-CSRF-TOKEN": document.querySelector("meta[name=\"csrf-token\"]").getAttribute("content"),
                            "Accept": "application/json",
                        },
                        body: JSON.stringify({
                            year: this.commentContext.year,
                            month: this.commentContext.month,
                            cost_center_id: this.commentContext.costCenterId,
                            account_id: this.commentContext.accountId,
                            entry_type: this.commentContext.entryType,
                            comment: this.newCommentText.trim(),
                        }),
                    })
                    .then(response => {
                        if (!response.ok) {
                            return response.json().then(data => { throw new Error(data.message || "Erro ao salvar comentário."); });
                        }
                        return response.json();
                    })
                    .then(newComment => {
                        this.comments.push(newComment); // Add the new comment to the list
                        this.newCommentText = ""; // Clear the input
                    })
                    .catch(error => {
                        console.error("Error submitting comment:", error);
                        this.commentError = error.message || "Não foi possível salvar o comentário.";
                    })
                    .finally(() => {
                        this.submittingComment = false;
                    });
                }
            };
        }
        // Add CSRF token meta tag if not already present in the main layout
        if (!document.querySelector("meta[name=\"csrf-token\"]")) {
            let meta = document.createElement("meta");
            meta.name = "csrf-token";
            meta.content = "{{ csrf_token() }}";
            document.getElementsByTagName("head")[0].appendChild(meta);
        }
    </script>
</x-app-layout>

