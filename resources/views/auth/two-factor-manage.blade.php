<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Two Factor Authentication') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    @if (session('status'))
                        <div class="mb-4 font-medium text-sm text-green-600">
                            {{ session('status') }}
                        </div>
                    @endif

                    <div class="mb-6">
                        <div class="flex items-center mb-4">
                            <div class="flex-shrink-0">
                                <svg class="h-8 w-8 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <div class="ml-3">
                                <h3 class="text-lg font-medium text-gray-900">
                                    {{ __('Two Factor Authentication Enabled') }}
                                </h3>
                                <p class="text-sm text-gray-600">
                                    {{ __('Two factor authentication is currently enabled for your account.') }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Recovery Codes Section -->
                        <div class="bg-gray-50 p-6 rounded-lg">
                            <h4 class="text-md font-medium text-gray-900 mb-3">
                                {{ __('Recovery Codes') }}
                            </h4>
                            <p class="text-sm text-gray-600 mb-4">
                                {{ __('Store these recovery codes in a secure password manager. They can be used to recover access to your account if your two factor authentication device is lost.') }}
                            </p>
                            <div class="space-y-2">
                                <a href="{{ route('two-factor.recovery-codes') }}" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                    {{ __('Show Recovery Codes') }}
                                </a>
                            </div>
                        </div>

                        <!-- Disable 2FA Section -->
                        <div class="bg-red-50 p-6 rounded-lg">
                            <h4 class="text-md font-medium text-gray-900 mb-3">
                                {{ __('Disable Two Factor Authentication') }}
                            </h4>
                            <p class="text-sm text-gray-600 mb-4">
                                {{ __('If you wish to disable two factor authentication, you can do so below. We recommend keeping it enabled for better security.') }}
                            </p>
                            
                            <form method="POST" action="{{ route('two-factor.disable') }}" onsubmit="return confirm('{{ __('Are you sure you want to disable two factor authentication? This will make your account less secure.') }}')">
                                @csrf
                                @method('DELETE')

                                <div class="mb-4">
                                    <x-input-label for="disable_password" :value="__('Current Password')" />
                                    <x-text-input id="disable_password" class="block mt-1 w-full" type="password" name="password" required autocomplete="current-password" />
                                    <x-input-error :messages="$errors->get('password')" class="mt-2" />
                                </div>

                                <button type="submit" class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-500 active:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                    {{ __('Disable Two Factor Authentication') }}
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>