@extends('layouts.app')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white py-3">
                <h4 class="mb-0 text-primary"><i class="bi bi-box-seam"></i> Registrar Nuevo Producto</h4>
            </div>
            <div class="card-body p-4">
                <form action="{{ route('products.store') }}" method="POST">
                    @csrf

                    <div class="row">
                        {{-- Columna Izquierda: Identificación --}}
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Código (Unidad Individual)</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-upc"></i></span>
                                    <input type="text" name="code" class="form-control @error('code') is-invalid @enderror" 
                                           value="{{ old('code') }}" required placeholder="Escanear unidad" autofocus>
                                </div>
                                @error('code') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold">Nombre del Producto</label>
                                <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" 
                                       value="{{ old('name') }}" required placeholder="Ej: Aceite de Girasol">
                                @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold">Marca</label>
                                <input type="text" name="brand" class="form-control @error('brand') is-invalid @enderror" 
                                       value="{{ old('brand') }}" placeholder="Ej: Natura">
                                @error('brand') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                             <div class="mb-3">
                                <label class="form-label fw-bold">Descripción / Notas</label>
                                <textarea name="description" class="form-control" rows="2">{{ old('description') }}</textarea>
                            </div>
                        </div>

                        {{-- Columna Derecha: Logística y Precios --}}
                        <div class="col-md-6">
                            <div class="mb-3 bg-light p-3 rounded border">
                                <label class="form-label text-primary fw-bold">
                                    <i class="bi bi-qr-code"></i> Código del Bulto/Caja
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-upc-scan"></i></span>
                                    <input type="text" name="package_code" class="form-control @error('package_code') is-invalid @enderror" 
                                           value="{{ old('package_code') }}" placeholder="Código de la caja">
                                </div>
                                @error('package_code') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                <div class="form-text small">Solo si la caja tiene un código distinto a la unidad.</div>
                            </div>

                            <div class="row">
                                <div class="col-6 mb-3">
                                    <label class="form-label fw-bold">Presentación</label>
                                    <input type="text" name="presentation" class="form-control @error('presentation') is-invalid @enderror" 
                                           value="{{ old('presentation') }}" placeholder="Ej: Botella 1L" required>
                                    @error('presentation') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-6 mb-3">
                                    <label class="form-label fw-bold">Unidades x Bulto</label>
                                    <input type="number" name="units_per_package" class="form-control @error('units_per_package') is-invalid @enderror" 
                                           value="{{ old('units_per_package', 1) }}" min="1" required>
                                    @error('units_per_package') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-6 mb-3">
                                    <label class="form-label fw-bold">Precio Unitario ($)</label>
                                    <input type="number" step="0.01" name="price_per_unit" class="form-control @error('price_per_unit') is-invalid @enderror" 
                                           value="{{ old('price_per_unit') }}" required>
                                    @error('price_per_unit') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-6 mb-3">
                                    <label class="form-label fw-bold">Precio Bulto ($)</label>
                                    <input type="number" step="0.01" name="price_per_package" class="form-control @error('price_per_package') is-invalid @enderror" 
                                           value="{{ old('price_per_package') }}" required>
                                    @error('price_per_package') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold text-danger">Stock Inicial (Unidades)</label>
                                <input type="number" name="stock" class="form-control @error('stock') is-invalid @enderror" 
                                       value="{{ old('stock', 0) }}" min="0" required>
                                @error('stock') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>
                    </div>

                    <hr class="my-4">
                    
                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{ route('products.index') }}" class="btn btn-light border">Cancelar</a>
                        <button type="submit" class="btn btn-primary px-4">
                            <i class="bi bi-save"></i> Guardar Producto
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection