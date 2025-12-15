@extends('layouts.app')

@section('content')
<div class="container">
    
    {{-- LOGICA VISUAL: Diferenciar Entrega (Real) de Remito (Papel) --}}
    @if(isset($tipo) && $tipo == 'entrega')
        <div class="alert alert-warning border-warning d-flex align-items-center mb-4" role="alert">
            <i class="bi bi-exclamation-triangle-fill fs-4 me-3"></i>
            <div>
                <h2 class="alert-heading h4 mb-0">Nueva Entrega por Escuela</h2>
                <p class="mb-0">Atención: Los productos ingresados aquí <strong>SE DESCONTARÁN DEL STOCK</strong> automáticamente.</p>
            </div>
        </div>
    @else
        <div class="d-flex align-items-center mb-4">
            <h2 class="mb-0">Generar Nuevo Remito (Administrativo)</h2>
            <span class="badge bg-secondary ms-3">No descuenta stock</span>
        </div>
    @endif

    {{-- BLOQUE CRÍTICO: Define la ruta a usar dinámicamente --}}
    @php
        // Si $tipo es 'entrega', usa la ruta remitos.store (Descuenta Stock).
        // Si no, usa remitos.store_oficial (NO Descuenta Stock).
        $storeRoute = (($tipo ?? 'remito') === 'entrega') ? 'remitos.store' : 'remitos.store_oficial';
    @endphp

    {{-- La acción del formulario ahora es dinámica --}}
    <form action="{{ route($storeRoute) }}" method="POST">
        @csrf

        {{-- CAMPO OCULTO CLAVE: Solo se envía si es la ruta de ENTREGA --}}
        @if(isset($tipo) && $tipo == 'entrega')
            <input type="hidden" name="tipo_operacion" value="entrega">
        @endif
        
        <div class="card mb-4 shadow-sm">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Cliente / Escuela</label>
                        <select name="client_id" class="form-select" required>
                            <option value="">Seleccione...</option>
                            @foreach($clients as $client)
                                <option value="{{ $client->id }}">{{ $client->name }} - {{ $client->address }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label fw-bold">Fecha</label>
                        <input type="date" name="date" class="form-control" value="{{ date('Y-m-d') }}" required>
                    </div>
                    <div class="col-md-3 mb-3">
                         <label class="form-label">Observación</label>
                         <input type="text" name="observation" class="form-control" placeholder="Opcional">
                    </div>
                </div>
            </div>
        </div>

        <div class="card shadow-sm mb-4">
            <div class="card-header bg-light">
                <h5 class="mb-0">Productos a entregar</h5>
                
                {{-- NUEVOS CAMPOS DE ESCÁNER Y BOTONES --}}
                <div class="d-flex align-items-end justify-content-between mt-3">
                    @if(isset($tipo) && $tipo == 'entrega')
                        <div class="input-group me-3 flex-grow-1">
                            <span class="input-group-text bg-info text-white"><i class="bi bi-qr-code-scan"></i> Escáner</span>
                            {{-- Este campo captura el código del escáner y activa el JS --}}
                            <input type="text" id="scannerInput" class="form-control form-control-lg" placeholder="Escanee el código de barras aquí..." autofocus>
                        </div>
                    @endif
                    
                    {{-- Botón para agregar manualmente --}}
                    <button type="button" class="btn btn-success" onclick="addProductRow()">
                        <i class="bi bi-plus-lg"></i> Agregar Manualmente
                    </button>
                </div>
            </div>
            
            <div class="card-body p-0">
                <table class="table table-bordered mb-0" id="productsTable">
                    <thead class="table-light">
                        <tr>
                            <th width="70%">Producto</th>
                            <th width="20%">Cantidad</th>
                            <th width="10%"></th>
                        </tr>
                    </thead>
                    <tbody id="productsBody">
                        {{-- Aquí se insertan las filas con JS --}}
                    </tbody>
                </table>
            </div>
        </div>

        <div class="text-end">
            <a href="{{ route('remitos.index') }}" class="btn btn-secondary">Cancelar</a>
            
            {{-- El botón cambia de color y texto según el tipo --}}
            @if(isset($tipo) && $tipo == 'entrega')
                <button type="submit" class="btn btn-warning btn-lg fw-bold">
                    <i class="bi bi-box-seam"></i> Confirmar Entrega (Descontar)
                </button>
            @else
                <button type="submit" class="btn btn-primary btn-lg">
                    <i class="bi bi-file-earmark-text"></i> Generar Remito
                </button>
            @endif
        </div>
    </form>
</div>

<script>
    // SOLUCIÓN DEFINITIVA DEL PARSE ERROR Y SINTAXIS:
    // 1. Usamos PHP puro para generar la cadena JSON segura.
    @php
        $productsJson = $products->keyBy('code')->map(function ($product) {
            return [
                'id' => $product->id,
                'name' => $product->name,
                'stock' => $product->stock,
                'code' => $product->code,
            ];
        })->toJson(JSON_FORCE_OBJECT);
    @endphp
    
    // 2. Imprimimos la cadena JSON segura y cruda dentro de una constante JavaScript.
    const productsData = JSON.parse({!! $productsJson !!});
    
    // Función principal para agregar una fila
    function addProductRow(productId = null, quantity = 1) {
        const tableBody = document.getElementById('productsBody');
        
        // Creamos la fila HTML
        const row = document.createElement('tr');
        row.innerHTML = `
            <td>
                <select name="products[]" class="form-select product-select" required>
                    <option value="">Seleccionar producto...</option>
                    @foreach($products as $product)
                        <option value="{{ $product->id }}" data-code="{{ $product->code }}">
                            {{ $product->name }} (Stock: {{ $product->stock }})
                        </option>
                    @endforeach
                </select>
            </td>
            <td>
                <input type="number" name="quantities[]" class="form-control quantity-input" placeholder="Cant." min="1" value="${quantity}" required>
            </td>
            <td class="text-center">
                <button type="button" class="btn btn-danger btn-sm" onclick="removeRow(this)">
                    <i class="bi bi-trash"></i>
                </button>
            </td>
        `;
        
        // Si se pasó un ID, seleccionamos el producto
        if (productId) {
            const select = row.querySelector('.product-select');
            select.value = productId;
            select.disabled = false;
            
            if (quantity === 1) {
                 setTimeout(() => row.querySelector('.quantity-input').focus(), 50);
            }
        }
        
        tableBody.appendChild(row);

        if (!productId) {
            setTimeout(() => row.querySelector('.product-select').focus(), 50);
        }
    }

    function removeRow(button) {
        button.closest('tr').remove();
    }
    
    // FUNCIONALIDAD DE ESCÁNER
    document.addEventListener('DOMContentLoaded', function() {
        const scannerInput = document.getElementById('scannerInput');
        const productsBody = document.getElementById('productsBody');

        // Solo inicializamos si estamos en la vista de entrega (con escáner)
        if (scannerInput) {
            // Eliminamos la fila vacía inicial
            if (productsBody.rows.length === 1) {
                 productsBody.rows[0].remove();
            }

            scannerInput.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault(); 
                    handleScan(scannerInput.value);
                    scannerInput.value = ''; // Limpiar el campo
                }
            });
            
        } else if (productsBody.rows.length === 0) {
            // Agregar una fila si no hay escáner (Remito Admin)
            addProductRow();
        }

        // Función para manejar el código de barras escaneado
        function handleScan(code) {
            code = String(code).trim();
            if (!code) return;

            const product = productsData[code]; 

            if (product) {
                let foundExisting = false;
                
                // 1. INTENTAR INCREMENTAR CANTIDAD
                Array.from(productsBody.rows).forEach(row => {
                    const select = row.querySelector('.product-select');
                    const input = row.querySelector('.quantity-input');
                    
                    if (select && input && parseInt(select.value) === product.id) {
                        input.value = parseInt(input.value) + 1;
                        foundExisting = true;
                    }
                });

                // 2. SI NO EXISTE, AGREGAR UNA NUEVA FILA
                if (!foundExisting) {
                    addProductRow(product.id, 1);
                }
            } else {
                alert(`Producto con código "${code}" no encontrado en el inventario.`);
            }
        }
    });
</script>
@endsection