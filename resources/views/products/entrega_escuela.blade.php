@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="row mb-4">
        <div class="col-md-8">
            <h2 class="fw-bold">Entrega Física a Escuela</h2>
            <p class="text-muted">Descuento de stock mediante escáner o selección manual.</p>
        </div>
        <div class="col-md-4 text-end">
            <a href="{{ route('products.index') }}" class="btn btn-secondary">Volver</a>
        </div>
    </div>

    <form action="{{ route('entregas.escuela.store') }}" method="POST" id="formEntrega">
        @csrf
        <div class="row">
            {{-- PANEL LATERAL: CLIENTE Y BÚSQUEDA --}}
            <div class="col-md-4">
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-body">
                        <div class="mb-4">
                            <label class="form-label fw-bold">1. Escuela / Cliente</label>
                            <select name="client_id" class="form-select shadow-sm" required>
                                <option value="">Seleccione destino...</option>
                                @foreach($clients as $client)
                                    <option value="{{ $client->id }}">{{ $client->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="p-3 bg-light rounded border mb-3">
                            <label class="form-label fw-bold text-success">
                                <i class="bi bi-search"></i> 2. Selección Manual
                            </label>
                            <select id="manual_select" class="form-select mb-2">
                                <option value="">Buscar producto...</option>
                                @foreach($products as $p)
                                    <option value="{{ $p->id }}">{{ $p->name }} (Stock: {{ $p->stock }})</option>
                                @endforeach
                            </select>
                            <button type="button" class="btn btn-success btn-sm w-100" onclick="agregarDesdeSelect()">
                                Agregar a la lista
                            </button>
                        </div>

                        <div class="p-3 bg-light rounded border">
                            <label class="form-label fw-bold text-primary">
                                <i class="bi bi-upc-scan"></i> 3. Escaneo de Barras
                            </label>
                            <input type="text" id="barcode_scanner" class="form-control" placeholder="Escanear...">
                            <div id="feedback_escaneo" class="mt-2 small"></div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- PANEL PRINCIPAL: LISTA DE CARGA --}}
            <div class="col-md-8">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-dark text-white py-3">
                        <h5 class="mb-0 fw-bold">Artículos a descargar del Depósito</h5>
                    </div>
                    <div class="card-body p-0">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-4">Producto</th>
                                    <th width="150" class="text-center">Cantidad</th>
                                    <th width="50"></th>
                                </tr>
                            </thead>
                            <tbody id="lista_entrega"></tbody>
                        </table>
                        <div id="vacio_msg" class="text-center py-5 text-muted">
                            <p>No hay artículos cargados para esta entrega.</p>
                        </div>
                    </div>
                    <div class="card-footer bg-white py-3 text-end">
                        <button type="submit" class="btn btn-success btn-lg px-5 fw-bold shadow-sm" id="btnFinalizar" disabled>
                            Finalizar y Descontar Stock
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
    const productosBase = @json($products);
    let itemIndex = 0;

    // LÓGICA DEL ESCÁNER
    document.getElementById('barcode_scanner').addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            const barcode = this.value.trim();
            const producto = productosBase.find(p => p.code == barcode || p.package_code == barcode);
            if (producto) {
                agregarOIncrementar(producto);
                document.getElementById('feedback_escaneo').innerHTML = `<span class="text-success fw-bold">✔ ${producto.name}</span>`;
            } else {
                document.getElementById('feedback_escaneo').innerHTML = `<span class="text-danger fw-bold">❌ No encontrado</span>`;
            }
            this.value = '';
        }
    });

    // LÓGICA DE SELECCIÓN MANUAL
    function agregarDesdeSelect() {
        const select = document.getElementById('manual_select');
        const id = select.value;
        if (!id) return;
        
        const producto = productosBase.find(p => p.id == id);
        if (producto) {
            agregarOIncrementar(producto);
            select.value = ""; // Limpiar selector
        }
    }

    function agregarOIncrementar(p) {
        let filaExistente = document.querySelector(`input[value="${p.id}"][name*="product_id"]`);

        if (filaExistente) {
            let inputCantidad = filaExistente.closest('tr').querySelector('input[name*="quantity"]');
            inputCantidad.value = parseFloat(inputCantidad.value) + 1;
        } else {
            const row = document.createElement('tr');
            row.innerHTML = `
                <td class="ps-4">
                    <strong>${p.name}</strong><br>
                    <small class="text-muted">Stock actual: ${p.stock}</small>
                    <input type="hidden" name="items[${itemIndex}][product_id]" value="${p.id}">
                </td>
                <td>
                    <input type="number" name="items[${itemIndex}][quantity]" class="form-control text-center" step="0.1" value="1" min="0.1" required>
                </td>
                <td class="text-center">
                    <button type="button" class="btn btn-outline-danger btn-sm border-0" onclick="this.closest('tr').remove(); actualizarEstado();">
                        <i class="bi bi-trash"></i>
                    </button>
                </td>
            `;
            document.getElementById('lista_entrega').appendChild(row);
            itemIndex++;
        }
        actualizarEstado();
    }

    function actualizarEstado() {
        const tabla = document.getElementById('lista_entrega');
        const btn = document.getElementById('btnFinalizar');
        const msg = document.getElementById('vacio_msg');
        
        if (tabla.children.length > 0) {
            msg.classList.add('d-none');
            btn.disabled = false;
        } else {
            msg.classList.remove('d-none');
            btn.disabled = true;
        }
    }
</script>
@endsection