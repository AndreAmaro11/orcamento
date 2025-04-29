<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __("Criar Novo Usuário") }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <form method="POST" action="{{ route("users.store") }}">
                        @csrf

                        <!-- Name -->
                        <div>
                            <x-input-label for="name" :value="__("Nome")" />
                            <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old("name")" required autofocus autocomplete="name" />
                            <x-input-error :messages="$errors->get("name")" class="mt-2" />
                        </div>

                        <!-- Email Address -->
                        <div class="mt-4">
                            <x-input-label for="email" :value="__("Email")" />
                            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old("email")" required autocomplete="username" />
                            <x-input-error :messages="$errors->get("email")" class="mt-2" />
                        </div>

                        <!-- Password -->
                        <div class="mt-4">
                            <x-input-label for="password" :value="__("Senha")" />
                            <x-text-input id="password" class="block mt-1 w-full"
                                            type="password"
                                            name="password"
                                            required autocomplete="new-password" />
                            <x-input-error :messages="$errors->get("password")" class="mt-2" />
                        </div>

                        <!-- Confirm Password -->
                        <div class="mt-4">
                            <x-input-label for="password_confirmation" :value="__("Confirmar Senha")" />
                            <x-text-input id="password_confirmation" class="block mt-1 w-full"
                                            type="password"
                                            name="password_confirmation" required autocomplete="new-password" />
                            <x-input-error :messages="$errors->get("password_confirmation")" class="mt-2" />
                        </div>

                        <!-- Roles -->
                        <div class="mt-4">
                            <x-input-label for="roles" :value="__("Papéis")" />
                            @foreach ($roles as $role)
                                <div class="flex items-center">
                                    <input id="role_{{ $role->id }}" type="checkbox" name="roles[]" value="{{ $role->id }}" class="rounded dark:bg-gray-900 border-gray-300 dark:border-gray-700 text-indigo-600 shadow-sm focus:ring-indigo-500 dark:focus:ring-indigo-600 dark:focus:ring-offset-gray-800">
                                    <label for="role_{{ $role->id }}" class="ml-2 text-sm text-gray-600 dark:text-gray-400">{{ $role->name }}</label>
                                </div>
                            @endforeach
                            <x-input-error :messages="$errors->get("roles")" class="mt-2" />
                        </div>

                        <!-- Cost Centers -->
                        <div class="mt-4">
                            <x-input-label for="cost_centers" :value="__("Centros de Custo (Opcional - Apenas para Editores/Visualizadores)")" />
                             <select multiple id="cost_centers" name="cost_centers[]" class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
                                @foreach ($costCenters as $costCenter)
                                    <option value="{{ $costCenter->id }}">{{ $costCenter->name }} ({{ $costCenter->code }})</option>
                                @endforeach
                            </select>
                            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Segure Ctrl (ou Cmd no Mac) para selecionar múltiplos centros de custo.</p>
                            <x-input-error :messages="$errors->get("cost_centers")" class="mt-2" />
                        </div>

                        <div class="flex items-center justify-end mt-4">
                            <a href="{{ route("users.index") }}" class="underline text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800">
                                {{ __("Cancelar") }}
                            </a>
                            <x-primary-button class="ms-4">
                                {{ __("Criar Usuário") }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

