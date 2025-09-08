<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900">
            {{ __('Two Factor Authentication') }}
        </h2>

        <p class="mt-1 text-sm text-gray-600">
            {{ __('Add additional security to your account using two factor authentication.') }}
        </p>
    </header>

    <div class="mt-6">
        @if (Auth::user()->two_factor_enabled)
            <!-- 2FA is enabled -->
            <div class="flex items-center space-x-3">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                    </svg>
                </div>
                <div class="flex-1">
                    <p class="text-sm font-medium text-gray-900">
                        {{ __('Two factor authentication is enabled.') }}
                    </p>
                    <p class="text-sm text-gray-600">
                        {{ __('Your account is protected with two factor authentication.') }}
                    </p>
                </div>
            </div>

            <div class="mt-4 flex items-center space-x-4">
                <a href="{{ route('two-factor.show') }}" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                    {{ __('Manage Two Factor Authentication') }}
                </a>
                
                <a href="{{ route('two-factor.recovery-codes') }}" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150">
                    {{ __('Show Recovery Codes') }}
                </a>
            </div>
        @else
            <!-- 2FA is not enabled -->
            <div class="flex items-center space-x-3">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                    </svg>
                </div>
                <div class="flex-1">
                    <p class="text-sm font-medium text-gray-900">
                        {{ __('Two factor authentication is not enabled.') }}
                    </p>
                    <p class="text-sm text-gray-600">
                        {{ __('When two factor authentication is enabled, you will be prompted for a secure, random token during authentication. You may retrieve this token from your phone\'s Google Authenticator application.') }}
                    </p>
                </div>
            </div>

            <div class="mt-4">
                <a href="{{ route('two-factor.show') }}" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                    {{ __('Enable Two Factor Authentication') }}
                </a>
            </div>
        @endif
    </div>
</section>