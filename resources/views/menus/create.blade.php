@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold text-gray-800">Crear Nuevo Menú</h2>
        <a href="{{ route('menus.index') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Volver
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
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
                        <input type="text" name="name" class="form-control form-control-lg"
                               placeholder="Ej: Fideos con Tuco"
                               value="{{ old('name') }}" required>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-bold">Día N°</label>
                        <input type="number" name="day_number"
                               class="form-control form-control-lg text-center"
                               value="{{ old('day_number', 1) }}" min="1" max="31" required>
                    </div>
                    <div class="col-md-5">
                        <label class="form-label fw-bold">Tipo de Servicio</label>
                        <select name="type" class="form-select form-select-lg" required>
                            <option value="" disabled selected>Seleccioná...</option>
                            @foreach($tiposMenu as $tipo)
                                <option value="{{ $tipo }}" {{ old('type') == $tipo ? 'selected' : '' }}>
                                    {{ $tipo }}
                                </option>
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
                <button type="button" class="btn btn-success btn-sm fw-bold"
                        data-bs-toggle="modal" data-bs-target="#newIngredientModal">
                    <i class="bi bi-plus-circle"></i> Nuevo Ingrediente
                </button>
            </div>

            <div class="card-body p-0">
                @if($ingredients->count() == 0)
                    <div class="text-center py-5">
                        <i class="bi bi-basket fs-1 text-muted"></i>
                        <p class="text-muted mt-2">La lista de ingredientes está vacía.</p>
                        <button type="button" class="btn btn-outline-success fw-bold"
                                data-bs-toggle="modal" data-bs-target="#newIngredientModal">
                            Crear el primer ingrediente (Ej: Fideos)
                        </button>
                    </div>
                @else
                    <div class="alert alert-light border-bottom mb-0 small">
                        <i class="bi bi-info-circle text-primary"></i>
                        Marcá los ingredientes y completá las cantidades por nivel educativo.
                        <strong>Estos datos son solo para generar remitos administrativos.</strong>
                    </div>

                    <div class="table-responsive" style="max-height: 600px; overflow-y: auto;">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light text-center small text-uppercase sticky-top">
                                <tr>
                                    <th class="text-start ps-4" width="25%">Ingrediente</th>
                                    <th width="12%">Unidad</th>
                                    <th width="15%" class="bg-warning bg-opacity-25">Cant. Jardín</th>
                                    <th width="15%" class="bg-primary bg-opacity-25">Cant. Primaria</th>
                                    <th width="15%" class="bg-secondary bg-opacity-25">Cant. Secundaria</th>
                                </tr>
                            </thead>
                            <tbody>
                                {{--
                                    FIX: La estructura del array debe coincidir con lo que
                                    espera MenuController::store() → ingredients[índice][ingredient_id]
                                    Se usa un índice numérico con JS para los checkboxes.
                                --}}
                                @foreach($ingredients as $index => $ingredient)
                                    <tr class="ingredient-row" data-index="{{ $index }}">
                                        <td class="ps-4">
                                            {{-- ingredient_id oculto, se activa con el checkbox --}}
                                            <input type="hidden"
                                                   name="ingredients[{{ $index }}][ingredient_id]"
                                                   value=""
                                                   class="hidden-ing-id"
                                                   data-real-id="{{ $ingredient->id }}">
                                            <div class="form-check">
                                                <input class="form-check-input ingredient-checkbox"
                                                       type="checkbox"
                                                       id="ing_{{ $ingredient->id }}"
                                                       data-index="{{ $index }}"
                                                       data-id="{{ $ingredient->id }}">
                                                <label class="form-check-label fw-bold cursor-pointer"
                                                       for="ing_{{ $ingredient->id }}">
                                                    {{ $ingredient->name }}
                                                    <small class="text-muted fw-normal">({{ $ingredient->unit_type ?? 'u.' }})</small>
                                                </label>
                                            </div>
                                        </td>
                                        <td>
                                            {{-- FIX: name="measure_unit" coincide con MenuController --}}
                                            <select name="ingredients[{{ $index }}][measure_unit]"
                                                    class="form-select form-select-sm">
                                                <option value="grams" {{ ($ingredient->unit_type == 'grams') ? 'selected' : '' }}>Gramos</option>
                                                <option value="cc"    {{ ($ingredient->unit_type == 'cc')    ? 'selected' : '' }}>CC / ML</option>
                                                <option value="units" {{ ($ingredient->unit_type == 'units') ? 'selected' : '' }}>Unidades</option>
                                            </select>
                                        </td>
                                        <td class="bg-warning bg-opacity-10">
                                            <input type="number" step="0.0001" min="0"
                                                   name="ingredients[{{ $index }}][qty_jardin]"
                                                   class="form-control form-control-sm text-center" placeholder="0">
                                        </td>
                                        <td class="bg-primary bg-opacity-10">
                                            <input type="number" step="0.0001" min="0"
                                                   name="ingredients[{{ $index }}][qty_primaria]"
                                                   class="form-control form-control-sm text-center" placeholder="0">
                                        </td>
                                        <td class="bg-secondary bg-opacity-10">
                                            <input type="number" step="0.0001" min="0"
                                                   name="ingredients[{{ $index }}][qty_secundaria]"
                                                   class="form-control form-control-sm text-center" placeholder="0">
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
                        <input type="text" name="name" class="form-control"
                               placeholder="Ej: Fideos Mostachol" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Descripción (opcional)</label>
                        <input type="text" name="description" class="form-control"
                               placeholder="Ej: Pasta seca de trigo">
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Unidad de Medida Principal</label>
                        {{-- FIX: name="unit_type" coincide con el campo de Ingredient y la validación del controller --}}
                        <select name="unit_type" class="form-select" required>
                            <option value="grams">Gramos / Kilogramos (g.)</option>
                            <option value="cc">Mililitros / Litros (cc.)</option>
                            <option value="units">Unidades (un.)</option>
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

<script>
    // Activar/desactivar el hidden input del ingredient_id al tildar el checkbox
    // El controller ignora filas donde ingredient_id esté vacío
    document.querySelectorAll('.ingredient-checkbox').forEach(function(checkbox) {
        checkbox.addEventListener('change', function() {
            var idx    = this.dataset.index;
            var realId = this.dataset.id;
            var hidden = document.querySelector('.hidden-ing-id[data-real-id="' + realId + '"]');
            hidden.value = this.checked ? realId : '';
        });
    });
</script>
@endsection
