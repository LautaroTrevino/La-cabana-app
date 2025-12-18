@extends('layouts.app')

@section('content')
<div class="container py-4">
    {{-- ENCABEZADO --}}
    <div class="row mb-4">
        <div class="col-md-8">
            <h2 class="fw-bold">Editando Receta: <span class="text-primary">{{ $menu->name }}</span></h2>
            <p class="text-muted">Define los ingredientes genéricos y cantidades por cupo.</p>
        </div>
        <div class="col-md-4 text-end">
            <a href="{{ route('menus.index') }}" class="btn btn-secondary">Volver</a>
        </div>
    </div>
    
    {{-- FORMULARIO PRINCIPAL --}}
    <form action="{{ route('menus.update', $menu) }}" method="POST">
        @csrf 
        @method('PUT')
        
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
    <h5 class="mb-0 fw-bold">Ingredientes de la Receta</h5>
    <div class="d-flex gap-2">
        {{-- NUEVO: Botón para ir a la gestión central de ingredientes --}}
        <a href="{{ route('ingredients.index') }}" class="btn btn-outline-secondary btn-sm fw-bold">
            <i class="bi bi-gear"></i> Administrar Base de Ingredientes
        </a>

        {{-- Botón Ingrediente Nuevo (Abre el Modal de creación rápida) --}}
        <button type="button" class="btn btn-outline-primary btn-sm fw-bold" data-bs-toggle="modal" data-bs-target="#newIngredientModal">
            <i class="bi bi-star"></i> Nuevo Ingrediente
        </button>

        {{-- Botón Agregar Fila a esta receta --}}
        <button type="button" class="btn btn-success btn-sm fw-bold" onclick="agregarFila()">
            <i class="bi bi-plus-lg"></i> Agregar Fila
        </button>
    </div>
</div>

            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light text-center">
                            <tr>
                                <th width="25%" class="text-start ps-4">Ingrediente</th>
                                <th width="20%" class="text-start text-muted">Descripción</th>
                                <th width="15%" class="bg-warning bg-opacity-10">Jardín</th>
                                <th width="15%" class="bg-primary bg-opacity-10">Primaria</th>
                                <th width="15%" class="bg-secondary bg-opacity-10">Secundaria</th>
                                <th width="5%"></th>
                            </tr>
                        </thead>
                        <tbody id="listaIngredientes">
                            {{-- CARGAMOS INGREDIENTES QUE YA TENGA LA RECETA --}}
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
                                    
                                    <td class="text-start">
                                        <small class="text-muted fst-italic descripcion-texto">
                                            {{ $ing->description ?? '-' }}
                                        </small>
                                    </td>

                                    {{-- Cantidades con sufijo de unidad --}}
                                    <td>
                                        <div class="input-group input-group-sm">
                                            <input type="number" step="0.0001" name="ingredients[{{ $index }}][qty_jardin]" class="form-control text-center bg-warning bg-opacity-10" value="{{ $ing->pivot->qty_jardin }}">
                                            <span class="input-group-text badge-unidad bg-warning bg-opacity-25">{{ $ing->unit_type ?? 'Un.' }}</span>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="input-group input-group-sm">
                                            <input type="number" step="0.0001" name="ingredients[{{ $index }}][qty_primaria]" class="form-control text-center bg-primary bg-opacity-10" value="{{ $ing->pivot->qty_primaria }}">
                                            <span class="input-group-text badge-unidad bg-primary bg-opacity-25">{{ $ing->unit_type ?? 'Un.' }}</span>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="input-group input-group-sm">
                                            <input type="number" step="0.0001" name="ingredients[{{ $index }}][qty_secundaria]" class="form-control text-center bg-secondary bg-opacity-10" value="{{ $ing->pivot->qty_secundaria }}">
                                            <span class="input-group-text badge-unidad bg-secondary bg-opacity-25">{{ $ing->unit_type ?? 'Un.' }}</span>
                                        </div>
                                    </td>
                                    
                                    <td class="text-center">
                                        <button type="button" class="btn btn-outline-danger btn-sm border-0" onclick="this.closest('tr').remove()">
                                            <i class="bi bi-x-lg"></i>
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                
                <div id="mensajeVacio" class="text-center py-5 {{ $menu->ingredients->count() > 0 ? 'd-none' : '' }}">
                    <p class="text-muted">No hay ingredientes cargados. ¡Agrega el primero!</p>
                </div>
            </div>

            <div class="card-footer bg-white py-3 text-end">
                <button type="submit" class="btn btn-primary btn-lg fw-bold px-5">Guardar Receta</button>
            </div>
        </div>
    </form>
</div>

{{-- MODAL PARA CREAR NUEVO INGREDIENTE CON UNIDAD --}}
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
                        <label class="form-label fw-bold">Unidad de Medida</label>
                        <select name="unit_type" class="form-select" required>
                            <option value="Un.">Unidades (Un.)</option>
                            <option value="Kg.">Kilogramos (Kg.)</option>
                            <option value="Lts.">Litros (Lts.)</option>
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
        return [
            'id' => $i->id,
            'name' => $i->name,
            'description' => $i->description ?? '-',
            'unit' => $i->unit_type ?? 'Un.'
        ];
    });
@endphp

<script>
    const listadoIngredientes = @json($ingredientesJson);

    // Diccionario para búsqueda rápida
    const infoIngredientes = {};
    listadoIngredientes.forEach(ing => {
        infoIngredientes[ing.id] = {
            desc: ing.description,
            unit: ing.unit
        };
    });

    let rowIndex = {{ $menu->ingredients->count() }};

    function agregarFila() {
        document.getElementById('mensajeVacio').classList.add('d-none');
        const tbody = document.getElementById('listaIngredientes');
        
        let options = '<option value="">Seleccionar...</option>';
        listadoIngredientes.forEach(i => options += `<option value="${i.id}">${i.name}</option>`);

        const row = document.createElement('tr');
        row.innerHTML = `
            <td class="ps-4">
                <select name="ingredients[${rowIndex}][ingredient_id]" class="form-select" onchange="actualizarInfo(this)" required>
                    ${options}
                </select>
            </td>
            <td class="text-start">
                <small class="text-muted fst-italic descripcion-texto">-</small>
            </td>
            <td>
                <div class="input-group input-group-sm">
                    <input type="number" step="0.0001" name="ingredients[${rowIndex}][qty_jardin]" class="form-control text-center bg-warning bg-opacity-10">
                    <span class="input-group-text badge-unidad bg-warning bg-opacity-25">Un.</span>
                </div>
            </td>
            <td>
                <div class="input-group input-group-sm">
                    <input type="number" step="0.0001" name="ingredients[${rowIndex}][qty_primaria]" class="form-control text-center bg-primary bg-opacity-10">
                    <span class="input-group-text badge-unidad bg-primary bg-opacity-25">Un.</span>
                </div>
            </td>
            <td>
                <div class="input-group input-group-sm">
                    <input type="number" step="0.0001" name="ingredients[${rowIndex}][qty_secundaria]" class="form-control text-center bg-secondary bg-opacity-10">
                    <span class="input-group-text badge-unidad bg-secondary bg-opacity-25">Un.</span>
                </div>
            </td>
            <td class="text-center">
                <button type="button" class="btn btn-outline-danger btn-sm border-0" onclick="this.closest('tr').remove()">
                    <i class="bi bi-x-lg"></i>
                </button>
            </td>
        `;
        tbody.appendChild(row);
        rowIndex++;
    }

    function actualizarInfo(selectElement) {
        const id = parseInt(selectElement.value);
        const fila = selectElement.closest('tr');
        const celdaDesc = fila.querySelector('.descripcion-texto');
        const badgesUnidad = fila.querySelectorAll('.badge-unidad');

        if (id && infoIngredientes[id]) {
            celdaDesc.innerText = infoIngredientes[id].desc;
            badgesUnidad.forEach(span => {
                span.innerText = infoIngredientes[id].unit;
            });
        } else {
            celdaDesc.innerText = '-';
            badgesUnidad.forEach(span => {
                span.innerText = 'Un.';
            });
        }
    }
</script>
@endsection