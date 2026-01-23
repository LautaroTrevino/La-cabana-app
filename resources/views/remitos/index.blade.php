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

            {{-- SECCIÓN DE FILTROS --}}
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white fw-bold">
                    <i class="bi bi-funnel"></i> Filtros de Búsqueda
                </div>
                <div class="card-body">
                    <form action="{{ route('remitos.index') }}" method="GET">
                        <div class="row g-3 align-items-end">
                            {{-- Filtro Cliente --}}
                            <div class="col-md-3">
                                <label class="form-label text-sm">Cliente</label>
                                <select name="client_id" class="form-select form-select-sm">
                                    <option value="">-- Todos --</option>
                                    @foreach($clients as $c)
                                        <option value="{{ $c->id }}" {{ request('client_id') == $c->id ? 'selected' : '' }}>
                                            {{ $c->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Filtro Fecha Exacta --}}
                            <div class="col-md-2">
                                <label class="form-label text-sm">Día Exacto</label>
                                <input type="date" name="date_search" class="form-control form-control-sm" value="{{ request('date_search') }}">
                            </div>

                            {{-- Filtro Rango --}}
                            <div class="col-md-2">
                                <label class="form-label text-sm">Desde</label>
                                <input type="date" name="date_start" class="form-control form-control-sm" value="{{ request('date_start') }}">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label text-sm">Hasta</label>
                                <input type="date" name="date_end" class="form-control form-control-sm" value="{{ request('date_end') }}">
                            </div>

                            {{-- Botones --}}
                            <div class="col-md-3 d-flex gap-2">
                                <button type="submit" class="btn btn-sm btn-primary w-100">
                                    <i class="bi bi-search"></i> Buscar
                                </button>
                                <a href="{{ route('remitos.index') }}" class="btn btn-sm btn-outline-secondary" title="Limpiar filtros">
                                    <i class="bi bi-x-lg"></i>
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Mensaje de Éxito --}}
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            {{-- TABLA 1: ORDENES DE ENTREGA (DEPÓSITO) --}}
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h4 class="text-gray-800 fw-bold"><i class="bi bi-box-seam text-dark"></i> Órdenes de Entrega (Stock)</h4>
                
                {{-- CORRECCIÓN AQUÍ: Usamos ordenes.create --}}
                <a href="{{ route('ordenes.create') }}" class="btn btn-dark btn-sm">
                    <i class="bi bi-plus-lg"></i> Nueva Entrega
                </a>
            </div>

            <div class="card shadow-sm border-0 mb-5">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0"> 
                            <thead class="table-dark text-white">
                                <tr>
                                    <th>N° Entrega</th>
                                    <th>Fecha</th>
                                    <th>Destino</th>
                                    <th class="text-center">Items</th>
                                    <th class="text-end">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($entregas as $entrega)
                                    <tr>
                                        <td class="fw-bold">{{ $entrega->number }}</td>
                                        <td>{{ \Carbon\Carbon::parse($entrega->date)->format('d/m/Y') }}</td>
                                        <td>{{ $entrega->client->name ?? 'N/A' }}</td>
                                        <td class="text-center">{{ $entrega->details->count() }}</td>
                                        <td class="text-end">
                                            <a href="{{ route('remitos.show', $entrega->id) }}" class="btn btn-sm btn-outline-dark" title="Ver">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            <a href="{{ route('remitos.print', $entrega->id) }}" target="_blank" class="btn btn-sm btn-outline-secondary" title="Imprimir">
                                                <i class="bi bi-printer"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="5" class="text-center py-3 text-muted">No hay entregas registradas con estos filtros.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <hr class="my-5 border-2">

            {{-- TABLA 2: REMITOS ADMINISTRATIVOS (MENÚS) --}}
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h4 class="text-gray-800 fw-bold"><i class="bi bi-file-earmark-text text-primary"></i> Remitos Administrativos (Menús)</h4>
                <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#createRemitoModal">
                    <i class="bi bi-calculator"></i> Generar desde Menú
                </button>
            </div>

            <div class="card shadow-sm border-0 mb-4">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0"> 
                            <thead class="bg-primary text-white">
                                <tr>
                                    <th>N° Remito</th>
                                    <th>Fecha</th>
                                    <th>Escuela</th>
                                    <th class="text-center">Ingredientes</th>
                                    <th class="text-end">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($remitosAdmin as $remito)
                                    <tr>
                                        <td class="fw-bold text-primary">{{ $remito->number }}</td>
                                        <td>{{ \Carbon\Carbon::parse($remito->date)->format('d/m/Y') }}</td>
                                        <td>{{ $remito->client->name ?? 'N/A' }}</td>
                                        <td class="text-center">{{ $remito->details->count() }}</td>
                                        <td class="text-end">
                                            <a href="{{ route('remitos.show', $remito->id) }}" class="btn btn-sm btn-outline-primary" title="Ver">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            <a href="{{ route('remitos.print', $remito->id) }}" target="_blank" class="btn btn-sm btn-outline-secondary" title="Imprimir">
                                                <i class="bi bi-printer"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="5" class="text-center py-3 text-muted">No hay remitos administrativos registrados con estos filtros.</td></tr>
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
            
            <form action="{{ route('remitos.storeMenu') }}" method="POST">
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
                                    <select name="menus[]" class="form-select" required>
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
    // Script del Modal
    document.getElementById('addMenuRow').addEventListener('click', function() {
        const tbody = document.getElementById('listaMenusRemito');
        const firstRow = tbody.querySelector('tr');
        const newRow = firstRow.cloneNode(true);
        newRow.querySelector('select').value = "";
        tbody.appendChild(newRow);
    });

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