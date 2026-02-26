@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold text-gray-800">Crear Nuevo Menú</h2>
        <a href="{{ route('menus.index') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Volver
        </a>
    </div>

    {{-- MENSAJES DE ÉXITO (Para cuando creas un ingrediente) --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- FORMULARIO PRINCIPAL --}}
    <form action="{{ route('menus.store') }}" method="POST">
        @csrf

        {{-- SECCIÓN 1: DATOS GENERALES --}}
        <div class="card shadow border-0 mb-4">
            <div class="card-body p-4">
                <div class="row">
                    <div class="col-md-5">
                        <label class="form-label fw-bold">Nombre del Plato</label>
                        <input type="text" name="name" class="form-control form-control-lg" placeholder="Ej: Fideos con Tuco" required>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-bold">Día N°</label>
                        <input type="number" name="day_number" class="form-control form-control-lg text-center" value="1" min="1" required>
                    </div>
                    <div class="col-md-5">
                        <label class="form-label fw-bold">Tipo de Servicio</label>
                        <select name="type" class="form-select form-select-lg" required>
                            <option value="" disabled selected>Selecciona...</option>
                            @foreach($tiposMenu as $tipo)
                                <option value="{{ $tipo }}">{{ $tipo }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
        </div>

        {{-- SECCIÓN 2: INGREDIENTES --}}
        <div class="card shadow border-0">
            <div class="card-header bg-dark text-white py-3 d-flex justify-content-between align-items-center">
                <h5 class="mb-0 fw-bold"><i class="bi bi-basket"></i> Definir Receta (Administrativa)</h5>
                
                {{-- BOTÓN PARA ABRIR EL MODAL DE NUEVO INGREDIENTE --}}
                <button type="button" class="btn btn-success btn-sm fw-bold" data-bs-toggle="modal" data-bs-target="#newIngredientModal">
                    <i class="bi bi-plus-circle"></i> Nuevo Ingrediente
                </button>
            </div>
            
            <div class="card-body p-0">
                @if($ingredients->count() == 0)
                    <div class="text-center py-5">
                        <i class="bi bi-basket fs-1 text-muted"></i>
                        <p class="text-muted mt-2">La lista de ingredientes está vacía.</p>
                        <button type="button" class="btn btn-outline-success fw-bold" data-bs-toggle="modal" data-bs-target="#newIngredientModal">
                            Crear el primer ingrediente (Ej: Fideos)
                        </button>
                    </div>
                @else
                    <div class="alert alert-light border-bottom mb-0 small">
                        <i class="bi bi-info-circle text-primary"></i> Marca los ingredientes y completa las cantidades. 
                        <strong>Estos datos son solo para generar los remitos administrativos.</strong>
                    </div>

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
                                    <tr>
                                        <td class="ps-4">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" 
                                                       name="ingredients[]" 
                                                       value="{{ $ingredient->id }}" 
                                                       id="ing_{{ $ingredient->id }}">
                                                <label class="form-check-label fw-bold cursor-pointer" for="ing_{{ $ingredient->id }}">
                                                    {{ $ingredient->name }}
                                                </label>
                                            </div>
                                        </td>
                                        <td>
                                            <select name="items[{{ $ingredient->id }}][measure_unit]" class="form-select form-select-sm">
                                                <option value="kg">Kg</option>
                                                <option value="lt">Lt</option>
                                                <option value="un">Un</option>
                                                <option value="gr">Gr</option>
                                                <option value="cc">CC</option>
                                            </select>
                                        </td>
                                        <td class="bg-warning bg-opacity-10">
                                            <input type="number" step="0.0001" min="0" name="items[{{ $ingredient->id }}][qty_jardin]" class="form-control form-control-sm text-center" placeholder="0">
                                        </td>
                                        <td class="bg-primary bg-opacity-10">
                                            <input type="number" step="0.0001" min="0" name="items[{{ $ingredient->id }}][qty_primaria]" class="form-control form-control-sm text-center" placeholder="0">
                                        </td>
                                        <td class="bg-secondary bg-opacity-10">
                                            <input type="number" step="0.0001" min="0" name="items[{{ $ingredient->id }}][qty_secundaria]" class="form-control form-control-sm text-center" placeholder="0">
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>

            <div class="card-footer bg-white py-3 text-end">
                <button type="submit" class="btn btn-success btn-lg fw-bold px-5 shadow">
                    <i class="bi bi-save"></i> GUARDAR MENÚ
                </button>
            </div>
        </div>
    </form>
</div>

{{-- MODAL PARA CREAR INGREDIENTE RÁPIDO --}}
<div class="modal fade" id="newIngredientModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title fw-bold">Nuevo Ingrediente (Administrativo)</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('ingredients.store_api') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <p class="text-muted small">Este ingrediente se guardará en la lista de nombres para usar en las recetas. No afecta al stock.</p>
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">Nombre</label>
                        <input type="text" name="name" class="form-control" placeholder="Ej: Fideos Mostachol" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">Unidad de Medida Principal</label>
                        <select name="unit" class="form-select" required>
                            <option value="kg">Kilogramos (kg)</option>
                            <option value="lt">Litros (lt)</option>
                            <option value="un">Unidades (un)</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success fw-bold">Guardar y Usar</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection