@extends('layouts.app')

@section('content')
<div class="container py-4">
    
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold text-gray-800"><i class="bi bi-basket3 text-primary"></i> Base de Ingredientes</h2>
            <p class="text-muted small mb-0">Gestiona los insumos disponibles para crear tus menús.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('menus.index') }}" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Volver a Menús
            </a>
            <button type="button" class="btn btn-primary fw-bold" data-bs-toggle="modal" data-bs-target="#createModal">
                <i class="bi bi-plus-lg"></i> Nuevo Ingrediente
            </button>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show shadow-sm">
            <ul class="mb-0 small">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">Nombre</th>
                            <th class="text-center">Unidad Base</th>
                            <th>Descripción</th>
                            <th class="text-end pe-4">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($ingredients as $ingredient)
                            @php
                                // Mapeo visual de unidades para que se vea bonito
                                $badges = [
                                    'grams' => ['bg' => 'bg-warning', 'text' => 'text-warning', 'label' => 'Gramos (g)'],
                                    'cc'    => ['bg' => 'bg-info',    'text' => 'text-info',    'label' => 'CC / Litros'],
                                    'units' => ['bg' => 'bg-success', 'text' => 'text-success', 'label' => 'Unidades'],
                                    // Fallback para datos viejos
                                    'Kg.'   => ['bg' => 'bg-secondary', 'text' => 'text-secondary', 'label' => 'Kg (Legacy)'],
                                    'Lts.'  => ['bg' => 'bg-secondary', 'text' => 'text-secondary', 'label' => 'Lts (Legacy)'],
                                ];
                                
                                $u = $ingredient->unit_type ?? 'units';
                                $style = $badges[$u] ?? ['bg' => 'bg-secondary', 'text' => 'text-dark', 'label' => $u];
                            @endphp
                            <tr>
                                <td class="ps-4 fw-bold text-dark">{{ $ingredient->name }}</td>
                                <td class="text-center">
                                    <span class="badge {{ $style['bg'] }} bg-opacity-10 {{ $style['text'] }} border {{ $style['text'] }} border-opacity-25">
                                        {{ $style['label'] }}
                                    </span>
                                </td>
                                <td class="text-muted small">
                                    {{ $ingredient->description ?? '-' }}
                                </td>
                                <td class="text-end pe-4">
                                    <button type="button" 
                                            class="btn btn-sm btn-outline-primary me-1" 
                                            onclick="openEditModal({{ json_encode($ingredient) }})"
                                            title="Editar">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    
                                    <form action="{{ route('ingredients.destroy', $ingredient->id) }}" method="POST" class="d-inline" onsubmit="return confirm('¿Borrar {{ $ingredient->name }}?');">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Eliminar">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center py-5 text-muted">
                                    No hay ingredientes cargados aún.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- MODAL CREAR --}}
<div class="modal fade" id="createModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">Nuevo Ingrediente</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('ingredients.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Nombre</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Unidad de Medida</label>
                        <select name="unit_type" class="form-select" required>
                            <option value="grams">Gramos (g)</option>
                            <option value="cc">Centímetros Cúbicos (cc)</option>
                            <option value="units">Unidades (Un.)</option>
                        </select>
                        <div class="form-text">Esta es la unidad sugerida al usarlo en recetas.</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Descripción</label>
                        <textarea name="description" class="form-control" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- MODAL EDITAR --}}
<div class="modal fade" id="editModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title">Editar Ingrediente</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="editForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Nombre</label>
                        <input type="text" name="name" id="editName" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Unidad de Medida</label>
                        <select name="unit_type" id="editUnit" class="form-select" required>
                            <option value="grams">Gramos (g)</option>
                            <option value="cc">Centímetros Cúbicos (cc)</option>
                            <option value="units">Unidades (Un.)</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Descripción</label>
                        <textarea name="description" id="editDescription" class="form-control" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-warning fw-bold">Actualizar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function openEditModal(ingredient) {
        // Llenar el formulario del modal
        document.getElementById('editName').value = ingredient.name;
        document.getElementById('editDescription').value = ingredient.description || '';
        
        // Manejo inteligente de unidades viejas para el select
        let unit = ingredient.unit_type;
        if(unit === 'Kg.' || unit === 'Grs.') unit = 'grams';
        if(unit === 'Lts.' || unit === 'CC.') unit = 'cc';
        if(unit === 'Un.') unit = 'units';
        document.getElementById('editUnit').value = unit || 'grams';

        // Configurar la ruta del formulario
        const form = document.getElementById('editForm');
        form.action = `/ingredients/${ingredient.id}`;

        // Abrir Modal
        const modal = new bootstrap.Modal(document.getElementById('editModal'));
        modal.show();
    }
</script>
@endsection