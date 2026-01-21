@extends('layouts.app')

@section('header')
    <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
        {{ __('Gestión de Escuelas') }}
    </h2>
@endsection

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="container-fluid"> 

            {{-- Encabezado y Botón Agregar --}}
            <div class="row mb-4 align-items-center">
                <div class="col-md-8">
                    <h3 class="mb-0 text-gray-800 dark:text-gray-200">Listado de Escuelas y Cupos</h3>
                </div>
                <div class="col-md-4 text-end">
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createClientModal">
                        <i class="bi bi-plus-lg"></i> Nueva Escuela
                    </button>
                </div>
            </div>

            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle table-sm mb-0"> 
                            <thead class="table-light text-center">
                                <tr>
                                    <th class="text-start ps-3">Escuela</th>
                                    <th>Nivel</th>
                                    <th class="text-start">Dirección</th>
                                    {{-- Resumimos cupos visualmente para que entre en pantalla --}}
                                    <th>Matricula Total</th>
                                    <th class="text-end pe-3">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($clients as $client)
                                    <tr class="text-center">
                                        <td class="text-start ps-3 fw-bold">{{ $client->name }}</td>
                                        
                                        {{-- MOSTRAR EL NIVEL CON COLORES --}}
                                        <td>
                                            @if($client->level == 'jardin')
                                                <span class="badge bg-warning text-dark">Jardín</span>
                                            @elseif($client->level == 'primaria')
                                                <span class="badge bg-success">Primaria</span>
                                            @elseif($client->level == 'secundaria')
                                                <span class="badge bg-secondary">Secundaria</span>
                                            @else
                                                <span class="badge bg-light text-dark">{{ ucfirst($client->level) }}</span>
                                            @endif
                                        </td>

                                        <td class="text-start text-muted small">{{ $client->address ?? '---' }}</td>
                                        
                                        {{-- Suma simple de cupos para visualización rápida --}}
                                        <td>
                                            @php
                                                $totalAlumnos = $client->cupo_jardin + $client->cupo_primaria + $client->cupo_secundaria;
                                            @endphp
                                            @if($totalAlumnos > 0)
                                                <span class="badge bg-primary fs-6">{{ $totalAlumnos }}</span>
                                            @else
                                                <span class="text-muted small">-</span>
                                            @endif
                                        </td>
                                        
                                        <td class="text-end pe-3">
                                            {{-- BOTÓN EDITAR: Ahora lleva a la página completa --}}
                                            <a href="{{ route('clients.edit', $client->id) }}" class="btn btn-sm btn-outline-primary me-1" title="Editar Cupos y Precios">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            
                                            <form action="{{ route('clients.destroy', $client->id) }}" method="POST" class="d-inline" onsubmit="return confirm('¿Estás seguro de borrar la escuela {{ $client->name }}?');">
                                                @csrf @method('DELETE')
                                                <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="5" class="text-center py-4 text-muted">No hay escuelas cargadas.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            
            @if(method_exists($clients, 'links'))
                <div class="mt-4">
                    {{ $clients->links() }}
                </div>
            @endif

        </div>
    </div>
</div>

{{-- MODAL CREAR (Solo datos básicos) --}}
<div class="modal fade" id="createClientModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">Agregar Nueva Escuela</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('clients.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Nombre</label>
                        <input type="text" name="name" class="form-control" required placeholder="Ej: E.E.S N° 1">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Nivel Principal</label>
                        <select name="level" class="form-select" required>
                            <option value="primaria" selected>Primaria</option>
                            <option value="jardin">Jardín</option>
                            <option value="secundaria">Secundaria</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Dirección</label>
                        <input type="text" name="address" class="form-control" placeholder="Ej: Calle Falsa 123">
                    </div>
                    
                    <div class="alert alert-info small">
                        <i class="bi bi-info-circle"></i> Podrás cargar los <strong>Cupos</strong> y <strong>Valores ($)</strong> editando la escuela después de crearla.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar Escuela</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection