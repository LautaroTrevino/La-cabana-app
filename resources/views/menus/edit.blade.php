@extends('layouts.app')

@section('content')
<div class="container py-4">
    {{-- ENCABEZADO --}}
    <div class="row mb-4">
        <div class="col-md-8">
            <h2 class="fw-bold">Editando Receta: <span class="text-primary">{{ $menu->name }}</span></h2>
            <p class="text-muted">Define los ingredientes, la unidad de medida y las cantidades por cupo.</p>
        </div>
        <div class="col-md-4 text-end">
            <a href="{{ route('menus.index') }}" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Volver
            </a>
        </div>
    </div>
    
    {{-- FORMULARIO PRINCIPAL --}}
    <form action="{{ route('menus.update', $menu) }}" method="POST">
        @csrf 
        @method('PUT')
        
        {{-- SECCIÓN 1: DATOS BÁSICOS DEL MENÚ (Esto faltaba y causaba el error) --}}
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-body bg-light">
                <div class="row">
                    <div class="col-md-6 mb-3 mb-md-0">
                        <label class="form-label fw-bold">Nombre del Plato</label>
                        <input type="text" name="name" class="form-control" value="{{ old('name', $menu->name) }}" required>
                    </div>
                    <div class="col-md-2 mb-3 mb-md-0">
                        <label class="form-label fw-bold">Día N°</label>
                        <input type="number" name="day_number" class="form-control text-center" value="{{ old('day_number', $menu->day_number) }}" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Categoría</label>
                        <input type="text" class="form-control bg-white text-muted" value="{{ $menu->type }}" readonly disabled>
                    </div>
                </div>
            </div>
        </div>
        
        {{-- SECCIÓN 2: INGREDIENTES --}}
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <h5 class="mb-0 fw-bold">Ingredientes de la Receta</h5>
                <div class="d-flex gap-2">
                    <a href="{{ route('ingredients.index') }}" target="_blank" class="btn btn-outline-secondary btn-sm fw-bold">
                        <i class="bi bi-gear"></i> Base de Ingredientes
                    </a>
                    <button type="button" class="btn btn-outline-primary btn-sm fw-bold" data-bs-toggle="modal" data-bs-target="#newIngredientModal">
                        <i class="bi bi-star"></i> Nuevo Ingrediente
                    </button>
                    <button type="button" class="btn btn-success btn-sm fw-bold" onclick="agregarFila()">
                        <i class="bi bi-plus-lg"></i> Agregar Fila
                    </button>
                </div>
            </div>

            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light text-center small text-uppercase">
                            <tr>
                                <th width="25%" class="text-start ps-4">Ingrediente</th>
                                <th width="10%">Unidad</th>
                                <th width="20%" class="text-start text-muted">Descripción</th>
                                <th width="13%" class="bg-warning bg-opacity-10">Jardín</th>
                                <th width="13%" class="bg-primary bg-opacity-10">Primaria</th>
                                <th width="13%" class="bg-secondary bg-opacity-10">Secundaria</th>
                                <th width="6%"></th>
                            </tr>
                        </thead>
                        <tbody id="listaIngredientes">
                            @foreach($menu->ingredients as $index => $ing)
                                <tr>
                                    <td class="ps-4">
                                        <select name="ingredients[{{ $index }}][ingredient_id]" class="form-select" onchange="actualizarInfo(this)">
                                            <option value="{{ $ing->id }}" selected>{{ $ing->name }}</option>
                                            @foreach($ingredients as $i)
                                                @if($i->id != $ing->id) 
                                                    <option value="{{ $i->id }}">{{ $i->name }}</option> 
                                                @endif
                                            @endforeach
                                        </select>
                                    </td>
                                    <td>
                                        <select name="ingredients[{{ $index }}][measure_unit]" class="form-select form-select-sm bg-light fw-bold text-center selector-unidad">
                                            @php 
                                                $currentUnit = $ing->pivot->measure_unit ?? ($ing->unit_type ?? 'grams');
                                                if($currentUnit == 'Kg.') $currentUnit = 'grams';
                                                if($currentUnit == 'Lts.') $currentUnit = 'cc';
                                                if($currentUnit == 'Un.') $currentUnit = 'units';
                                            @endphp
                                            <option value="grams" {{ $currentUnit == 'grams' ? 'selected' : '' }}>Gramos</option>
                                            <option value="cc" {{ $currentUnit == 'cc' ? 'selected' : '' }}>CC / ML</option>
                                            <option value="units" {{ $currentUnit == 'units' ? 'selected' : '' }}>Unidades</option>
                                        </select>
                                    </td>
                                    <td class="text-start">
                                        <small class="text-muted fst-italic descripcion-texto">
                                            {{ $ing->description ?? '-' }}
                                        </small>
                                    </td>
                                    <td class="bg-warning bg-opacity-10">
                                        <input type="number" step="0.0001" name="ingredients[{{ $index }}][qty_jardin]" class="form-control text-center form-control-sm" value="{{ $ing->pivot->qty_jardin }}" placeholder="0">
                                    </td>
                                    <td class="bg-primary bg-opacity-10">
                                        <input type="number" step="0.0001" name="ingredients[{{ $index }}][qty_primaria]" class="form-control text-center form-control-sm" value="{{ $ing->pivot->qty_primaria }}" placeholder="0">
                                    </td>
                                    <td class="bg-secondary bg-opacity-10">
                                        <input type="number" step="0.0001" name="ingredients[{{ $index }}][qty_secundaria]" class="form-control text-center form-control-sm" value="{{ $ing->pivot->qty_secundaria }}" placeholder="0">
                                    </td>
                                    <td class="text-center">
                                        <button type="button" class="btn btn-outline-danger btn-sm border-0" onclick="this.closest('tr').remove()">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                
                <div id="mensajeVacio" class="text-center py-5 {{ $menu->ingredients->count() > 0 ? 'd-none' : '' }}">
                    <i class="bi bi-basket3 fs-1 text-muted mb-2 d-block"></i>
                    <p class="text-muted">No hay ingredientes cargados. ¡Agrega el primero!</p>
                </div>
            </div>

            <div class="card-footer bg-white py-3 text-end">
                <button type="submit" class="btn btn-primary btn-lg fw-bold px-5">
                    <i class="bi bi-save"></i> Guardar Receta
                </button>
            </div>
        </div>
    </form>
