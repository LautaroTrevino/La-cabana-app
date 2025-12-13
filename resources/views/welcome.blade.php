<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>La Cabaña - Acceso</title>
        
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    
    <body class="antialiased bg-gray-50 flex items-center justify-center min-h-screen">
        
        <div class="flex flex-col items-center">
            
            <div class="mb-8">
                <img src="{{ asset('logo.png') }}" 
                     alt="Logo La Cabaña" 
                     style="width: 300px; height: auto;" 
                     class="object-contain">
            </div>

            @if (Route::has('login'))
                <div style="width: 300px;" class="flex flex-col gap-3">
                    @auth
                        <a href="{{ url('/dashboard') }}" 
                           class="block w-full rounded-md bg-gray-200 px-4 py-3 text-center text-sm font-bold text-black shadow-sm hover:bg-gray-300 transition-colors">
                            Ir al Panel de Control
                        </a>
                    @else
                        <a href="{{ route('login') }}" 
                           class="block w-full rounded-md bg-gray-200 px-4 py-3 text-center text-sm font-bold text-black shadow-sm hover:bg-gray-300 transition-colors">
                            Ingresar
                        </a>

                        @if (Route::has('register'))
                            <a href="{{ route('register') }}" 
                               class="block w-full rounded-md bg-gray-200 px-4 py-3 text-center text-sm font-bold text-black shadow-sm hover:bg-gray-300 transition-colors">
                                Registrarse
                            </a>
                        @endif
                    @endauth
                </div>
            @endif
            
            <p class="mt-8 text-center text-xs text-gray-400">
                &copy; {{ date('Y') }} La Cabaña MDP
            </p>

        </div>

    </body>
</html>