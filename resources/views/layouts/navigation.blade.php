{{-- Estilos CSS unificados --}}
<style>
    .texto-negro-hover-gris {
        color: #000000 !important;
        transition: color 0.2s ease-in-out;
        font-weight: 600;
    }
    
    .texto-negro-hover-gris:hover {
        color: #808080 !important;
    }

    .texto-negro-hover-gris svg {
        fill: #000000 !important;
        transition: fill 0.2s ease-in-out;
    }
    
    .texto-negro-hover-gris:hover svg {
        fill: #808080 !important;
    }
</style>

<nav x-data="{ open: false }" class="bg-white dark:bg-gray-800 border-b border-gray-100 dark:border-gray-700">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-24">
            <div class="flex">
                {{-- Logo --}}
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('dashboard') }}">
                        <x-application-logo class="block h-20 w-auto fill-current texto-negro-hover-gris" />
                    </a>
                </div>

                {{-- NAVEGACIÓN DE ESCRITORIO --}}
                <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
                    
                    <x-nav-link :href="route('products.index')" :active="request()->routeIs('products.*')" class="texto-negro-hover-gris">
                        {{ __('Productos') }}
                    </x-nav-link>

                    @if(Auth::user()->role === 'admin' || Auth::user()->role === 'usuario')
                        <x-nav-link :href="route('remitos.index')" :active="request()->routeIs('remitos.*')" class="texto-negro-hover-gris">
                            {{ __('Remitos') }}
                        </x-nav-link>
                    @endif

                    @if(Auth::user()->role === 'admin')
                        <x-nav-link :href="route('clients.index')" :active="request()->routeIs('clients.*')" class="texto-negro-hover-gris">
                            {{ __('Escuelas') }}
                        </x-nav-link>

                        <x-nav-link :href="route('menus.index')" :active="request()->routeIs('menus.*')" class="texto-negro-hover-gris">
                            {{ __('Menús') }}
                        </x-nav-link>

                        {{-- Balance unificado como nav-link --}}
                        <x-nav-link :href="route('balance.index')" :active="request()->routeIs('balance.*')" class="texto-negro-hover-gris">
                            {{ __('Balance') }}
                        </x-nav-link>

                        <x-nav-link :href="route('admin.usuarios')" :active="request()->routeIs('admin.usuarios')" class="texto-negro-hover-gris">
                            {{ __('Usuarios') }}
                        </x-nav-link>
                    @endif
                </div>
            </div>

            {{-- Menú de Usuario (Derecha) --}}
            <div class="hidden sm:flex sm:items-center sm:ms-6">
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md bg-white dark:bg-gray-800 focus:outline-none transition ease-in-out duration-150 texto-negro-hover-gris">
                            <div>{{ Auth::user()->name }} <span class="text-xs text-indigo-500">({{ ucfirst(Auth::user()->role) }})</span></div>
                            <div class="ms-1">
                                <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </div>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <x-dropdown-link :href="route('profile.edit')">
                            {{ __('Perfil') }}
                        </x-dropdown-link>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <x-dropdown-link :href="route('logout')" onclick="event.preventDefault(); this.closest('form').submit();">
                                {{ __('Cerrar Sesión') }}
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>

            {{-- Hamburguesa Móvil --}}
            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-md hover:bg-gray-100 focus:outline-none transition duration-150 ease-in-out texto-negro-hover-gris">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    {{-- MENÚ MÓVIL --}}
    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden">
        <div class="pt-2 pb-3 space-y-1">
            <x-responsive-nav-link :href="route('products.index')" :active="request()->routeIs('products.*')" class="texto-negro-hover-gris">
                {{ __('Productos') }}
            </x-responsive-nav-link>

            @if(Auth::user()->role === 'admin' || Auth::user()->role === 'usuario')
                <x-responsive-nav-link :href="route('remitos.index')" :active="request()->routeIs('remitos.*')" class="texto-negro-hover-gris">
                    {{ __('Remitos') }}
                </x-responsive-nav-link>
            @endif

            @if(Auth::user()->role === 'admin')
                <x-responsive-nav-link :href="route('clients.index')" :active="request()->routeIs('clients.*')" class="texto-negro-hover-gris">
                    {{ __('Escuelas') }}
                </x-responsive-nav-link>

                <x-responsive-nav-link :href="route('menus.index')" :active="request()->routeIs('menus.*')" class="texto-negro-hover-gris">
                    {{ __('Menús') }}
                </x-responsive-nav-link>

                <x-responsive-nav-link :href="route('balance.index')" :active="request()->routeIs('balance.*')" class="texto-negro-hover-gris">
                    {{ __('Balance') }}
                </x-responsive-nav-link>

                <x-responsive-nav-link :href="route('admin.usuarios')" :active="request()->routeIs('admin.usuarios')" class="texto-negro-hover-gris">
                    {{ __('Usuarios') }}
                </x-responsive-nav-link>
            @endif
        </div>

        {{-- Configuración de Usuario Móvil --}}
        <div class="pt-4 pb-1 border-t border-gray-200 dark:border-gray-600">
            <div class="px-4">
                <div class="font-medium text-base texto-negro-hover-gris">{{ Auth::user()->name }}</div>
                <div class="font-medium text-sm text-gray-500">{{ Auth::user()->email }}</div>
            </div>

            <div class="mt-3 space-y-1">
                <x-responsive-nav-link :href="route('profile.edit')">
                    {{ __('Perfil') }}
                </x-responsive-nav-link>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <x-responsive-nav-link :href="route('logout')" onclick="event.preventDefault(); this.closest('form').submit();">
                        {{ __('Cerrar Sesión') }}
                    </x-responsive-nav-link>
                </form>
            </div>
        </div>
    </div>
</nav>