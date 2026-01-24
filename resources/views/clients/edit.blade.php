@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="row mb-4 align-items-center">
        <div class="col-md-8">
            <h2 class="fw-bold text-gray-800">
                <i class="bi bi-pencil-square text-primary"></i> Editando Escuela
            </h2>
            <p class="text-muted small mb-0">Define los cupos (cantidades) habilitados. Los precios se configuran globalmente.</p>
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
            {{-- COLUMNA IZQUIERDA: DATOS DE CONTACTO --}}
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
                            <label class="form-label small">Dirección</label>
                            <input type="text" name="address" class="form-control" value="{{ old('address', $client->address) }}">
                        </div>
                        <div class="mb-3">
                            <label class="form-label small">CUIT / ID</label>
                            <input type="text" name="cuit" class="form-control" value="{{ old('cuit', $client->cuit) }}">
                        </div>
                        <div class="mb-3">
                            <label class="form-label small">Teléfono</label>
                            <input type="text" name="phone" class="form-control" value="{{ old('phone', $client->phone) }}">
                        </div>
                        <div class="mb-3">
                            <label class="form-label small">Email</label>
                            <input type="email" name="email" class="form-control" value="{{ old('email', $client->email) }}">
                        </div>
                    </div>
                </div>
            </div>

            {{-- COLUMNA DERECHA: CONFIGURACIÓN DE CUPOS --}}
            <div class="col-lg-8">
                <div class="card shadow border-0 mb-4">
                    <div class="card-header bg-primary text-white fw-bold py-3">
                        <i class="bi bi-people-fill"></i> Cupos Habilitados (Cantidades)
                    </div>
                    <div class="card-body bg-light">
                        
                        <div class="alert alert-info border-0 shadow-sm d-flex align-items-center mb-4">
                            <i class="bi bi-info-circle-fill fs-4 me-3"></i>
                            <div>
                                <strong>Importante:</strong> Aquí solo se cargan las cantidades de alumnos.
                                <br>Los valores monetarios ($) se configuran una única vez en la sección "Balance".
                            </div>
                        </div>

                        <div class="row g-4">
                            
                            {{-- GRUPO 1: COMEDOR --}}
                            <div class="col-12 border-bottom pb-3">
                                <h6 class="text-success fw-bold mb-3"><i class="bi bi-egg-fried"></i> Servicio Comedor</h6>
                                <div class="row">
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold small">Cupo Estándar</label>
                                        <input type="number" name="quota_comedor" class="form-control border-success" value="{{ old('quota_comedor', $client->quota_comedor ?? 0) }}">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold small">Cupo Alternativo</label>
                                        <input type="number" name="quota_comedor_alt" class="form-control border-success" value="{{ old('quota_comedor_alt', $client->quota_comedor_alt ?? 0) }}">
                                        <div class="form-text small">Sin Sal, Celíacos, etc.</div>
                                    </div>
                                </div>
                            </div>

                            {{-- GRUPO 2: DMC --}}
                            <div class="col-12 border-bottom pb-3">
                                <h6 class="text-primary fw-bold mb-3"><i class="bi bi-cup-hot"></i> Servicio DMC</h6>
                                <div class="row">
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold small">Cupo Estándar</label>
                                        <input type="number" name="quota_dmc" class="form-control border-primary" value="{{ old('quota_dmc', $client->quota_dmc ?? 0) }}">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold small">Cupo Alternativo</label>
                                        <input type="number" name="quota_dmc_alt" class="form-control border-primary" value="{{ old('quota_dmc_alt', $client->quota_dmc_alt ?? 0) }}">
                                    </div>
                                </div>
                            </div>

                            {{-- GRUPO 3: OTROS --}}
                            <div class="col-12">
                                <h6 class="text-secondary fw-bold mb-3"><i class="bi bi-box-seam"></i> Otros Servicios</h6>
                                <div class="row">
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold small">Listo Consumo (LCB)</label>
                                        <input type="number" name="quota_lcb" class="form-control" value="{{ old('quota_lcb', $client->quota_lcb ?? 0) }}">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold small text-danger">Maternal</label>
                                        <input type="number" name="quota_maternal" class="form-control border-danger" value="{{ old('quota_maternal', $client->quota_maternal ?? 0) }}">
                                    </div>
                                </div>
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