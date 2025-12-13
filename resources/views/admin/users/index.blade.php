@extends('layouts.app')

@section('header')
    <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
        {{ __('Gestión de Usuarios') }}
    </h2>
@endsection

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="container-fluid"> 
            
            <div class="row mb-4 align-items-center">
                <div class="col-md-8">
                    <h3 class="mb-0 text-gray-800 dark:text-gray-200">Usuarios del Sistema</h3>
                </div>
                {{-- Botón para futuro: Crear Usuario --}}
                <div class="col-md-4 text-end">
                    <button class="btn btn-secondary" disabled>
                        <i class="bi bi-person-plus"></i> Nuevo Usuario (Pronto)
                    </button>
                </div>
            </div>

            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0"> 
                            <thead class="table-light">
                                <tr>
                                    <th>Nombre</th>
                                    <th>Email</th>
                                    <th>Rol</th>
                                    <th>Fecha Registro</th>
                                    <th class="text-end">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($users as $user)
                                    <tr>
                                        <td class="fw-bold">{{ $user->name }}</td>
                                        <td>{{ $user->email }}</td>
                                        <td>
                                            @if($user->role === 'admin')
                                                <span class="badge bg-danger">ADMINISTRADOR</span>
                                            @else
                                                <span class="badge bg-primary">USUARIO</span>
                                            @endif
                                        </td>
                                        <td>{{ $user->created_at->format('d/m/Y') }}</td>
                                        <td class="text-end">
                                            {{-- Evitamos que te borres a ti mismo --}}
                                            @if(Auth::id() !== $user->id)
                                                <button class="btn btn-sm btn-outline-danger" onclick="alert('Función de borrar pendiente')">
                                                    <i class="bi bi-trash"></i>
                                                </button>
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
    </div>
</div>
@endsection