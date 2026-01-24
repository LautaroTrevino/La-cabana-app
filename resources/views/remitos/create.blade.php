@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold text-gray-800">
            <i class="bi bi-file-earmark-text text-primary"></i> Generar Nuevo Remito
        </h2>
        <a href="{{ route('remitos.index') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Volver
        </a>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="card shadow border-0">
                <div class="card-header bg-primary text-white fw-bold py-3">
                    Configuración del Envío
                </div>
                <div class="card-body bg-light">
                    
                    <form action="{{ route('remitos.store') }}" method="POST">
                        @csrf

                        {{-- 1. SELECCIÓN DE ESCUELA --}}
                        <div class="row mb-4">
                            <div class="col-md-8">
                                <label class="form-label fw-bold">1. Seleccionar Escuela (Destino)</label>
                                <select name="client_id" class="form-select form-select-lg shadow-sm" required>
                                    <option value="">Elegir escuela...</option>
                                    @foreach($clients as $client)
                                        <option value="{{ $client->id }}">
                                            {{ $client->name }} 
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold">2. Fecha de Entrega</label>
                                <input type="date" name="date" class="form-control form-control-lg" value="{{ date('Y-m-d') }}" required>
                            </div>
                        </div>

                        <hr class="my-4">

                        {{-- 2. SELECCIÓN DE MENÚS (AGRUPADOS) --}}
                        <div class="mb-4">
                            <label class="form-label fw-bold mb-3 fs-5">3. Seleccionar Menús a Preparar</label>
                            
                            @if($menus->isEmpty())
                                <div class="alert alert-warning">
                                    No hay menús cargados. Ve a la sección "Menús" para crear los ciclos (Lunes 1, Martes 2, etc).
                                </div>
                            @else
                                <div class="row g-3">
                                    {{-- AGRUPAMOS AUTOMÁTICAMENTE POR TIPO DE MENÚ --}}
                                    @foreach($menus->groupBy('type') as $tipo => $menusDelTipo)
                                        <div class="col-md-6">
                                            <div class="card h-100 border-0 shadow-sm">
                                                <div class="card-header fw-bold text-uppercase bg-dark text-white text-center">
                                                    {{ $tipo }}
                                                </div>
                                                <div class="card-body p-0">
                                                    <div class="list-group list-group-flush">
                                                        @foreach($menusDelTipo as $menu)
                                                            <label class="list-group-item list-group-item-action d-flex gap-3 align-items-center cursor-pointer">
                                                                <input class="form-check-input flex-shrink-0" type="checkbox" name="menus[]" value="{{ $menu->id }}" style="transform: scale(1.3);">
                                                                <span class="pt-1 form-checked-content w-100">
                                                                    <strong class="d-block">{{ $menu->name }}</strong>
                                                                    {{-- Muestra ingredientes resumidos si quieres --}}
                                                                    <small class="text-muted" style="font-size: 0.8em;">
                                                                        {{ $menu->ingredients->count() }} ingrediente(s)
                                                                    </small>
                                                                </span>
                                                            </label>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                            
                            <div class="form-text mt-3">
                                <i class="bi bi-info-circle"></i> Marca los menús correspondientes al día de la fecha. El sistema usará el cupo adecuado (Comedor, DMC, etc.) automáticamente.
                            </div>
                        </div>

                        <div class="d-grid pt-3">
                            <button type="submit" class="btn btn-success btn-lg fw-bold shadow py-3">
                                <i class="bi bi-calculator"></i> CALCULAR Y GENERAR REMITO
                            </button>
                        </div>

                    </form>

                </div>
            </div>
        </div>
    </div>
</div>
@endsection