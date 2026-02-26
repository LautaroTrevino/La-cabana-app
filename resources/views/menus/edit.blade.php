@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold text-gray-800">Editar Menú: <span class="text-primary">{{ $menu->name }}</span></h2>
        <a href="{{ route('menus.index') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Volver
        </a>
    </div>

    <form action="{{ route('menus.update', $menu->id) }}" method="POST">
        @csrf
        @method('PUT')

        {{-- DATOS BÁSICOS --}}
        <div class="card shadow border-0 mb-4">
            <div class="card-body p-4">
                <div class="row">
                    <div class="col-md-6">
                        <label class="fw-bold">Nombre del Menú</label>
                        <input type="text" name="name" class="form-control form-control-lg" value="{{ $menu->name }}" required>
                    </div>
                    <div class="col-md-6">
                        <label class="fw-bold">Tipo de Servicio</label>
                        <select name="type" class="form-select form-select-lg" required>
                            @foreach($tiposMenu as $tipo)
                                <option value="{{ $tipo }}" {{ $menu->type == $tipo ? 'selected' : '' }}>
                                    {{ $tipo }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
        </div>

        {{-- TABLA DE INGREDIENTES --}}
        <div class="card shadow border-0">
            <div class="card-header bg-dark text-white py-3">
                <h5 class="mb-0 fw-bold">Editar Receta</h5>
            </div>
            
            <div class="card-body p-0">
                <div class="table-responsive" style="max-height: 600px; overflow-y: auto;">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light text-center small text-uppercase sticky-top">
                            <tr>
                                <th class="text-start ps-4" width="25%">Ingrediente</th>
                                <th width="10%">Unidad</th>
                                <th width="15%" class="bg-warning bg-opacity-25">Cant. Jardín</th>
                                <th width="15%" class="bg-primary bg-opacity-25">Cant. Primaria</th>
                                <th width="15%" class="bg-secondary bg-opacity-25">Cant. Secundaria</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($ingredients as $ingredient)
                                @php
                                    // Verificar si este ingrediente ya está en el menú
                                    $isChecked = $menu->ingredients->contains($ingredient->id);
                                    // Obtener datos guardados (pivot) o nulos
                                    $pivot = $isChecked ? $menu->ingredients->find($ingredient->id)->pivot : null;
                                    $unit = $pivot->measure_unit ?? 'kg';
                                @endphp
                                <tr>
                                    <td class="ps-4">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" 
                                                   name="ingredients[]" 
                                                   value="{{ $ingredient->id }}" 
                                                   {{ $isChecked ? 'checked' : '' }}>
                                            <label class="form-check-label fw-bold">
                                                {{ $ingredient->name }}
                                            </label>
                                        </div>
                                    </td>
                                    <td>
                                        <select name="items[{{ $ingredient->id }}][measure_unit]" class="form-select form-select-sm">
                                            <option value="kg" {{ $unit == 'kg' ? 'selected' : '' }}>Kg</option>
                                            <option value="lt" {{ $unit == 'lt' ? 'selected' : '' }}>Lt</option>
                                            <option value="un" {{ $unit == 'un' ? 'selected' : '' }}>Un</option>
                                            <option value="gr" {{ $unit == 'gr' ? 'selected' : '' }}>Gr</option>
                                            <option value="cc" {{ $unit == 'cc' ? 'selected' : '' }}>CC</option>
                                        </select>
                                    </td>
                                    <td class="bg-warning bg-opacity-10">
                                        <input type="number" step="0.0001" name="items[{{ $ingredient->id }}][qty_jardin]" 
                                               class="form-control form-control-sm text-center" 
                                               value="{{ $pivot->qty_jardin ?? '' }}" placeholder="0">
                                    </td>
                                    <td class="bg-primary bg-opacity-10">
                                        <input type="number" step="0.0001" name="items[{{ $ingredient->id }}][qty_primaria]" 
                                               class="form-control form-control-sm text-center" 
                                               value="{{ $pivot->qty_primaria ?? '' }}" placeholder="0">
                                    </td>
                                    <td class="bg-secondary bg-opacity-10">
                                        <input type="number" step="0.0001" name="items[{{ $ingredient->id }}][qty_secundaria]" 
                                               class="form-control form-control-sm text-center" 
                                               value="{{ $pivot->qty_secundaria ?? '' }}" placeholder="0">
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="card-footer bg-white py-3 text-end">
                <button type="submit" class="btn btn-primary btn-lg fw-bold px-5 shadow">
                    <i class="bi bi-save"></i> GUARDAR CAMBIOS
                </button>
            </div>
        </div>
    </form>
</div>
@endsection