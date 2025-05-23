<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __("Criar Novo Centro de Custo") }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <form method="POST" action="{{ route("cost-centers.store") }}">
                        @csrf

                        <!-- Code -->
                        <div>
                            <x-input-label for="code" :value="__("Código")" />
                            <x-text-input id="code" class="block mt-1 w-full" type="text" name="code" :value="old("code")" required autofocus />
                            <x-input-error :messages="$errors->get("code")" class="mt-2" />
                        </div>

                        <!-- Name -->
                        <div class="mt-4">
                            <x-input-label for="name" :value="__("Nome")" />
                            <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old("name")" required />
                            <x-input-error :messages="$errors->get("name")" class="mt-2" />
                        </div>

                        <!-- Description -->
                        <div class="mt-4">
                            <x-input-label for="description" :value="__("Descrição (Opcional)")" />
                            <textarea id="description" name="description" rows="3" class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">{{ old("description") }}</textarea>
                            <x-input-error :messages="$errors->get("description")" class="mt-2" />
                        </div>

                        <div class="flex items-center justify-end mt-4">
                            <a href="{{ route("cost-centers.index") }}" class="underline text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800">
                                {{ __("Cancelar") }}
                            </a>
                            <x-primary-button class="ms-4">
                                {{ __("Criar Centro de Custo") }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

