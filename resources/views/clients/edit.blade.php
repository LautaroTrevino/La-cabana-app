@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="row mb-4 align-items-center">
        <div class="col-md-8">
            <h2 class="fw-bold text-gray-800">
                <i class="bi bi-pencil-square text-primary"></i> Editando Escuela
            </h2>
            <p class="text-muted small mb-0">Configura los cupos habilitados por servicio para el cálculo de facturación.</p>
        </div>
        <div class="col-md-4 text-end">
            <a href="{{ route('clients.index') }}" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Volver
            </a>
        </div>
    </div>

    <form action="{{ route('clients.update', $client->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="row">
            {{-- COLUMNA IZQUIERDA: DATOS BÁSICOS --}}
            <div class="col-lg-4">
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-white fw-bold py-3">
                        <i class="bi bi-building"></i> Datos Institucionales
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Nombre Escuela</label>
                            <input type="text" name="name" class="form-control" value="{{ old('name', $client->name) }}" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Dirección</label>
                            <input type="text" name="address" class="form-control" value="{{ old('address', $client->address) }}">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">CUIT / Identificación</label>
                            <input type="text" name="cuit" class="form-control" value="{{ old('cuit', $client->cuit) }}">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Teléfono</label>
                            <input type="text" name="phone" class="form-control" value="{{ old('phone', $client->phone) }}">
                        </div>
                    </div>
                </div>
            </div>

            {{-- COLUMNA DERECHA: CUPOS --}}
            <div class="col-lg-8">
                
                {{-- SECCIÓN PRINCIPAL: CUPOS POR SERVICIO (FACTURACIÓN/BALANCE) --}}
                <div class="card shadow border-0 mb-4">
                    <div class="card-header bg-primary text-white fw-bold py-3 d-flex justify-content-between">
                        <span><i class="bi bi-receipt"></i> Cupos por Servicio (Facturación)</span>
                        <span class="badge bg-white text-primary">Prioridad Balance</span>
                    </div>
                    <div class="card-body bg-light">
                        <div class="row g-3">
                            {{-- GRUPO COMEDOR --}}
                            <div class="col-md-6">
                                <label class="form-label fw-bold text-success">Cupo Comedor</label>
                                <input type="number" name="quota_comedor" class="form-control border-success" value="{{ old('quota_comedor', $client->quota_comedor ?? 0) }}">
                                <div class="form-text small">Menú principal de almuerzo.</div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold text-success">Cupo Comedor Alt.</label>
                                <input type="number" name="quota_comedor_alt" class="form-control border-success" value="{{ old('quota_comedor_alt', $client->quota_comedor_alt ?? 0) }}">
                                <div class="form-text small">Menú alternativo (celíacos, sin sal, etc).</div>
                            </div>

                            <hr class="text-muted my-2">

                            {{-- GRUPO DMC --}}
                            <div class="col-md-4">
                                <label class="form-label fw-bold text-primary">Cupo DMC</label>
                                <input type="number" name="quota_dmc" class="form-control border-primary" value="{{ old('quota_dmc', $client->quota_dmc ?? 0) }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold text-primary">Cupo DMC Alt.</label>
                                <input type="number" name="quota_dmc_alt" class="form-control border-primary" value="{{ old('quota_dmc_alt', $client->quota_dmc_alt ?? 0) }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold text-primary">Cupo Maternal</label>
                                <input type="number" name="quota_maternal" class="form-control border-primary" value="{{ old('quota_maternal', $client->quota_maternal ?? 0) }}">
                            </div>

                            <hr class="text-muted my-2">

                            {{-- OTROS --}}
                            <div class="col-md-6">
                                <label class="form-label fw-bold text-secondary">Cupo Listo Consumo</label>
                                <input type="number" name="quota_listo" class="form-control" value="{{ old('quota_listo', $client->quota_listo ?? 0) }}">
                            </div>
                        </div>
                    </div>
                </div>

                {{-- SECCIÓN SECUNDARIA: CUPOS OPERATIVOS (INGREDIENTES) --}}
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-secondary text-white fw-bold py-2">
                        <i class="bi bi-box-seam"></i> Cupos Operativos (Cocina)
                    </div>
                    <div class="card-body">
                        <p class="small text-muted mb-2">Estos valores se usan si tus recetas calculan ingredientes por nivel educativo.</p>
                        <div class="row g-2">
                            <div class="col-4">
                                <label class="small fw-bold">Jardín</label>
                                <input type="number" name="cupo_jardin" class="form-control form-control-sm" value="{{ old('cupo_jardin', $client->cupo_jardin ?? 0) }}">
                            </div>
                            <div class="col-4">
                                <label class="small fw-bold">Primaria</label>
                                <input type="number" name="cupo_primaria" class="form-control form-control-sm" value="{{ old('cupo_primaria', $client->cupo_primaria ?? 0) }}">
                            </div>
                            <div class="col-4">
                                <label class="small fw-bold">Secundaria</label>
                                <input type="number" name="cupo_secundaria" class="form-control form-control-sm" value="{{ old('cupo_secundaria', $client->cupo_secundaria ?? 0) }}">
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <div class="d-grid gap-2 d-md-flex justify-content-md-end mb-5">
            <button type="submit" class="btn btn-primary btn-lg fw-bold px-5 shadow">
                <i class="bi bi-save"></i> Guardar Cambios
            </button>
        </div>
    </form>
</div>
@endsection