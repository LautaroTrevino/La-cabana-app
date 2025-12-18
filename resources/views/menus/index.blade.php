@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Gestión de Menús</h2>
            <p class="text-muted small mb-0">Selecciona una categoría para ver y editar sus recetas diarias.</p>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card shadow-sm border-0">
        {{-- PESTAÑAS SUPERIORES --}}
        <div class="card-header bg-white border-bottom-0 pb-0">
            <ul class="nav nav-tabs card-header-tabs" id="menuTabs" role="tablist">
                @foreach($tiposMenu as $index => $tipo)
                    @php 
                        $targetId = Str::slug($tipo); 
                        $isActive = $index === 0 ? 'active' : '';
                    @endphp
                    <li class="nav-item" role="presentation">
                        <button class="nav-link {{ $isActive }} fw-bold" 
                                id="{{ $targetId }}-tab" 
                                data-bs-toggle="tab" 
                                data-bs-target="#{{ $targetId }}" 
                                type="button" 
                                role="tab">
                            {{ $tipo }}
                        </button>
                    </li>
                @endforeach
            </ul>
        </div>

        <div class="card-body">
            <div class="tab-content" id="menuTabsContent">
                @foreach($tiposMenu as $index => $tipo)
                    @php 
                        $targetId = Str::slug($tipo);
                        $isActive = $index === 0 ? 'show active' : '';
                        $menusDelTipo = $menus->where('type', $tipo);
                    @endphp

                    <div class="tab-pane fade {{ $isActive }}" id="{{ $targetId }}" role="tabpanel">
                        
                        {{-- BOTÓN AGREGAR MENU (Especifico para esta pestaña) --}}
                        <div class="d-flex justify-content-end mb-3">
                            <button type="button" 
                                    class="btn btn-success btn-sm fw-bold" 
                                    onclick="abrirModalCrear('{{ $tipo }}')">
                                <i class="bi bi-plus-circle"></i> Agregar Menú a {{ $tipo }}
                            </button>
                        </div>

                        @if($menusDelTipo->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th class="ps-4 text-center" width="10%">Día</th>
                                            <th width="70%">Nombre del Menú</th>
                                            <th class="text-end pe-4">Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($menusDelTipo as $menu)
                                            <tr>
                                                <td class="text-center">
                                                    <span class="badge bg-light text-dark border">
                                                        {{ $menu->day_number }}
                                                    </span>
                                                </td>
                                                <td class="fw-bold">
                                                    {{ $menu->name }}
                                                </td>
                                                <td class="text-end pe-4">
                                                    <a href="{{ route('menus.edit', $menu->id) }}" class="btn btn-sm btn-outline-primary">
                                                        <i class="bi bi-pencil-square"></i> Editar Ingredientes
                                                    </a>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="text-center py-5 text-muted bg-light rounded border border-dashed">
                                <i class="bi bi-journal-plus fs-1 d-block mb-3 text-secondary"></i>
                                <p class="mb-2">No hay menús cargados para <strong>{{ $tipo }}</strong>.</p>
                                <button type="button" class="btn btn-outline-success btn-sm" onclick="abrirModalCrear('{{ $tipo }}')">
                                    ¡Crea el primero ahora!
                                </button>
                            </div>
                        @endif

                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>

{{-- MODAL PARA CREAR NUEVO MENÚ --}}
<div class="modal fade" id="createMenuModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title">Nuevo Menú</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('menus.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    
                    {{-- Tipo (Se llena solo con JS) --}}
                    <div class="mb-3">
                        <label class="form-label fw-bold">Categoría</label>
                        <input type="text" id="modalTypeDisplay" class="form-control bg-light" readonly>
                        <input type="hidden" name="type" id="modalTypeInput">
                    </div>

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold">Día N°</label>
                            <input type="number" name="day_number" class="form-control" placeholder="Ej: 1" required min="1" max="31">
                        </div>
                        <div class="col-md-8 mb-3">
                            <label class="form-label fw-bold">Nombre del Plato</label>
                            <input type="text" name="name" class="form-control" placeholder="Ej: FIDEOS CON TUCO" required>
                        </div>
                    </div>
                    
                    <div class="alert alert-info d-flex align-items-center small py-2">
                        <i class="bi bi-info-circle-fill me-2"></i>
                        <div>Al guardar, irás directo a cargar los ingredientes.</div>
                    </div>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success fw-bold">Crear y Editar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function abrirModalCrear(tipo) {
        // Ponemos el tipo en el input visible y en el oculto
        document.getElementById('modalTypeDisplay').value = tipo;
        document.getElementById('modalTypeInput').value = tipo;

        // Abrimos el modal
        var myModal = new bootstrap.Modal(document.getElementById('createMenuModal'));
        myModal.show();
    }
</script>
@endsection