@extends('layouts.app')

@section('content')
<div class="container">
    
    <div class="row mb-4 align-items-center">
        {{-- Título --}}
        <div class="col-md-4">
            <h1 class="mb-0">Listado de Productos</h1>
        </div>

        {{-- Formulario de Búsqueda --}}
        <div class="col-md-4 mt-3">
            <form action="{{ route('products.index') }}" method="GET">
                <div class="input-group">
                    <input type="text" name="search" class="form-control" 
                           placeholder="Buscar por nombre, marca o código..." 
                           value="{{ request('search') }}">
                    <button class="btn btn-primary" type="submit">
                        <i class="bi bi-search"></i>
                    </button>
                    @if(request('search'))
                        <a href="{{ route('products.index') }}" class="btn btn-outline-danger" title="Limpiar búsqueda">
                            <i class="bi bi-x-lg"></i>
                        </a>
                    @endif
                </div>
            </form>
        </div>

        {{-- Botones de Acción --}}
        <div class="col-md-4 text-end mt-3">
            
            {{-- NUEVO BOTÓN: Registrar Entrega por Escuela (Descuenta Stock) --}}
            {{-- Este botón lleva al formulario con el parámetro ?tipo=entrega --}}
            <a href="{{ route('remitos.create', ['tipo' => 'entrega']) }}" class="btn btn-warning me-2 text-dark fw-bold">
                <i class="bi bi-truck"></i> Entrega Escuela
            </a>

            {{-- Botón Nuevo Producto --}}
            <a href="{{ route('products.create') }}" class="btn btn-success">
                <i class="bi bi-plus-lg"></i> Nuevo Producto
            </a>
        </div>
    </div>

    {{-- Mensajes de Sesión --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Tabla de Productos --}}
    <div class="card shadow-sm">
        <div class="card-body">
            <table class="table table-striped align-middle">
                <thead>
                    <tr>
                        <th>Producto</th>
                        <th>Presentación</th>
                        <th>Precios</th>
                        <th class="text-center">Stock</th>
                        <th class="text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($products as $product)
                        <tr>
                            <td>
                                <strong>{{ $product->name }}</strong><br>
                                <small class="text-muted">{{ $product->brand }}</small><br>
                                <small class="text-primary"><i class="bi bi-upc"></i> {{ $product->code }}</small>
                            </td>
                            <td>
                                {{ $product->presentation }} <br>
                                <span class="badge bg-secondary">Pack x{{ $product->units_per_package }}</span>
                            </td>
                            <td>
                                <small>Unit: ${{ number_format($product->price_per_unit, 2) }}</small><br>
                                <small>Bulto: ${{ number_format($product->price_per_package, 2) }}</small>
                            </td>
                            <td class="text-center">
                                <h3>
                                    <span class="badge {{ $product->stock < 10 ? 'bg-danger' : 'bg-success' }}">
                                        {{ $product->stock }}
                                    </span>
                                </h3>
                            </td>
                            <td class="text-end">
                                {{-- Botones de Entrada/Salida Rápida (Modal) --}}
                                <button type="button" class="btn btn-success btn-sm me-1"
                                        onclick="openModal({{ $product->id }}, '{{ addslashes($product->name) }}', 'entry')"
                                        title="Registrar Entrada">
                                    <i class="bi bi-plus-lg"></i>
                                </button>

                                <button type="button" class="btn btn-warning btn-sm me-1"
                                        onclick="openModal({{ $product->id }}, '{{ addslashes($product->name) }}', 'exit')"
                                        title="Registrar Salida">
                                    <i class="bi bi-dash-lg"></i>
                                </button>

                                {{-- Botones de Edición/Eliminación --}}
                                <a href="{{ route('products.edit', $product->id) }}" class="btn btn-primary btn-sm me-1" title="Editar Producto">
                                    <i class="bi bi-pencil"></i>
                                </a>

                                <form action="{{ route('products.destroy', $product->id) }}" method="POST" class="d-inline" onsubmit="return confirm('¿Estás seguro de borrar este producto?');">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-danger btn-sm" title="Eliminar"><i class="bi bi-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center py-4">No se encontraron productos.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- MODAL PARA MOVIMIENTOS DE STOCK RAPIDOS --}}
<div class="modal fade" id="movementModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTitle">Registrar Movimiento</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="movementForm" action="" method="POST">
                @csrf
                <div class="modal-body">
                    
                    <div class="alert alert-secondary text-center">
                        Producto: <strong id="productNameDisplay">---</strong>
                    </div>

                    <input type="hidden" name="type" id="movementType">
                    
                    <label class="form-label">Tipo de Unidad:</label>
                    <div class="btn-group w-100 mb-3" role="group">
                        <input type="radio" class="btn-check" name="unit_type" id="typeUnit" value="unit" checked>
                        <label class="btn btn-outline-primary" for="typeUnit">Unidades Sueltas</label>

                        <input type="radio" class="btn-check" name="unit_type" id="typePackage" value="package">
                        <label class="btn btn-outline-primary" for="typePackage">Cajas Cerradas</label>
                    </div>

                    <div class="mb-3" id="clientDiv">
                        <label class="form-label fw-bold">Cliente / Escuela:</label>
                        <select name="client_id" class="form-select" id="clientSelect">
                            <option value="">-- Seleccione una Escuela --</option>
                            {{-- IMPORTANTE: Para que esto no falle, el ProductController también debe enviar la variable $clients --}}
                            @if(isset($clients))
                                @foreach($clients as $client)
                                    <option value="{{ $client->id }}">{{ $client->name }} ({{ $client->cuit ?? '' }})</option>
                                @endforeach
                            @endif
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Cantidad</label>
                        <input type="number" name="quantity" class="form-control form-control-lg" required min="1" placeholder="Ej: 5">
                    </div>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar Movimiento</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function openModal(id, name, type) {
        const modalElement = document.getElementById('movementModal');
        const form = document.getElementById('movementForm');
        const title = document.getElementById('modalTitle');
        const nameDisplay = document.getElementById('productNameDisplay');
        const typeInput = document.getElementById('movementType');
        const clientDiv = document.getElementById('clientDiv');
        const clientSelect = document.getElementById('clientSelect');

        // Configurar ruta dinámica
        form.action = '{{ url("products") }}/' + id + '/movement'; 
        nameDisplay.textContent = name;
        typeInput.value = type;

        if (type === 'entry') {
            title.textContent = "🟢 Registrar ENTRADA";
            title.className = "modal-title text-success";
            clientDiv.style.display = 'none';
            clientSelect.required = false;
        } else {
            title.textContent = "🔴 Registrar SALIDA";
            title.className = "modal-title text-danger";
            clientDiv.style.display = 'block';
            clientSelect.required = true;
        }

        const modal = new bootstrap.Modal(modalElement);
        modal.show();
    }
</script>
@endsection