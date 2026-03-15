@extends('layouts.app')

@section('content')
<div class="container py-4">
    
    {{-- ENCABEZADO Y FILTROS --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold text-gray-800">
            <i class="bi bi-files text-primary"></i> Gestión de Entregas
        </h2>
        
        {{-- BOTONES DE ACCIÓN --}}
        <div class="d-flex gap-2">
            <a href="{{ route('ordenes.create') }}" class="btn btn-dark shadow-sm">
                <i class="bi bi-upc-scan"></i> Nueva Salida (Escáner)
            </a>
            <a href="{{ route('remitos.create') }}" class="btn btn-success fw-bold shadow-sm">
                <i class="bi bi-plus-lg"></i> Generar Remito (Menú)
            </a>
        </div>
    </div>

    {{-- BARRA DE FILTROS --}}
    <div class="card mb-4 border-0 shadow-sm bg-light">
        <div class="card-body py-3">
            <form action="{{ route('remitos.index') }}" method="GET" class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label class="form-label small fw-bold text-muted">Filtrar por Escuela</label>
                    <select name="client_id" class="form-select" onchange="this.form.submit()">
                        <option value="">-- Todas las escuelas --</option>
                        @foreach($clients as $c)
                            <option value="{{ $c->id }}" {{ request('client_id') == $c->id ? 'selected' : '' }}>
                                {{ $c->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-bold text-muted">Filtrar por Fecha</label>
                    <input type="date" name="date_search" class="form-control" value="{{ request('date_search') }}" onchange="this.form.submit()">
                </div>
                <div class="col-md-2">
                    <a href="{{ route('remitos.index') }}" class="btn btn-outline-secondary w-100">Limpiar</a>
                </div>
            </form>
        </div>
    </div>

    {{-- PESTAÑAS DE NAVEGACIÓN --}}
    <ul class="nav nav-tabs mb-3" id="myTab" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active fw-bold" id="remitos-tab" data-bs-toggle="tab" data-bs-target="#remitos" type="button" role="tab">
                <i class="bi bi-file-text"></i> Remitos (Menús)
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link fw-bold" id="entregas-tab" data-bs-toggle="tab" data-bs-target="#entregas" type="button" role="tab"
                    style="color: #0d6efd; border: 2px solid #0d6efd; border-bottom: none; background-color: #e8f0fe;">
                <i class="bi bi-box-seam"></i> Salidas de Depósito (Real)
            </button>
        </li>
    </ul>

    <div class="tab-content" id="myTabContent">
        
        {{-- TABLA 1: REMITOS (CALCULADOS) --}}
        <div class="tab-pane fade show active" id="remitos" role="tabpanel">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-4">Número</th>
                                    <th>Fecha</th>
                                    <th>Escuela</th>
                                    <th class="text-center">Ítems</th>
                                    <th class="text-end pe-4">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($remitos as $remito)
                                    <tr>
                                        <td class="ps-4 fw-bold text-primary">{{ $remito->number }}</td>
                                        <td>{{ \Carbon\Carbon::parse($remito->date)->format('d/m/Y') }}</td>
                                        <td>{{ $remito->client->name ?? 'N/A' }}</td>
                                        <td class="text-center">
                                            <span class="badge bg-info text-dark">{{ $remito->items->count() }} Ingredientes</span>
                                        </td>
                                        <td class="text-end pe-4">
                                            <a href="{{ route('remitos.show', $remito->id) }}" class="btn btn-sm btn-outline-primary" title="Ver Detalle">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            <a href="{{ route('remitos.print', $remito->id) }}" target="_blank" class="btn btn-sm btn-primary" title="Imprimir PDF">
                                                <i class="bi bi-printer"></i>
                                            </a>
                                            <form action="{{ route('remitos.destroy', $remito->id) }}" method="POST" class="d-inline delete-form">
                                                @csrf
                                                @method('DELETE')
                                                <button type="button"
                                                        class="btn btn-sm btn-outline-danger btn-delete"
                                                        data-number="{{ $remito->number }}"
                                                        title="Eliminar">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center py-5 text-muted">No hay remitos generados con los filtros actuales.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        {{-- TABLA 2: ORDENES DE ENTREGA (ESCÁNER) --}}
        <div class="tab-pane fade" id="entregas" role="tabpanel">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-striped align-middle mb-0">
                            <thead class="bg-dark text-white">
                                <tr>
                                    <th class="ps-4">Orden #</th>
                                    <th>Fecha Salida</th>
                                    <th>Destino</th>
                                    <th class="text-center">Productos</th>
                                    <th class="text-end pe-4">Estado</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($entregas as $entrega)
                                    <tr>
                                        <td class="ps-4 fw-bold font-monospace">{{ $entrega->number }}</td>
                                        <td>{{ \Carbon\Carbon::parse($entrega->date)->format('d/m/Y') }}</td>
                                        <td class="fw-bold">{{ $entrega->client->name ?? 'N/A' }}</td>
                                        <td class="text-center">
                                            <span class="badge bg-secondary">{{ $entrega->details->count() }} ítems</span>
                                        </td>
                                        <td class="text-end pe-4">
                                            <span class="badge bg-success"><i class="bi bi-check-circle"></i> Entregado</span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center py-5 text-muted">No hay salidas de depósito registradas.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

{{-- MODAL DE CONFIRMACIÓN PARA ELIMINAR --}}
<div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-danger text-white border-0">
                <h5 class="modal-title fw-bold">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i> Eliminar Remito
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body py-4 px-4">
                <p class="mb-1">¿Estás seguro de que querés eliminar el remito:</p>
                <p class="fw-bold text-danger fs-5 mb-3" id="deleteModalNumber"></p>
                <div class="alert alert-warning border-0 mb-0 py-2">
                    <i class="bi bi-info-circle me-1"></i>
                    Esta acción <strong>no se puede deshacer</strong> y eliminará todos sus ítems.
                </div>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                    <i class="bi bi-x-lg me-1"></i> Cancelar
                </button>
                <button type="button" id="btnConfirmDelete" class="btn btn-danger fw-bold">
                    <i class="bi bi-trash me-1"></i> Sí, eliminar
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    let formToSubmit = null;

    document.querySelectorAll('.btn-delete').forEach(btn => {
        btn.addEventListener('click', function () {
            formToSubmit = this.closest('.delete-form');
            document.getElementById('deleteModalNumber').textContent = this.dataset.number;
            new bootstrap.Modal(document.getElementById('deleteModal')).show();
        });
    });

    document.getElementById('btnConfirmDelete').addEventListener('click', function () {
        if (formToSubmit) formToSubmit.submit();
    });
</script>
@endsection