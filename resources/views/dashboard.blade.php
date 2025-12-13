<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Panel de Control
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-bold">¡Hola, {{ Auth::user()->name }}!</h3>
                    <p class="text-gray-600">Tu rol actual es: <span class="uppercase font-bold">{{ Auth::user()->role }}</span></p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">

                @if(Auth::user()->role == 'admin' || Auth::user()->role == 'usuario')
                <a href="{{ route('remitos.index') }}" class="block p-6 bg-white border border-gray-200 rounded-lg shadow hover:bg-gray-50 transition">
                    <h5 class="mb-2 text-2xl font-bold tracking-tight text-gray-900">📦 Remitos</h5>
                    <p class="font-normal text-gray-700">Crear, ver y gestionar los remitos de stock.</p>
                </a>
                @endif

                @if(Auth::user()->role == 'admin')
                <a href="/admin/usuarios" class="block p-6 bg-white border border-gray-200 rounded-lg shadow hover:bg-gray-50 transition">
                    <h5 class="mb-2 text-2xl font-bold tracking-tight text-gray-900">👥 Usuarios</h5>
                    <p class="font-normal text-gray-700">Gestionar empleados y permisos de acceso.</p>
                </a>
                @endif

                <a href="{{ route('profile.edit') }}" class="block p-6 bg-white border border-gray-200 rounded-lg shadow hover:bg-gray-50 transition">
                    <h5 class="mb-2 text-2xl font-bold tracking-tight text-gray-900">⚙️ Mi Cuenta</h5>
                    <p class="font-normal text-gray-700">Cambiar contraseña o datos personales.</p>
                </a>

            </div>
        </div>
    </div>
</x-app-layout>