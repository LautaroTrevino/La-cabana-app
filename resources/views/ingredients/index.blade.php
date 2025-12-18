@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold">Gestión de Ingredientes</h2>
        <a href="{{ route('menus.index') }}" class="btn btn-secondary">Volver a Menús</a>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Nombre</th>
                        <th>Unidad</th>
                        <th>Descripción</th>
                        <th class="text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($ingredients as $ing)
                    <tr>
                        <td><strong>{{ $ing->name }}</strong></td>
                        <td><span class="badge bg-info text-dark">{{ $ing->unit_type }}</span></td>
                        <td class="text-muted">{{ $ing->description ?? '-' }}</td>
                        <td class="text-end">
                            <button class="btn btn-sm btn-outline-primary" 
                                    data-bs-toggle="modal" 
                                    data-bs-target="#editModal{{ $ing->id }}">
                                <i class="bi bi-pencil"></i>
                            </button>

                            <form action="{{ route('ingredients.destroy', $ing) }}" method="POST" class="d-inline">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('¿Eliminar ingrediente?')">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>

                    <div class="modal fade" id="editModal{{ $ing->id }}" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog">
                            <form action="{{ route('ingredients.update', $ing) }}" method="POST">
                                @csrf @method('PUT')
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">Editar Ingrediente</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="mb-3">
                                            <label class="form-label">Nombre</label>
                                            <input type="text" name="name" class="form-control" value="{{ $ing->name }}" required>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Unidad</label>
                                            <select name="unit_type" class="form-select">
                                                <option value="Un." {{ $ing->unit_type == 'Un.' ? 'selected' : '' }}>Unidades</option>
                                                <option value="Kg." {{ $ing->unit_type == 'Kg.' ? 'selected' : '' }}>Kilogramos</option>
                                                <option value="Lts." {{ $ing->unit_type == 'Lts.' ? 'selected' : '' }}>Litros</option>
                                            </select>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Descripción</label>
                                            <textarea name="description" class="form-control">{{ $ing->description }}</textarea>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection