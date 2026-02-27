@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold text-gray-800">Editar Menú: <span class="text-primary">{{ $menu->name }}</span></h2>
        <a href="{{ route('menus.index') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Volver
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show shadow-sm">
            <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <form action="{{ route('menus.update', $menu->id) }}" method="POST">
        @csrf
        @method('PUT')

        {{-- DATOS BÁSICOS --}}
        <div class="card shadow border-0 mb-4">
            <div class="card-body p-4">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="fw-bold">Nombre del Menú</label>
                        <input type="text" name="name" class="form-control form-control-lg"
                               value="{{ $menu->name }}" required>
                    </div>
                    <div class="col-md-2">
                        <label class="fw-bold">Día N°</label>
                        <input type="number" name="day_number"
                               class="form-control form-control-lg text-center"
                               value="{{ $menu->day_number }}" min="1" max="31" required>
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
            <div class="card-header bg-dark text-white py-3 d-flex justify-content-between align-items-center">
                <h5 class="mb-0 fw-bold"><i class="bi bi-basket"></i> Receta / Ingredientes</h5>
                <button type="button" class="btn btn-success btn-sm fw-bold"
                        data-bs-toggle="modal" data-bs-target="#newIngredientModal">
                    <i class="bi bi-plus-circle"></i> Nuevo Ingrediente
                </button>
            </div>

            <div class="card-body p-0">
                @if($ingredients->count() == 0)
                    <div class="text-center py-5">
                        <i class="bi bi-basket fs-1 text-muted"></i>
                        <p class="text-muted mt-2">No hay ingredientes. Creá el primero.</p>
                        <button type="button" class="btn btn-outline-success fw-bold"
                                data-bs-toggle="modal" data-bs-target="#newIngredientModal">
                            <i class="bi bi-plus-circle"></i> Crear ingrediente
                        </button>
                    </div>
                @else
                    <div class="px-4 py-2 bg-light border-bottom small text-muted">
                        <i class="bi bi-info-circle text-primary"></i>
                        Tildá los ingredientes de esta receta y completá las cantidades por nivel.
                        Los que queden <strong>sin tildar</strong> serán eliminados de la receta al guardar.
                    </div>

                    <div class="table-responsive" style="max-height: 600px; overflow-y: auto;">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-dark text-center small text-uppercase sticky-top">
                                <tr>
                                    <th class="text-start ps-4" style="width:30%">Ingrediente</th>
                                    <th style="width:12%">Unidad</th>
                                    <th style="width:15%" class="bg-warning bg-opacity-50">Cant. Jardín</th>
                                    <th style="width:15%" class="bg-primary bg-opacity-50">Cant. Primaria</th>
                                    <th style="width:15%" class="bg-secondary bg-opacity-50">Cant. Secundaria</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($ingredients as $index => $ingredient)
                                    @php
                                        $isChecked = $menu->ingredients->contains($ingredient->id);
                                        $pivot     = $isChecked
                                                        ? $menu->ingredients->find($ingredient->id)->pivot
                                                        : null;

                                        $rawUnit = $pivot->measure_unit ?? $ingredient->unit_type ?? 'grams';
                                        $unit = match(strtolower(trim($rawUnit))) {
                                            'kg', 'gr', 'g'       => 'grams',
                                            'lt', 'ml'            => 'cc',
                                            'un', 'u'             => 'units',
                                            'grams','cc','units'  => $rawUnit,
                                            default               => 'grams',
                                        };
                                    @endphp

                                    {{--
                                        ESTRATEGIA CONFIABLE:
                                        - ingredient_id SIEMPRE se envía (no depende de JS)
                                        - El checkbox controla si la fila está "activa" visualmente
                                        - El controller usa ingredient_id para saber qué guardar
                                          e ignora filas donde el checkbox no está tildado
                                          gracias al campo `_active` que acompaña cada fila
                                    --}}
                                    <tr id="row_{{ $ingredient->id }}"
                                        class="{{ $isChecked ? 'table-success' : 'opacity-50' }}">
                                        <td class="ps-4">
                                            {{-- Siempre enviamos el id; el campo _active indica si está seleccionado --}}
                                            <input type="hidden"
                                                   name="ingredients[{{ $index }}][ingredient_id]"
                                                   value="{{ $ingredient->id }}">
                                            <input type="hidden"
                                                   name="ingredients[{{ $index }}][_active]"
                                                   value="{{ $isChecked ? '1' : '0' }}"
                                                   id="active_{{ $ingredient->id }}">

                                            <div class="form-check">
                                                <input class="form-check-input"
                                                       type="checkbox"
                                                       id="chk_{{ $ingredient->id }}"
                                                       onchange="toggleIngrediente('{{ $ingredient->id }}')"
                                                       {{ $isChecked ? 'checked' : '' }}>
                                                <label class="form-check-label fw-bold"
                                                       for="chk_{{ $ingredient->id }}">
                                                    {{ $ingredient->name }}
                                                </label>
                                            </div>
                                        </td>
                                        <td>
                                            <select name="ingredients[{{ $index }}][measure_unit]"
                                                    class="form-select form-select-sm">
                                                <option value="grams" {{ $unit=='grams' ? 'selected':'' }}>Gramos</option>
                                                <option value="cc"    {{ $unit=='cc'    ? 'selected':'' }}>CC / ML</option>
                                                <option value="units" {{ $unit=='units' ? 'selected':'' }}>Unidades</option>
                                            </select>
                                        </td>
                                        <td class="bg-warning bg-opacity-10">
                                            <input type="number" step="0.0001" min="0"
                                                   name="ingredients[{{ $index }}][qty_jardin]"
                                                   class="form-control form-control-sm text-center"
                                                   value="{{ $pivot->qty_jardin ?? '' }}"
                                                   placeholder="0">
                                        </td>
                                        <td class="bg-primary bg-opacity-10">
                                            <input type="number" step="0.0001" min="0"
                                                   name="ingredients[{{ $index }}][qty_primaria]"
                                                   class="form-control form-control-sm text-center"
                                                   value="{{ $pivot->qty_primaria ?? '' }}"
                                                   placeholder="0">
                                        </td>
                                        <td class="bg-secondary bg-opacity-10">
                                            <input type="number" step="0.0001" min="0"
                                                   name="ingredients[{{ $index }}][qty_secundaria]"
                                                   class="form-control form-control-sm text-center"
                                                   value="{{ $pivot->qty_secundaria ?? '' }}"
                                                   placeholder="0">
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>

            <div class="card-footer bg-white py-3 text-end">
                <button type="submit" class="btn btn-primary btn-lg fw-bold px-5 shadow">
                    <i class="bi bi-save"></i> GUARDAR CAMBIOS
                </button>
            </div>
        </div>
    </form>
</div>

{{-- MODAL NUEVO INGREDIENTE --}}
<div class="modal fade" id="newIngredientModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title fw-bold">
                    <i class="bi bi-plus-circle me-1"></i> Nuevo Ingrediente
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('ingredients.store_api') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <p class="text-muted small mb-3">
                        Se guardará en la base de ingredientes. No afecta el stock.
                        <strong>La página se recarga para que aparezca en la lista.</strong>
                    </p>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Nombre <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control"
                               placeholder="Ej: Arroz, Aceite, Leche..." required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Descripción (opcional)</label>
                        <input type="text" name="description" class="form-control"
                               placeholder="Ej: Arroz largo fino">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Unidad de Medida <span class="text-danger">*</span></label>
                        <select name="unit_type" class="form-select" required>
                            <option value="grams">Gramos / Kilogramos (g.)</option>
                            <option value="cc">Mililitros / Litros (cc.)</option>
                            <option value="units">Unidades (un.)</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success fw-bold">
                        <i class="bi bi-save"></i> Guardar Ingrediente
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function toggleIngrediente(id) {
        var checkbox = document.getElementById('chk_' + id);
        var activeInput = document.getElementById('active_' + id);
        var row = document.getElementById('row_' + id);

        if (checkbox.checked) {
            activeInput.value = '1';
            row.classList.remove('opacity-50');
            row.classList.add('table-success');
        } else {
            activeInput.value = '0';
            row.classList.remove('table-success');
            row.classList.add('opacity-50');
        }
    }
</script>
@endsection
