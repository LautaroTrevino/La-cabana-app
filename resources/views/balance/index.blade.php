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
        
        {{-- BOTÓN QUE ABRE EL MODAL (Ya no redirige) --}}
        <button type="button" class="btn btn-success fw-bold text-white shadow-sm" data-bs-toggle="modal" data-bs-target="#globalPricesModal">
            <i class="bi bi-cash-coin"></i> Ajustar Valores de Cupo
        </button>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light text-uppercase small">
                        <tr>
                            <th class="ps-4">Escuela</th>
                            <th class="text-center">Servicios</th>
                            <th class="text-end">Ingresos (Teórico)</th>
                            <th class="text-end">Costos (Real)</th>
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
                                    No hay movimientos registrados para calcular el balance.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                    @if(count($balanceData) > 0)
                    <tfoot>
                        <tr class="table-light fw-bold">
                            <td colspan="2" class="text-end text-uppercase">Totales Generales:</td>
                            <td class="text-end text-success">
                                $ {{ number_format(collect($balanceData)->sum('ingresos'), 2, ',', '.') }}
                            </td>
                            <td class="text-end text-danger">
                                $ {{ number_format(collect($balanceData)->sum('gastos'), 2, ',', '.') }}
                            </td>
                            <td class="text-end pe-4 fs-5">
                                @php $totalNeto = collect($balanceData)->sum('balance'); @endphp
                                <span class="{{ $totalNeto >= 0 ? 'text-success' : 'text-danger' }}">
                                    $ {{ number_format($totalNeto, 2, ',', '.') }}
                                </span>
                            </td>
                        </tr>
                    </tfoot>
                    @endif
                </table>
            </div>
        </div>
    </div>
</div>

{{-- MODAL DE EDICIÓN DE PRECIOS GLOBALES --}}
<div class="modal fade" id="globalPricesModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title fw-bold">
                    <i class="bi bi-cash-stack"></i> Configurar Valores Globales
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            
            {{-- Formulario apunta a la ruta de actualización --}}
            <form action="{{ route('clients.updateGlobalPrices') }}" method="POST">
                @csrf
                @method('PUT')
                
                <div class="modal-body bg-light">
                    <p class="small text-muted mb-4 border-bottom pb-2">
                        Al actualizar estos valores, se recalcularán los ingresos y la rentabilidad de todas las escuelas inmediatamente.
                    </p>
                    
                    <div class="mb-3 row align-items-center">
                        <label class="col-sm-6 col-form-label fw-bold text-success">Valor Comedor</label>
                        <div class="col-sm-6">
                            <div class="input-group">
                                <span class="input-group-text bg-white text-success fw-bold">$</span>
                                <input type="number" step="0.01" name="valor_comedor" class="form-control fw-bold text-end" value="{{ $precios->valor_comedor ?? 0 }}">
                            </div>
                        </div>
                    </div>

                    <div class="mb-3 row align-items-center">
                        <label class="col-sm-6 col-form-label fw-bold text-primary">Valor DMC</label>
                        <div class="col-sm-6">
                            <div class="input-group">
                                <span class="input-group-text bg-white text-primary fw-bold">$</span>
                                <input type="number" step="0.01" name="valor_dmc" class="form-control fw-bold text-end" value="{{ $precios->valor_dmc ?? 0 }}">
                            </div>
                        </div>
                    </div>

                    <div class="mb-0 row align-items-center">
                        <label class="col-sm-6 col-form-label fw-bold text-secondary">Valor Listo Consumo</label>
                        <div class="col-sm-6">
                            <div class="input-group">
                                <span class="input-group-text bg-white text-secondary fw-bold">$</span>
                                <input type="number" step="0.01" name="valor_lc" class="form-control fw-bold text-end" value="{{ $precios->valor_lc ?? 0 }}">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success fw-bold px-4">Actualizar y Recalcular</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection