@extends('layouts.app')

@section('content')
{{-- CAMBIO 1: Usamos 'container' en lugar de 'container-fluid' para que no ocupe todo el ancho --}}
<div class="container py-4"> 
    
    {{-- BARRA DE ESCANEO RÁPIDO --}}
    <div class="card mb-4 border-0 shadow-sm" style="background: linear-gradient(to right, #1e293b, #334155);">
        <div class="card-body p-4">
            <form action="{{ route('products.quickScan') }}" method="POST" class="row g-3 align-items-end">
                @csrf
                
                {{-- Selector de Modo --}}
                <div class="col-md-3">
                    <label class="text-white small fw-bold mb-1">MODO</label>
                    <div class="btn-group w-100" role="group">
                        <input type="radio" class="btn-check" name="scan_mode" id="modeEntry" value="entry" checked onchange="toggleModeColor('entry')">
                        <label class="btn btn-outline-success fw-bold" for="modeEntry">📥 ENTRADA</label>

                        <input type="radio" class="btn-check" name="scan_mode" id="modeExit" value="exit" onchange="toggleModeColor('exit')">
                        <label class="btn btn-outline-danger fw-bold" for="modeExit">🗑️ ROTURA</label>
                    </div>
                </div>

                {{-- Input del Código --}}
                <div class="col-md-5">
                    <label class="text-white small fw-bold mb-1">CÓDIGO DE BARRAS</label>
                    <div class="input-group">
                        <span class="input-group-text bg-white border-0"><i class="bi bi-upc-scan"></i></span>
                        <input type="text" name="scan_code" id="scan_code" class="form-control form-control-lg border-0" 
                               placeholder="Escanee aquí..." autofocus autocomplete="off">
                    </div>
                </div>

                {{-- Cantidad --}}
                <div class="col-md-2">
                    <label class="text-white small fw-bold mb-1">CANTIDAD</label>
                    <input type="number" name="scan_quantity" class="form-control form-control-lg border-0 text-center" 
                           value="1" min="1" required>
                </div>

                {{-- Botón Procesar --}}
                <div class="col-md-2">
                    <button type="submit" class="btn btn-light btn-lg w-100 fw-bold text-dark" id="btnAction">
                        <i class="bi bi-check-lg"></i> OK
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- BARRA DE BÚSQUEDA Y BOTONES --}}
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h3 class="mb-0 fw-bold text-gray-800">Inventario</h3>
        </div>
        
        <div class="d-flex gap-2">
            <form action="{{ route('products.index') }}" method="GET" class="d-flex">
                <div class="input-group">
                    <input type="text" name="search" class="form-control" placeholder="Buscar..." value="{{ request('search') }}">
                    <button class="btn btn-outline-secondary" type="submit"><i class="bi bi-search"></i></button>
                </div>
            </form>
            
            <a href="{{ route('deposito.create') }}" class="btn btn-primary text-nowrap">
                <i class="bi bi-truck"></i> Entrega
            </a>
            <a href="{{ route('products.create') }}" class="btn btn-success text-nowrap">
                <i class="bi bi-plus-lg"></i> Nuevo
            </a>
        </div>
    </div>

    {{-- Mensajes de Feedback --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show shadow-sm fw-bold">
            <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show shadow-sm fw-bold">
            <i class="bi bi-exclamation-triangle-fill me-2"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- TABLA DE PRODUCTOS --}}
    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4">Producto</th>
                            <th>Presentación</th>
                            <th>Precios</th>
                            <th class="text-center">Stock</th>
                            <th class="text-end pe-4">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($products as $product)
                            <tr>
                                <td class="ps-4">
                                    <div class="fw-bold text-dark">{{ $product->name }}</div>
                                    <div class="small text-muted">{{ $product->brand }}</div>
                                    <div class="small text-primary font-monospace"><i class="bi bi-upc"></i> {{ $product->code }}</div>
                                </td>
                                <td>
                                    {{ $product->presentation }} <br>
                                    <span class="badge bg-secondary">Caja x{{ $product->units_per_package }}</span>
                                </td>
                                <td>
                                    <div class="small">Unit: ${{ number_format($product->price_per_unit, 0, ',', '.') }}</div>
                                    <div class="small text-muted">Bulto: ${{ number_format($product->price_per_package, 0, ',', '.') }}</div>
                                </td>
                                <td class="text-center">
                                    <span class="badge {{ $product->stock < 10 ? 'bg-danger' : 'bg-success' }} fs-6">
                                        {{ $product->stock }}
                                    </span>
                                </td>
                                <td class="text-end pe-4">
                                    <a href="{{ route('products.edit', $product->id) }}" class="btn btn-sm btn-outline-primary" title="Editar">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    
                                    <form action="{{ route('products.destroy', $product->id) }}" method="POST" class="d-inline" onsubmit="return confirm('¿Borrar?');">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger" title="Eliminar">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="text-center py-5 text-muted">No hay productos registrados.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if(method_exists($products, 'links'))
            <div class="p-3 bg-light border-top">
                {{ $products->links() }}
            </div>
        @endif
    </div>
</div>

{{-- SCRIPT PARA LA BARRA DE ESCANEO --}}
<script>
    // Foco automático en el escáner al cargar la página
    document.addEventListener("DOMContentLoaded", function() {
        const input = document.getElementById('scan_code');
        if(input) input.focus();
    });

    // Cambiar color del botón según el modo
    function toggleModeColor(mode) {
        const btn = document.getElementById('btnAction');
        const input = document.getElementById('scan_code');
        
        if (mode === 'entry') {
            btn.classList.remove('btn-danger');
            btn.classList.add('btn-light');
            btn.innerHTML = '<i class="bi bi-check-lg"></i> ENTRADA';
        } else {
            btn.classList.remove('btn-light');
            btn.classList.add('btn-danger');
            btn.innerHTML = '<i class="bi bi-trash"></i> ROTURA';
        }
        // Devolver el foco al input
        input.focus();
    }
</script>
@endsection