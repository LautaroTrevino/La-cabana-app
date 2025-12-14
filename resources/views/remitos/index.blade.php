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
                <div class="alert alert-success alert-dismissible fade show" role="alert">
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
                                    <th>Cant. Items</th>
                                    <th>Estado</th>
                                    <th class="text-end">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($remitos as $remito)
                                    <tr>
                                        <td class="fw-bold">{{ $remito->numero_remito }}</td>
                                        <td>{{ \Carbon\Carbon::parse($remito->fecha)->format('d/m/Y') }}</td>
                                        <td>{{ $remito->cliente }}</td>
                                        <td>{{ $remito->details->count() }} Líneas</td>
                                        <td>
                                            @if($remito->estado == 'pendiente')
                                                <span class="badge bg-warning text-dark">PENDIENTE</span>
                                            @else
                                                <span class="badge bg-success">ENTREGADO</span>
                                            @endif
                                        </td>
                                        <td class="text-end">
                                            {{-- CORRECCIÓN AQUÍ: Usamos etiquetas <a> con las rutas --}}
                                            
                                            {{-- Botón VER --}}
                                            <a href="{{ route('remitos.show', $remito->id) }}" class="btn btn-sm btn-outline-primary" title="Ver Detalle">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            
                                            {{-- Botón IMPRIMIR (Abre pestaña nueva) --}}
                                            <a href="{{ route('remitos.print', $remito->id) }}" target="_blank" class="btn btn-sm btn-outline-secondary" title="Imprimir">
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

{{-- MODAL PARA CREAR REMITO --}}
<div class="modal fade" id="createRemitoModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title">Generar Nuevo Remito</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('remitos.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Fecha</label>
                            <input type="date" name="fecha" class="form-control" value="{{ date('Y-m-d') }}" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Cliente / Escuela</label>
                            <select name="cliente" class="form-select" required>
                                <option value="">Seleccione...</option>
                                @foreach($clients as $client)
                                    <option value="{{ $client->name }}">{{ $client->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <hr>
                    <h6 class="fw-bold text-success">Productos a Entregar</h6>
                    
                    <table class="table table-sm table-bordered" id="tablaProductos">
                        <thead class="table-light">
                            <tr>
                                <th>Producto</th>
                                <th width="150">Cantidad</th>
                                <th width="50"></th>
                            </tr>
                        </thead>
                        <tbody id="listaProductos">
                            <tr>
                                <td>
                                    <select name="productos[]" class="form-select form-select-sm" required>
                                        <option value="">Buscar producto...</option>
                                        @foreach($products as $product)
                                            <option value="{{ $product->id }}">{{ $product->name }}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td>
                                    <input type="number" name="cantidades[]" class="form-control form-control-sm" placeholder="0" step="0.01" required>
                                </td>
                                <td class="text-center">
                                    <button type="button" class="btn btn-sm btn-danger remove-row"><i class="bi bi-x"></i></button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    
                    <button type="button" class="btn btn-sm btn-outline-success" id="btnAgregarProducto">
                        <i class="bi bi-plus-circle"></i> Agregar otro producto
                    </button>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success">Generar Remito</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.getElementById('btnAgregarProducto').addEventListener('click', function() {
        var row = document.querySelector('#listaProductos tr').cloneNode(true);
        row.querySelector('select').value = '';
        row.querySelector('input').value = '';
        document.getElementById('listaProductos').appendChild(row);
    });

    document.getElementById('listaProductos').addEventListener('click', function(e) {
        if (e.target.closest('.remove-row')) {
            if (document.querySelectorAll('#listaProductos tr').length > 1) {
                e.target.closest('tr').remove();
            } else {
                alert("Debes mantener al menos un producto.");
            }
        }
    });
</script>
@endsection