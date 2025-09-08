<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Two Factor Authentication Setup') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="mb-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">
                            {{ __('Enable Two Factor Authentication') }}
                        </h3>
                        <p class="text-sm text-gray-600 mb-6">
                            {{ __('Two factor authentication adds an additional layer of security to your account by requiring you to provide a six digit token from your phone in addition to your password when signing in.') }}
                        </p>
                    </div>

                    @if (session('status'))
                        <div class="mb-4 font-medium text-sm text-green-600">
                            {{ session('status') }}
                        </div>
                    @endif

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <!-- QR Code Section -->
                        <div>
                            <h4 class="text-md font-medium text-gray-900 mb-4">
                                {{ __('Step 1: Scan QR Code') }}
                            </h4>
                            <p class="text-sm text-gray-600 mb-4">
                                {{ __('Scan the following QR code using your phone\'s authenticator application (Google Authenticator, Authy, etc.).') }}
                            </p>
                            <div class="bg-white p-4 rounded-lg border border-gray-200 inline-block">
                                <img src="https://api.qrserver.com/v1/create-qr-code/?size=200x200&data={{ urlencode($qrCodeUrl) }}" alt="QR Code" class="w-48 h-48">
                            </div>
                            <div class="mt-4">
                                <p class="text-sm text-gray-600 mb-2">
                                    {{ __('If you cannot scan the QR code, you can manually enter this secret key:') }}
                                </p>
                                <div class="bg-gray-100 p-3 rounded font-mono text-sm break-all">
                                    {{ $secret }}
                                </div>
                            </div>
                        </div>

                        <!-- Verification Section -->
                        <div>
                            <h4 class="text-md font-medium text-gray-900 mb-4">
                                {{ __('Step 2: Verify Setup') }}
                            </h4>
                            <p class="text-sm text-gray-600 mb-4">
                                {{ __('Enter the six digit code from your authenticator app and your current password to enable two factor authentication.') }}
                            </p>

                            <form method="POST" action="{{ route('two-factor.enable') }}">
                                @csrf

                                <!-- Authentication Code -->
                                <div class="mb-4">
                                    <x-input-label for="code" :value="__('Authentication Code')" />
                                    <x-text-input id="code" class="block mt-1 w-full" type="text" name="code" :value="old('code')" required autofocus autocomplete="off" maxlength="6" placeholder="123456" />
                                    <x-input-error :messages="$errors->get('code')" class="mt-2" />
                                </div>

                                <!-- Current Password -->
                                <div class="mb-6">
                                    <x-input-label for="password" :value="__('Current Password')" />
                                    <x-text-input id="password" class="block mt-1 w-full" type="password" name="password" required autocomplete="current-password" />
                                    <x-input-error :messages="$errors->get('password')" class="mt-2" />
                                </div>

                                <div class="flex items-center justify-between">
                                    <a href="{{ route('dashboard') }}" class="text-sm text-gray-600 hover:text-gray-900 underline">
                                        {{ __('Cancel') }}
                                    </a>
                                    <x-primary-button>
                                        {{ __('Enable Two Factor Authentication') }}
                                    </x-primary-button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>