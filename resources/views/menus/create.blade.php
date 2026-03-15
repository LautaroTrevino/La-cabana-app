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

            <div class="card-body p-4">

                {{-- BUSCADOR --}}
                <div class="row g-2 align-items-end mb-4">
                    <div class="col position-relative">
                        <label class="form-label fw-bold small text-uppercase text-muted">Buscar ingrediente</label>
                        <input type="text" id="ingredientSearch"
                               class="form-control form-control-lg"
                               placeholder="Escribí para buscar... (ej: Fideos)"
                               autocomplete="off">
                        <div id="searchDropdown"
                             class="list-group shadow position-absolute"
                             style="display:none; z-index:1000; width:100%; max-height:220px; overflow-y:auto; top:100%;">
                        </div>
                    </div>
                    <div class="col-auto">
                        <button type="button" id="btnAddIngredient" class="btn btn-primary btn-lg fw-bold" disabled>
                            <i class="bi bi-plus-lg"></i> Agregar
                        </button>
                    </div>
                </div>

                {{-- ESTADO VACÍO --}}
                <div id="noIngredients" class="text-center py-4 text-muted border rounded bg-light">
                    <i class="bi bi-basket fs-2 d-block mb-2"></i>
                    Todavía no agregaste ningún ingrediente.<br>Buscá uno arriba para comenzar.
                </div>

                {{-- TABLA DE INGREDIENTES AGREGADOS --}}
                <div class="table-responsive" id="ingredientsTableWrapper" style="display:none;">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light text-center small text-uppercase sticky-top">
                            <tr>
                                <th class="text-start ps-3" width="28%">Ingrediente</th>
                                <th width="13%">Unidad</th>
                                <th width="15%" class="bg-warning bg-opacity-25">Cant. Jardín</th>
                                <th width="15%" class="bg-primary bg-opacity-25">Cant. Primaria</th>
                                <th width="15%" class="bg-secondary bg-opacity-25">Cant. Secundaria</th>
                                <th width="8%"></th>
                            </tr>
                        </thead>
                        <tbody id="ingredientsTableBody"></tbody>
                    </table>
                </div>
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
const allIngredients = {!! $ingredientsJson !!};

const unitLabels = { grams: 'Gramos', cc: 'CC / ML', units: 'Unidades' };

let rowIndex      = 0;
let selectedIngId = null;
let addedIds      = new Set();

const searchInput  = document.getElementById('ingredientSearch');
const dropdown     = document.getElementById('searchDropdown');
const btnAdd       = document.getElementById('btnAddIngredient');
const tableBody    = document.getElementById('ingredientsTableBody');
const tableWrapper = document.getElementById('ingredientsTableWrapper');
const noMsg        = document.getElementById('noIngredients');

// Buscador con dropdown
searchInput.addEventListener('input', function () {
    const q = this.value.trim().toLowerCase();
    selectedIngId   = null;
    btnAdd.disabled = true;
    dropdown.innerHTML = '';

    if (q.length < 1) { dropdown.style.display = 'none'; return; }

    const matches = allIngredients.filter(i =>
        i.name.toLowerCase().includes(q) && !addedIds.has(i.id)
    );

    if (matches.length === 0) {
        dropdown.innerHTML = '<span class="list-group-item text-muted small py-2">Sin resultados</span>';
        dropdown.style.display = 'block';
        return;
    }

    matches.forEach(ing => {
        const item = document.createElement('button');
        item.type = 'button';
        item.className = 'list-group-item list-group-item-action py-2';
        item.innerHTML = `<strong>${ing.name}</strong> <small class="text-muted ms-1">(${unitLabels[ing.unit_type] || ing.unit_type})</small>`;
        item.addEventListener('click', () => {
            searchInput.value = ing.name;
            selectedIngId     = ing.id;
            btnAdd.disabled   = false;
            dropdown.style.display = 'none';
            btnAdd.focus();
        });
        dropdown.appendChild(item);
    });

    dropdown.style.display = 'block';
});

// Cerrar dropdown al hacer click afuera
document.addEventListener('click', e => {
    if (!searchInput.contains(e.target) && !dropdown.contains(e.target))
        dropdown.style.display = 'none';
});

// Permitir seleccionar con Enter en el primer resultado
searchInput.addEventListener('keydown', e => {
    if (e.key === 'Enter') {
        e.preventDefault();
        const first = dropdown.querySelector('button');
        if (first) first.click();
    }
});

// Agregar fila a la tabla
btnAdd.addEventListener('click', function () {
    if (!selectedIngId) return;
    const ing = allIngredients.find(i => i.id === selectedIngId);
    if (!ing) return;

    addedIds.add(ing.id);
    const idx = rowIndex++;

    const unitOpts = Object.entries(unitLabels).map(([val, label]) =>
        `<option value="${val}" ${val === ing.unit_type ? 'selected' : ''}>${label}</option>`
    ).join('');

    const tr = document.createElement('tr');
    tr.dataset.ingId = ing.id;
    tr.innerHTML = `
        <td class="ps-3 fw-bold">
            ${ing.name}
            <input type="hidden" name="ingredients[${idx}][ingredient_id]" value="${ing.id}">
        </td>
        <td>
            <select name="ingredients[${idx}][measure_unit]" class="form-select form-select-sm">${unitOpts}</select>
        </td>
        <td class="bg-warning bg-opacity-10">
            <input type="number" step="0.0001" min="0" name="ingredients[${idx}][qty_jardin]"
                   class="form-control form-control-sm text-center" placeholder="0" autofocus>
        </td>
        <td class="bg-primary bg-opacity-10">
            <input type="number" step="0.0001" min="0" name="ingredients[${idx}][qty_primaria]"
                   class="form-control form-control-sm text-center" placeholder="0">
        </td>
        <td class="bg-secondary bg-opacity-10">
            <input type="number" step="0.0001" min="0" name="ingredients[${idx}][qty_secundaria]"
                   class="form-control form-control-sm text-center" placeholder="0">
        </td>
        <td class="text-center">
            <button type="button" class="btn btn-outline-danger btn-sm btn-remove" title="Quitar">
                <i class="bi bi-trash"></i>
            </button>
        </td>`;

    tr.querySelector('.btn-remove').addEventListener('click', () => {
        addedIds.delete(ing.id);
        tr.remove();
        updateTable();
    });

    tableBody.appendChild(tr);
    updateTable();

    // Foco al primer campo de cantidad de la fila recién agregada
    tr.querySelector('input[type="number"]').focus();

    // Resetear buscador
    searchInput.value = '';
    selectedIngId     = null;
    btnAdd.disabled   = true;
    searchInput.focus();
});

function updateTable() {
    const hasRows = tableBody.querySelectorAll('tr').length > 0;
    tableWrapper.style.display = hasRows ? 'block' : 'none';
    noMsg.style.display        = hasRows ? 'none'  : 'block';
}
</script>
@endsection
