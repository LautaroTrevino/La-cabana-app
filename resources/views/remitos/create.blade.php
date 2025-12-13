@extends('layouts.app')

@section('content')
<div class="container">
    <h2 class="mb-4">Generar Nuevo Remito</h2>

    <form action="{{ route('remitos.store') }}" method="POST">
        @csrf
        
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
            <div class="card-header bg-light d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Productos a entregar</h5>
                <button type="button" class="btn btn-sm btn-success" onclick="addProductRow()">
                    <i class="bi bi-plus-lg"></i> Agregar Producto
                </button>
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
                        </tbody>
                </table>
            </div>
        </div>

        <div class="text-end">
            <a href="{{ route('remitos.index') }}" class="btn btn-secondary">Cancelar</a>
            <button type="submit" class="btn btn-primary btn-lg">Generar Remito</button>
        </div>
    </form>
</div>

<script>
    function addProductRow() {
        const tableBody = document.getElementById('productsBody');
        const rowCount = tableBody.rows.length;
        
        const row = `
            <tr>
                <td>
                    <select name="products[]" class="form-select" required>
                        <option value="">Seleccionar producto...</option>
                        @foreach($products as $product)
                            <option value="{{ $product->id }}">
                                {{ $product->name }} (Stock: {{ $product->stock }})
                            </option>
                        @endforeach
                    </select>
                </td>
                <td>
                    <input type="number" name="quantities[]" class="form-control" placeholder="Cant." min="1" required>
                </td>
                <td class="text-center">
                    <button type="button" class="btn btn-danger btn-sm" onclick="removeRow(this)">
                        <i class="bi bi-trash"></i>
                    </button>
                </td>
            </tr>
        `;
        
        // Insertar HTML al final de la tabla
        tableBody.insertAdjacentHTML('beforeend', row);
    }

    function removeRow(button) {
        button.closest('tr').remove();
    }

    // Agregar una fila automáticamente al cargar
    document.addEventListener('DOMContentLoaded', function() {
        addProductRow();
    });
</script>
@endsection