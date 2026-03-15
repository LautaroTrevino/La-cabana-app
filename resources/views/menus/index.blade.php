@extends('layouts.app')

@section('content')
<div class="container py-4">
    
    {{-- Título y Botón Principal --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold text-gray-800">Gestión de Menús</h2>
            <p class="text-muted mb-0">Administra las recetas y porciones por nivel educativo.</p>
        </div>
        
        <div class="d-flex gap-2">
            <a href="{{ route('ingredients.index') }}" class="btn btn-outline-secondary fw-bold shadow-sm">
                <i class="bi bi-egg-fried"></i> Gestionar Ingredientes
            </a>
            {{-- ESTE BOTÓN AHORA TE LLEVA A LA PÁGINA COMPLETA, NO ABRE MODAL --}}
            <a href="{{ route('menus.create') }}" class="btn btn-success fw-bold shadow-sm">
                <i class="bi bi-plus-lg"></i> Nuevo Menú Completo
            </a>
        </div>
    </div>

    {{-- Mensajes de Éxito --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show shadow-sm border-0" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- PESTAÑAS POR TIPO --}}
    <div class="card shadow-sm border-0">
        <div class="card-header bg-white border-bottom-0 pb-0">
            <ul class="nav nav-tabs card-header-tabs" id="menuTabs" role="tablist">
                @foreach($tiposMenu as $index => $tipo)
                    @php 
                        $targetId = \Illuminate\Support\Str::slug($tipo); 
                        $isActive = $index === 0 ? 'active' : '';
                    @endphp
                    <li class="nav-item" role="presentation">
                        <button class="nav-link {{ $isActive }} fw-bold" 
                                id="{{ $targetId }}-tab" 
                                data-bs-toggle="tab" 
                                data-bs-target="#{{ $targetId }}" 
                                type="button">
                            {{ $tipo }}
                        </button>
                    </li>
                @endforeach
            </ul>
        </div>

        <div class="card-body bg-light">
            <div class="tab-content" id="menuTabsContent">
                @foreach($tiposMenu as $index => $tipo)
                    @php 
                        $targetId = \Illuminate\Support\Str::slug($tipo); 
                        $isActive = $index === 0 ? 'show active' : '';
                        // Filtramos solo los menús de este tipo
                        $menusDelTipo = $menus->where('type', $tipo);
                    @endphp
                    
                    <div class="tab-pane fade {{ $isActive }}" id="{{ $targetId }}" role="tabpanel">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body p-0">
                                @if($menusDelTipo->isEmpty())
                                    <div class="text-center py-5">
                                        <i class="bi bi-journal-x fs-1 text-muted mb-2"></i>
                                        <p class="text-muted">No hay menús cargados para <strong>{{ $tipo }}</strong>.</p>
                                        <a href="{{ route('menus.create') }}" class="btn btn-outline-success btn-sm">
                                            ¡Crea el primero ahora!
                                        </a>
                                    </div>
                                @else
                                    <div class="table-responsive">
                                        <table class="table table-hover align-middle mb-0">
                                            <thead class="table-light">
                                                <tr>
                                                    <th class="text-center" width="10%">Día</th>
                                                    <th width="40%">Nombre del Menú</th>
                                                    <th class="text-center">Ingredientes</th>
                                                    <th class="text-end pe-4">Acciones</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($menusDelTipo as $menu)
                                                    <tr>
                                                        <td class="text-center">
                                                            <span class="badge bg-primary rounded-pill">{{ $menu->day_number }}</span>
                                                        </td>
                                                        <td class="fw-bold text-gray-800">{{ $menu->name }}</td>
                                                        <td class="text-center">
                                                            <span class="badge bg-light text-dark border">
                                                                {{ $menu->ingredients->count() }} ítems
                                                            </span>
                                                        </td>
                                                        <td class="text-end pe-4">
                                                            <a href="{{ route('menus.edit', $menu->id) }}" class="btn btn-outline-primary btn-sm me-1">
                                                                <i class="bi bi-pencil-square"></i> Editar
                                                            </a>
                                                            <form action="{{ route('menus.destroy', $menu->id) }}" method="POST" class="d-inline" onsubmit="return confirm('¿Eliminar este menú?');">
                                                                @csrf
                                                                @method('DELETE')
                                                                <button class="btn btn-outline-danger btn-sm">
                                                                    <i class="bi bi-trash"></i>
                                                                </button>
                                                            </form>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
@endsection