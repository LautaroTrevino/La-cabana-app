@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold text-gray-800">
                <i class="bi bi-graph-up-arrow text-primary"></i> Balance Financiero
            </h2>
            <p class="text-muted mb-0">Rentabilidad calculada según precios globales y costos reales.</p>
        </div>
        
        {{-- BOTÓN QUE ABRE EL MODAL DE PRECIOS --}}
        <button type="button" class="btn btn-warning fw-bold text-dark shadow-sm" data-bs-toggle="modal" data-bs-target="#globalPricesModal">
            <i class="bi bi-currency-dollar"></i> Configurar Valores de Cupo
        </button>
    </div>

    {{-- TOTALES GENERALES --}}
    <div class="row mb-4 g-3">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm bg-success text-white h-100">
                <div class="card-body">
                    <h6 class="text-uppercase small opacity-75">Ingresos Totales (Teórico)</h6>
                    <h3 class="fw-bold mb-0">$ {{ number_format($totalIngresosPeriodo, 2, ',', '.') }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm bg-danger text-white h-100">
                <div class="card-body">
                    <h6 class="text-uppercase small opacity-75">Gastos Totales (Real)</h6>
                    <h3 class="fw-bold mb-0">$ {{ number_format($totalGastosPeriodo, 2, ',', '.') }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm bg-primary text-white h-100">
                <div class="card-body">
                    <h6 class="text-uppercase small opacity-75">Rentabilidad Neta</h6>
                    <h3 class="fw-bold mb-0">$ {{ number_format($totalIngresosPeriodo - $totalGastosPeriodo, 2, ',', '.') }}</h3>
                </div>
            </div>
        </div>
    </div>

    {{-- TABLA DE BALANCE --}}
    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light text-uppercase small">
                        <tr>
                            <th class="ps-4">Escuela</th>
                            <th class="text-center">Días Servicio</th>
                            <th class="text-end">Ingresos</th>
                            <th class="text-end">Costos</th>
                            <th class="text-end pe-4">Rentabilidad</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($balanceData as $row)
                            <tr>
                                <td class="ps-4 fw-bold">{{ $row['cliente'] }}</td>
                                <td class="text-center">
                                    <span class="badge bg-light text-dark border">{{ $row['servicios'] }}</span>
                                </td>
                                <td class="text-end text-success font-monospace">
                                    $ {{ number_format($row['ingresos'], 2, ',', '.') }}
                                </td>
                                <td class="text-end text-danger font-monospace">
                                    $ {{ number_format($row['gastos'], 2, ',', '.') }}
                                </td>
                                <td class="text-end pe-4 fw-bold fs-5">
                                    @if($row['balance'] >= 0)
                                        <span class="text-success bg-success bg-opacity-10 px-2 py-1 rounded">
                                            <i class="bi bi-caret-up-fill small"></i>
                                            $ {{ number_format($row['balance'], 2, ',', '.') }}
                                        </span>
                                    @else
                                        <span class="text-danger bg-danger bg-opacity-10 px-2 py-1 rounded">
                                            <i class="bi bi-caret-down-fill small"></i>
                                            $ {{ number_format($row['balance'], 2, ',', '.') }}
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-5 text-muted">
                                    <i class="bi bi-clipboard-data fs-1 mb-3 d-block opacity-25"></i>
                                    No hay movimientos registrados en este periodo.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- MODAL DE EDICIÓN DE PRECIOS GLOBALES (LOS 6 PRECIOS) --}}
<div class="modal fade" id="globalPricesModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title fw-bold">
                    <i class="bi bi-cash-stack"></i> Configurar Valores Globales
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            
            {{-- Formulario apunta al controlador de Balance --}}
            <form action="{{ route('balance.updatePrices') }}" method="POST">
                @csrf
                <div class="modal-body bg-light">
                    <div class="row g-3">
                        
                        {{-- GRUPO COMEDOR --}}
                        <div class="col-12"><h6 class="fw-bold text-success border-bottom pb-2">Servicio Comedor</h6></div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Estándar ($)</label>
                            <input type="number" step="0.01" name="valor_comedor" class="form-control" value="{{ $precios->valor_comedor ?? 0 }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Alternativo ($)</label>
                            <input type="number" step="0.01" name="valor_comedor_alt" class="form-control" value="{{ $precios->valor_comedor_alt ?? 0 }}">
                        </div>

                        {{-- GRUPO DMC --}}
                        <div class="col-12 mt-4"><h6 class="fw-bold text-primary border-bottom pb-2">Servicio DMC</h6></div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Estándar ($)</label>
                            <input type="number" step="0.01" name="valor_dmc" class="form-control" value="{{ $precios->valor_dmc ?? 0 }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Alternativo ($)</label>
                            <input type="number" step="0.01" name="valor_dmc_alt" class="form-control" value="{{ $precios->valor_dmc_alt ?? 0 }}">
                        </div>

                        {{-- OTROS --}}
                        <div class="col-12 mt-4"><h6 class="fw-bold text-secondary border-bottom pb-2">Otros Servicios</h6></div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Listo Consumo ($)</label>
                            <input type="number" step="0.01" name="valor_lc" class="form-control" value="{{ $precios->valor_lc ?? 0 }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-danger">Maternal ($)</label>
                            <input type="number" step="0.01" name="valor_maternal" class="form-control" value="{{ $precios->valor_maternal ?? 0 }}">
                        </div>

                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary fw-bold px-4">Guardar y Recalcular</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection