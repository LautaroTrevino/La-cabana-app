@extends('layouts.app')

{{-- 1. HEADER: Título en la barra superior blanca (si tu layout lo soporta) --}}
@section('header')
    <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
        {{ __('Gestión de Escuelas') }}
    </h2>
@endsection

@section('content')
{{-- 2. WRAPPER: Espaciado para que encaje con el diseño general --}}
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="container-fluid"> 

            {{-- Encabezado y Botón Agregar --}}
            <div class="row mb-4 align-items-center">
                <div class="col-md-8">
                    {{-- Si el header de arriba no se muestra, este título sirve de respaldo --}}
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
                                    <th class="text-start">Dirección</th>
                                    <th>DMC</th>
                                    <th>DMC Alt.</th>
                                    <th>Comedor</th>
                                    <th>Com. Alt.</th>
                                    <th>Listo</th>
                                    <th>Maternal</th>
                                    <th class="text-end pe-3">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                {{-- Usamos forelse para manejar el caso de lista vacía --}}
                                @forelse($clients as $client)
                                    <tr class="text-center">
                                        <td class="text-start ps-3 fw-bold">{{ $client->name }}</td>
                                        <td class="text-start text-muted small">{{ $client->address ?? '---' }}</td>
                                        
                                        <td>
                                            @if($client->quota_dmc > 0)
                                                <span class="badge bg-info text-dark">{{ $client->quota_dmc }}</span>
                                            @else
                                                <span class="text-muted text-opacity-25">-</span>
                                            @endif
                                        </td>

                                        <td>
                                            @if($client->quota_dmc_alt > 0)
                                                <span class="badge bg-warning text-dark">{{ $client->quota_dmc_alt }}</span>
                                            @else
                                                <span class="text-muted text-opacity-25">-</span>
                                            @endif
                                        </td>

                                        <td>
                                            @if($client->quota_comedor > 0)
                                                <span class="badge bg-success">{{ $client->quota_comedor }}</span>
                                            @else
                                                <span class="text-muted text-opacity-25">-</span>
                                            @endif
                                        </td>

                                        <td>
                                            @if($client->quota_comedor_alt > 0)
                                                <span class="badge bg-warning text-dark">{{ $client->quota_comedor_alt }}</span>
                                            @else
                                                <span class="text-muted text-opacity-25">-</span>
                                            @endif
                                        </td>

                                        <td>
                                            @if($client->quota_listo > 0)
                                                <span class="badge bg-primary">{{ $client->quota_listo }}</span>
                                            @else
                                                <span class="text-muted text-opacity-25">-</span>
                                            @endif
                                        </td>

                                        <td>
                                            @if($client->quota_maternal > 0)
                                                <span class="badge bg-secondary">{{ $client->quota_maternal }}</span>
                                            @else
                                                <span class="text-muted text-opacity-25">-</span>
                                            @endif
                                        </td>
                                        
                                        <td class="text-end pe-3">
                                            {{-- IMPORTANTE: json_encode evita errores con comillas en los nombres --}}
                                            <button class="btn btn-sm btn-outline-primary me-1" 
                                                    onclick="editClient({{ json_encode($client) }})">
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                            
                                            <form action="{{ route('clients.destroy', $client->id) }}" method="POST" class="d-inline" onsubmit="return confirm('¿Estás seguro de borrar la escuela {{ $client->name }}?');">
                                                @csrf @method('DELETE')
                                                <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="9" class="text-center py-4 text-muted">No hay escuelas cargadas.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            
            {{-- Paginación (opcional, solo si la usas en el controlador) --}}
            @if(method_exists($clients, 'links'))
                <div class="mt-4">
                    {{ $clients->links() }}
                </div>
            @endif

        </div>
    </div>
</div>

{{-- MODAL CREAR --}}
<div class="modal fade" id="createClientModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">Agregar Nueva Escuela</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('clients.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Nombre de la Escuela</label>
                            <input type="text" name="name" class="form-control" required placeholder="Ej: E.E.S N° 1">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Dirección</label>
                            <input type="text" name="address" class="form-control" placeholder="Ej: Calle Falsa 123">
                        </div>
                    </div>

                    <h6 class="border-bottom pb-2 mb-3 text-primary">Configuración de Cupos</h6>
                    
                    {{-- Campos de Cupos --}}
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Cupo DMC</label>
                            <input type="number" name="quota_dmc" class="form-control" min="0" value="0">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Cupo DMC Alt.</label>
                            <input type="number" name="quota_dmc_alt" class="form-control" min="0" value="0">
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Cupo Comedor</label>
                            <input type="number" name="quota_comedor" class="form-control" min="0" value="0">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Cupo Comedor Alt.</label>
                            <input type="number" name="quota_comedor_alt" class="form-control" min="0" value="0">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <label class="form-label">Cupo Listo</label>
                            <input type="number" name="quota_listo" class="form-control" min="0" value="0">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Cupo Maternal</label>
                            <input type="number" name="quota_maternal" class="form-control" min="0" value="0">
                        </div>
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

{{-- MODAL EDITAR --}}
<div class="modal fade" id="editClientModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title">Editar Escuela</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="editClientForm" action="" method="POST">
                @csrf @method('PUT')
                <div class="modal-body">
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Nombre de la Escuela</label>
                            <input type="text" name="name" id="editName" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Dirección</label>
                            <input type="text" name="address" id="editAddress" class="form-control">
                        </div>
                    </div>

                    <h6 class="border-bottom pb-2 mb-3 text-primary">Configuración de Cupos</h6>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Cupo DMC</label>
                            <input type="number" name="quota_dmc" id="editDmc" class="form-control" min="0">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Cupo DMC Alt.</label>
                            <input type="number" name="quota_dmc_alt" id="editDmcAlt" class="form-control" min="0">
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Cupo Comedor</label>
                            <input type="number" name="quota_comedor" id="editComedor" class="form-control" min="0">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Cupo Comedor Alt.</label>
                            <input type="number" name="quota_comedor_alt" id="editComedorAlt" class="form-control" min="0">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <label class="form-label">Cupo Listo</label>
                            <input type="number" name="quota_listo" id="editListo" class="form-control" min="0">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Cupo Maternal</label>
                            <input type="number" name="quota_maternal" id="editMaternal" class="form-control" min="0">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Actualizar Cambios</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function editClient(client) {
        // Configuramos la acción del formulario dinámicamente
        document.getElementById('editClientForm').action = '/clients/' + client.id;

        // Rellenamos los campos
        document.getElementById('editName').value = client.name;
        document.getElementById('editAddress').value = client.address ? client.address : '';

        document.getElementById('editDmc').value = client.quota_dmc;
        document.getElementById('editDmcAlt').value = client.quota_dmc_alt;
        document.getElementById('editComedor').value = client.quota_comedor;
        document.getElementById('editComedorAlt').value = client.quota_comedor_alt;
        document.getElementById('editListo').value = client.quota_listo;
        document.getElementById('editMaternal').value = client.quota_maternal;

        // Abrimos el modal (Bootstrap 5)
        var myModal = new bootstrap.Modal(document.getElementById('editClientModal'));
        myModal.show();
    }
</script>
@endsection