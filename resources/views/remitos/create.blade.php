@extends('layouts.app')

@section('content')
<div class="container py-4">
    <h2 class="fw-bold mb-4">Generar Remito de Menús</h2>

    <form action="{{ route('remitos.storeOficial') }}" method="POST" id="remitoForm">
        @csrf
        
        <div class="row">
            {{-- COLUMNA IZQUIERDA: CONFIGURACIÓN --}}
            <div class="col-md-5">
                <div class="card shadow-sm border-0 mb-3">
                    <div class="card-header bg-white fw-bold">1. Datos del Cliente</div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label">Escuela / Cliente</label>
                            <select name="client_id" class="form-select" required>
                                <option value="">Seleccione...</option>
                                @foreach($clients as $client)
                                    <option value="{{ $client->id }}">
                                        {{ $client->name }} ({{ ucfirst($client->level) }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Fecha del Remito</label>
                            <input type="date" name="date" class="form-control" value="{{ date('Y-m-d') }}" required>
                        </div>
                    </div>
                </div>

                <div class="card shadow-sm border-0 bg-primary text-white">
                    <div class="card-body">
                        <h5 class="fw-bold"><i class="bi bi-basket"></i> 2. Agregar Menús</h5>
                        <p class="small text-white-50">Selecciona los servicios que se entregarán en este remito.</p>
                        
                        <div class="input-group mb-3">
                            <select id="menuSelector" class="form-select text-dark">
                                <option value="">Elegir Menú...</option>
                                @foreach($menus as $menu)
                                    <option value="{{ $menu->id }}" data-name="{{ $menu->name }}" data-type="{{ $menu->type }}">
                                        {{ $menu->type }} - Día {{ $menu->day_number }} - {{ $menu->name }}
                                    </option>
                                @endforeach
                            </select>
                            <button type="button" class="btn btn-light fw-bold text-primary" onclick="agregarMenu()">
                                <i class="bi bi-plus-lg"></i> Agregar
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            {{-- COLUMNA DERECHA: LISTA DE MENÚS SELECCIONADOS --}}
            <div class="col-md-7">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-header bg-white fw-bold d-flex justify-content-between align-items-center">
                        <span>3. Menús a Procesar</span>
                        <span class="badge bg-secondary" id="countBadge">0</span>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-striped mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Servicio</th>
                                        <th>Menú</th>
                                        <th class="text-end">Acción</th>
                                    </tr>
                                </thead>
                                <tbody id="listaMenus">
                                    {{-- Aquí se agregan los menús con JS --}}
                                </tbody>
                            </table>
                        </div>
                        
                        <div id="emptyState" class="text-center py-5 text-muted">
                            <i class="bi bi-arrow-left-circle fs-1"></i>
                            <p class="mt-2">Agrega menús desde el panel izquierdo.</p>
                        </div>
                    </div>
                    <div class="card-footer bg-white py-3 text-end">
                        <button type="submit" class="btn btn-success btn-lg fw-bold px-4" id="btnGenerar" disabled>
                            <i class="bi bi-check-lg"></i> Generar Remito
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
    function agregarMenu() {
        const selector = document.getElementById('menuSelector');
        const id = selector.value;
        
        if(!id) return; // Si no eligió nada, salir

        const option = selector.options[selector.selectedIndex];
        const name = option.getAttribute('data-name');
        const type = option.getAttribute('data-type');

        // Validar que no esté ya agregado
        if(document.getElementById('menu-row-' + id)) {
            alert('Este menú ya está en la lista.');
            return;
        }

        const tbody = document.getElementById('listaMenus');
        const row = document.createElement('tr');
        row.id = 'menu-row-' + id;
        
        row.innerHTML = `
            <td><span class="badge bg-info text-dark">${type}</span></td>
            <td class="fw-bold">${name}</td>
            <td class="text-end">
                <input type="hidden" name="menu_ids[]" value="${id}">
                <button type="button" class="btn btn-sm btn-outline-danger border-0" onclick="eliminarFila('${id}')">
                    <i class="bi bi-trash"></i>
                </button>
            </td>
        `;

        tbody.appendChild(row);
        actualizarEstado();
        selector.value = ""; // Resetear selector
    }

    function eliminarFila(id) {
        document.getElementById('menu-row-' + id).remove();
        actualizarEstado();
    }

    function actualizarEstado() {
        const tbody = document.getElementById('listaMenus');
        const count = tbody.children.length;
        
        document.getElementById('countBadge').innerText = count;
        
        if(count > 0) {
            document.getElementById('emptyState').classList.add('d-none');
            document.getElementById('btnGenerar').disabled = false;
        } else {
            document.getElementById('emptyState').classList.remove('d-none');
            document.getElementById('btnGenerar').disabled = true;
        }
    }
</script>
@endsection