</div>

{{-- MODAL PARA CREAR NUEVO INGREDIENTE --}}
<div class="modal fade" id="newIngredientModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">Crear Nuevo Ingrediente</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('ingredients.store_api') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Nombre</label>
                        <input type="text" name="name" class="form-control" placeholder="Ej: Polenta" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Unidad Sugerida</label>
                        <select name="unit_type" class="form-select" required>
                            <option value="grams">Gramos (g)</option>
                            <option value="cc">Centimetros Cúbicos (cc)</option>
                            <option value="units">Unidades (Un.)</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Descripción (Opcional)</label>
                        <textarea name="description" class="form-control" rows="2" placeholder="Ej: Harina de maíz precocida..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar Ingrediente</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- JAVASCRIPT --}}
@php
    $ingredientesJson = $ingredients->map(function($i) {
        $u = $i->unit_type;
        if($u == 'Kg.' || $u == 'Grs.') $u = 'grams';
        if($u == 'Lts.' || $u == 'CC.') $u = 'cc';
        if($u == 'Un.') $u = 'units';
        return [
            'id' => $i->id,
            'name' => $i->name,
            'description' => $i->description ?? '-',
            'unit' => $u ?? 'grams'
        ];
    });
@endphp

<script>
    const listadoIngredientes = @json($ingredientesJson);
    const infoIngredientes = {};
    listadoIngredientes.forEach(ing => {
        infoIngredientes[ing.id] = { desc: ing.description, unit: ing.unit };
    });

    let rowIndex = {{ $menu->ingredients->count() }} + Date.now();

    function agregarFila() {
        document.getElementById('mensajeVacio').classList.add('d-none');
        const tbody = document.getElementById('listaIngredientes');
        
        let options = '<option value="">Seleccionar...</option>';
        listadoIngredientes.forEach(i => options += `<option value="${i.id}">${i.name}</option>`);

        const row = document.createElement('tr');
        row.innerHTML = `
            <td class="ps-4">
                <select name="ingredients[${rowIndex}][ingredient_id]" class="form-select" onchange="actualizarInfo(this)" required>${options}</select>
            </td>
            <td>
                <select name="ingredients[${rowIndex}][measure_unit]" class="form-select form-select-sm bg-light fw-bold text-center selector-unidad">
                    <option value="grams">Gramos</option>
                    <option value="cc">CC / ML</option>
                    <option value="units">Unidades</option>
                </select>
            </td>
            <td class="text-start"><small class="text-muted fst-italic descripcion-texto">-</small></td>
            <td class="bg-warning bg-opacity-10"><input type="number" step="0.0001" name="ingredients[${rowIndex}][qty_jardin]" class="form-control text-center form-control-sm" placeholder="0"></td>
            <td class="bg-primary bg-opacity-10"><input type="number" step="0.0001" name="ingredients[${rowIndex}][qty_primaria]" class="form-control text-center form-control-sm" placeholder="0"></td>
            <td class="bg-secondary bg-opacity-10"><input type="number" step="0.0001" name="ingredients[${rowIndex}][qty_secundaria]" class="form-control text-center form-control-sm" placeholder="0"></td>
            <td class="text-center"><button type="button" class="btn btn-outline-danger btn-sm border-0" onclick="this.closest('tr').remove()"><i class="bi bi-trash"></i></button></td>
        `;
        tbody.appendChild(row);
        rowIndex++;
    }

    function actualizarInfo(selectElement) {
        const id = parseInt(selectElement.value);
        const fila = selectElement.closest('tr');
        if (id && infoIngredientes[id]) {
            fila.querySelector('.descripcion-texto').innerText = infoIngredientes[id].desc;
            fila.querySelector('.selector-unidad').value = infoIngredientes[id].unit;
        }
    }
</script>
@endsection