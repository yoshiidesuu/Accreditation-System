<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Two Factor Recovery Codes') }}
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
                        <h3 class="text-lg font-medium text-gray-900 mb-4">
                            {{ __('Recovery Codes') }}
                        </h3>
                        <div class="bg-yellow-50 border border-yellow-200 rounded-md p-4 mb-6">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <h3 class="text-sm font-medium text-yellow-800">
                                        {{ __('Important Security Information') }}
                                    </h3>
                                    <div class="mt-2 text-sm text-yellow-700">
                                        <p>{{ __('Store these recovery codes in a secure password manager. They can be used to recover access to your account if your two factor authentication device is lost. Each code can only be used once.') }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                        <!-- Recovery Codes Display -->
                        <div>
                            <h4 class="text-md font-medium text-gray-900 mb-4">
                                {{ __('Your Recovery Codes') }}
                            </h4>
                            <div class="bg-gray-100 p-4 rounded-lg">
                                <div class="grid grid-cols-1 gap-2 font-mono text-sm">
                                    @foreach ($recoveryCodes as $code)
                                        <div class="bg-white p-3 rounded border text-center">
                                            {{ $code }}
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                            
                            <div class="mt-4 flex space-x-3">
                                <button onclick="printCodes()" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                                    </svg>
                                    {{ __('Print Codes') }}
                                </button>
                                
                                <button onclick="copyCodes()" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-500 active:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                                    </svg>
                                    {{ __('Copy All') }}
                                </button>
                            </div>
                        </div>

                        <!-- Regenerate Codes Section -->
                        <div>
                            <h4 class="text-md font-medium text-gray-900 mb-4">
                                {{ __('Regenerate Recovery Codes') }}
                            </h4>
                            <p class="text-sm text-gray-600 mb-4">
                                {{ __('If you have lost your recovery codes or suspect they have been compromised, you can regenerate them. This will invalidate all existing recovery codes.') }}
                            </p>
                            
                            <form method="POST" action="{{ route('two-factor.recovery-codes.regenerate') }}" onsubmit="return confirm('{{ __('Are you sure you want to regenerate your recovery codes? This will invalidate all existing codes.') }}')">
                                @csrf

                                <div class="mb-4">
                                    <x-input-label for="regenerate_password" :value="__('Current Password')" />
                                    <x-text-input id="regenerate_password" class="block mt-1 w-full" type="password" name="password" required autocomplete="current-password" />
                                    <x-input-error :messages="$errors->get('password')" class="mt-2" />
                                </div>

                                <button type="submit" class="inline-flex items-center px-4 py-2 bg-yellow-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-yellow-500 active:bg-yellow-700 focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                    {{ __('Regenerate Recovery Codes') }}
                                </button>
                            </form>
                        </div>
                    </div>

                    <div class="mt-8 pt-6 border-t border-gray-200">
                        <a href="{{ route('two-factor.show') }}" class="text-sm text-gray-600 hover:text-gray-900 underline">
                            {{ __('← Back to Two Factor Authentication') }}
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function printCodes() {
            const codes = @json($recoveryCodes);
            const printWindow = window.open('', '_blank');
            printWindow.document.write(`
                <html>
                    <head>
                        <title>Recovery Codes - {{ config('app.name') }}</title>
                        <style>
                            body { font-family: Arial, sans-serif; padding: 20px; }
                            .header { text-align: center; margin-bottom: 30px; }
                            .codes { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; max-width: 400px; margin: 0 auto; }
                            .code { padding: 10px; border: 1px solid #ccc; text-align: center; font-family: monospace; }
                            .warning { background: #fff3cd; border: 1px solid #ffeaa7; padding: 15px; margin: 20px 0; border-radius: 5px; }
                        </style>
                    </head>
                    <body>
                        <div class="header">
                            <h1>{{ config('app.name') }} - Recovery Codes</h1>
                            <p>Generated on: ${new Date().toLocaleDateString()}</p>
                        </div>
                        <div class="warning">
                            <strong>Important:</strong> Store these codes in a secure location. Each code can only be used once.
                        </div>
                        <div class="codes">
                            ${codes.map(code => `<div class="code">${code}</div>`).join('')}
                        </div>
                    </body>
                </html>
            `);
            printWindow.document.close();
            printWindow.print();
        }

        function copyCodes() {
            const codes = @json($recoveryCodes);
            const codesText = codes.join('\n');
            
            if (navigator.clipboard) {
                navigator.clipboard.writeText(codesText).then(() => {
                    alert('{{ __('Recovery codes copied to clipboard!') }}');
                });
            } else {
                // Fallback for older browsers
                const textArea = document.createElement('textarea');
                textArea.value = codesText;
                document.body.appendChild(textArea);
                textArea.select();
                document.execCommand('copy');
                document.body.removeChild(textArea);
                alert('{{ __('Recovery codes copied to clipboard!') }}');
            }
        }
    </script>
</x-app-layout>