@extends('layouts.app')

@section('content')
<div class="container py-4">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-0"><i class="bi bi-people-fill text-primary"></i> Usuarios del Sistema</h2>
            <p class="text-muted small mb-0">Gestioná los accesos y roles de cada usuario.</p>
        </div>
        <button class="btn btn-success fw-bold shadow-sm" data-bs-toggle="modal" data-bs-target="#modalNuevoUsuario">
            <i class="bi bi-person-plus"></i> Nuevo Usuario
        </button>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show shadow-sm border-0">
            <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show shadow-sm border-0">
            <i class="bi bi-x-circle-fill me-2"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- LEYENDA DE ROLES --}}
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100" style="border-left: 4px solid #198754 !important;">
                <div class="card-body py-2 px-3">
                    <span class="badge bg-success mb-1">Usuario</span>
                    <p class="small text-muted mb-0">Acceso solo a <strong>Productos</strong>. No puede gestionar datos del sistema.</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100" style="border-left: 4px solid #0d6efd !important;">
                <div class="card-body py-2 px-3">
                    <span class="badge bg-primary mb-1">Administrativo</span>
                    <p class="small text-muted mb-0">Acceso a todo <strong>excepto Usuarios</strong>. Eliminar requiere contraseña de admin.</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100" style="border-left: 4px solid #dc3545 !important;">
                <div class="card-body py-2 px-3">
                    <span class="badge bg-danger mb-1">Administrador</span>
                    <p class="small text-muted mb-0">Acceso <strong>completo</strong> al sistema incluyendo usuarios y todas las eliminaciones.</p>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">Nombre</th>
                            <th>Email</th>
                            <th class="text-center">Rol Actual</th>
                            <th class="text-center">Cambiar Rol</th>
                            <th>Registro</th>
                            <th class="text-end pe-4">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($users as $user)
                        <tr>
                            <td class="ps-4 fw-bold">
                                {{ $user->name }}
                                @if($user->id === Auth::id())
                                    <span class="badge bg-secondary ms-1 small">Vos</span>
                                @endif
                            </td>
                            <td class="text-muted">{{ $user->email }}</td>
                            <td class="text-center">
                                @if($user->role === 'admin')
                                    <span class="badge bg-danger px-3 py-2">Administrador</span>
                                @elseif($user->role === 'administrativo')
                                    <span class="badge bg-primary px-3 py-2">Administrativo</span>
                                @else
                                    <span class="badge bg-success px-3 py-2">Usuario</span>
                                @endif
                            </td>
                            <td class="text-center">
                                @if($user->id !== Auth::id())
                                    <form action="{{ route('admin.usuarios.rol', $user->id) }}" method="POST" class="d-flex gap-2 justify-content-center">
                                        @csrf
                                        <select name="role" class="form-select form-select-sm" style="width: auto;">
                                            <option value="usuario"       {{ $user->role === 'usuario'       ? 'selected' : '' }}>Usuario</option>
                                            <option value="administrativo"{{ $user->role === 'administrativo'? 'selected' : '' }}>Administrativo</option>
                                            <option value="admin"         {{ $user->role === 'admin'         ? 'selected' : '' }}>Administrador</option>
                                        </select>
                                        <button type="submit" class="btn btn-sm btn-outline-primary" title="Guardar rol">
                                            <i class="bi bi-check-lg"></i>
                                        </button>
                                    </form>
                                @else
                                    <span class="text-muted small fst-italic">Tu rol</span>
                                @endif
                            </td>
                            <td class="text-muted small">{{ $user->created_at->format('d/m/Y') }}</td>
                            <td class="text-end pe-4">
                                @if($user->id !== Auth::id())
                                    <form action="{{ route('admin.usuarios.destroy', $user->id) }}" method="POST" class="d-inline"
                                          onsubmit="return confirm('¿Eliminar al usuario {{ $user->name }}? Esta acción no se puede deshacer.')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Eliminar usuario">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                @else
                                    <span class="text-muted small fst-italic">Tu cuenta</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- MODAL NUEVO USUARIO --}}
<div class="modal fade" id="modalNuevoUsuario" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-success text-white border-0">
                <h5 class="modal-title fw-bold"><i class="bi bi-person-plus me-2"></i>Crear Nuevo Usuario</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.usuarios.store') }}" method="POST">
                @csrf
                <div class="modal-body px-4 py-3">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Nombre completo</label>
                        <input type="text" name="name" class="form-control" placeholder="Ej: Juan Pérez" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Email</label>
                        <input type="email" name="email" class="form-control" placeholder="correo@ejemplo.com" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Contraseña</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Confirmar contraseña</label>
                        <input type="password" name="password_confirmation" class="form-control" required>
                    </div>
                    <div class="mb-1">
                        <label class="form-label fw-bold">Rol</label>
                        <select name="role" class="form-select" required>
                            <option value="usuario">Usuario — solo Productos</option>
                            <option value="administrativo">Administrativo — todo excepto Usuarios</option>
                            <option value="admin">Administrador — acceso completo</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success fw-bold">
                        <i class="bi bi-person-check me-1"></i> Crear Usuario
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
