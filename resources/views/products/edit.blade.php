@extends('layouts.app')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8"> <div class="card shadow-sm">
            <div class="card-header bg-white">
                <h4 class="mb-0">Editar Producto</h4>
            </div>
            <div class="card-body">
                <form action="{{ route('products.update', $product->id) }}" method="POST">
                    @csrf
                    @method('PUT') <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Código (SKU)</label>
                                <input type="text" name="code" class="form-control" value="{{ $product->code }}" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Marca</label>
                                <input type="text" name="brand" class="form-control" value="{{ $product->brand }}">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Nombre del Producto</label>
                                <input type="text" name="name" class="form-control" value="{{ $product->name }}" required>
                            </div>
                             <div class="mb-3">
                                <label class="form-label">Descripción</label>
                                <textarea name="description" class="form-control" rows="2">{{ $product->description }}</textarea>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="row">
                                <div class="col-6 mb-3">
                                    <label class="form-label">Presentación</label>
                                    <input type="text" name="presentation" class="form-control" value="{{ $product->presentation }}">
                                </div>
                                <div class="col-6 mb-3">
                                    <label class="form-label">Unidades por Bulto</label>
                                    <input type="number" name="units_per_package" class="form-control" value="{{ $product->units_per_package }}" required>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-6 mb-3">
                                    <label class="form-label">Precio Unitario ($)</label>
                                    <input type="number" step="0.01" name="price_per_unit" class="form-control" value="{{ $product->price_per_unit }}" required>
                                </div>
                                <div class="col-6 mb-3">
                                    <label class="form-label">Precio Bulto ($)</label>
                                    <input type="number" step="0.01" name="price_per_package" class="form-control" value="{{ $product->price_per_package }}" required>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label bg-warning bg-opacity-10 p-1">Stock Actual</label>
                                <input type="number" name="stock" class="form-control" value="{{ $product->stock }}" required>
                            </div>
                        </div>
                    </div>

                    <hr>
                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{ route('products.index') }}" class="btn btn-secondary">Cancelar</a>
                        <button type="submit" class="btn btn-warning">Actualizar Cambios</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection