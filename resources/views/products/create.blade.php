@extends('layouts.app')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card shadow-sm">
            <div class="card-header bg-white">
                <h4 class="mb-0">Nuevo Producto</h4>
            </div>
            <div class="card-body">
                <form action="{{ route('products.store') }}" method="POST">
                    @csrf

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Código (Unidad Individual)</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-upc"></i></span>
                                    <input type="text" name="code" class="form-control" required placeholder="Escanear unidad">
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Marca</label>
                                <input type="text" name="brand" class="form-control">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Nombre del Producto</label>
                                <input type="text" name="name" class="form-control" required>
                            </div>
                             <div class="mb-3">
                                <label class="form-label">Descripción</label>
                                <textarea name="description" class="form-control" rows="2"></textarea>
                            </div>
                        </div>

                        <div class="col-md-6">
                            
                            <div class="mb-3 bg-light p-2 rounded border">
                                <label class="form-label text-primary fw-bold">
                                    <i class="bi bi-box-seam"></i> Código de Barras del Bulto/Caja
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-upc-scan"></i></span>
                                    <input type="text" name="package_code" class="form-control" placeholder="Escanear código de la caja">
                                </div>
                                <div class="form-text small">Escanear solo si la caja tiene un código distinto al de la unidad.</div>
                            </div>
                            <div class="row">
                                <div class="col-6 mb-3">
                                    <label class="form-label">Presentación</label>
                                    <input type="text" name="presentation" class="form-control" placeholder="Ej: Caja, Pack">
                                </div>
                                <div class="col-6 mb-3">
                                    <label class="form-label">Unidades por Bulto</label>
                                    <input type="number" name="units_per_package" class="form-control" value="1" required>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-6 mb-3">
                                    <label class="form-label">Precio Unitario ($)</label>
                                    <input type="number" step="0.01" name="price_per_unit" class="form-control" required>
                                </div>
                                <div class="col-6 mb-3">
                                    <label class="form-label">Precio Bulto ($)</label>
                                    <input type="number" step="0.01" name="price_per_package" class="form-control" required>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label bg-warning bg-opacity-10 p-1">Stock Inicial (Unidades)</label>
                                <input type="number" name="stock" class="form-control" value="0" required>
                            </div>
                        </div>
                    </div>

                    <hr>
                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{ route('products.index') }}" class="btn btn-secondary">Cancelar</a>
                        <button type="submit" class="btn btn-primary">Guardar Producto</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection