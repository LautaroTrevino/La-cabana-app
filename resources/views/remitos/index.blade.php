@extends('layouts.app')

@section('header')
    <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
        {{ __('Control de Remitos') }}
    </h2>
@endsection

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="container-fluid"> 
            
            {{-- Encabezado --}}
            <div class="row mb-4 align-items-center">
                <div class="col-md-8">
                    <h3 class="mb-0 text-gray-800 dark:text-gray-200">Listado de Remitos</h3>
                </div>
                <div class="col-md-4 text-end">
                    <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#createRemitoModal">
                        <i class="bi bi-file-earmark-plus"></i> Nuevo Remito
                    </button>
                </div>
            </div>

            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            {{-- Tabla de Remitos --}}
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0"> 
                            <thead class="table-light">
                                <tr>
                                    <th>N° Remito</th>
                                    <th>Fecha</th>
                                    <th>Cliente / Escuela</th>
                                    <th>Items</th>
                                    <th>Tipo</th>
                                    <th class="text-end">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($remitos as $remito)
                                    <tr>
                                        <td class="fw-bold">{{ $remito->number }}</td>
                                        <td>{{ \Carbon\Carbon::parse($remito->date)->format('d/m/Y') }}</td>
                                        <td>{{ $remito->client->name ?? 'N/A' }}</td>
                                        <td>{{ $remito->details->count() }}</td>
                                        <td>
                                            <span class="badge {{ $remito->tipo == 'remito' ? 'bg-primary' : 'bg-dark' }}">
                                                {{ strtoupper($remito->tipo) }}
                                            </span>
                                        </td>
                                        <td class="text-end">
                                            {{-- Botón VER DETALLE --}}
                                            <a href="{{ route('remitos.show', $remito->id) }}" class="btn btn-sm btn-outline-primary" title="Ver Detalle">
                                                <i class="bi bi-eye"></i>
                                            </a>

                                            {{-- BOTÓN IMPRIMIR PDF (Tamaño Carta) --}}
                                            <a href="{{ route('remitos.print', $remito->id) }}" target="_blank" class="btn btn-sm btn-outline-secondary" title="Imprimir PDF">
                                                <i class="bi bi-printer"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="6" class="text-center py-4 text-muted">No hay remitos generados aún.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- MODAL PARA GENERAR REMITO POR MENÚS --}}
<div class="modal fade" id="createRemitoModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title"><i class="bi bi-calculator me-2"></i>Generar Remito por Menú</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('remitos.storeOficial') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Fecha</label>
                            <input type="date" name="date" class="form-control" value="{{ date('Y-m-d') }}" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Cliente / Escuela</label>
                            <select name="client_id" class="form-select" required>
                                <option value="">Seleccione...</option>
                                @foreach($clients as $client)
                                    <option value="{{ $client->id }}">{{ $client->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <hr>
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h6 class="fw-bold text-success mb-0">Menús a incluir</h6>
                        <button type="button" class="btn btn-sm btn-outline-success" id="addMenuRow">
                            <i class="bi bi-plus-circle"></i> Agregar otro menú
                        </button>
                    </div>
                    
                    <table class="table table-sm table-bordered">
                        <tbody id="listaMenusRemito">
                            <tr>
                                <td>
                                    <select name="menu_ids[]" class="form-select" required>
                                        <option value="">Seleccionar menú...</option>
                                        @foreach($menus as $menu)
                                            <option value="{{ $menu->id }}">[{{ $menu->type }}] Día {{ $menu->day_number }} - {{ $menu->name }}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td width="50px" class="text-center">
                                    <button type="button" class="btn btn-sm btn-danger remove-menu-row"><i class="bi bi-trash"></i></button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success fw-bold">Generar Remito</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    // Agregar fila de menú
    document.getElementById('addMenuRow').addEventListener('click', function() {
        const tbody = document.getElementById('listaMenusRemito');
        const firstRow = tbody.querySelector('tr');
        const newRow = firstRow.cloneNode(true);
        newRow.querySelector('select').value = "";
        tbody.appendChild(newRow);
    });

    // Quitar fila de menú
    document.getElementById('listaMenusRemito').addEventListener('click', function(e) {
        if (e.target.closest('.remove-menu-row')) {
            const rows = document.querySelectorAll('#listaMenusRemito tr');
            if (rows.length > 1) {
                e.target.closest('tr').remove();
            }
        }
    });
</script>
@endsection