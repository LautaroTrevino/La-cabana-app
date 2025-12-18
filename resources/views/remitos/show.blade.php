@extends('layouts.app')

@section('content')
<div class="container py-4">
    
    {{-- Botón Volver --}}
    <a href="{{ route('remitos.index') }}" class="btn btn-outline-secondary mb-4 no-print">
        <i class="bi bi-arrow-left"></i> Volver al listado
    </a>

    <div class="card shadow-sm border-0">
        {{-- Cabecera del Comprobante --}}
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <div>
                <h4 class="mb-0 text-primary">Comprobante: {{ $remito->number }}</h4>
                <small class="text-muted">Generado el: {{ \Carbon\Carbon::parse($remito->date)->format('d/m/Y') }}</small>
            </div>
            <div class="no-print">
                {{-- Badge de Tipo de Operación --}}
                @if($remito->tipo == 'entrega')
                    <span class="badge bg-dark p-2">
                        <i class="bi bi-truck"></i> ENTREGA REAL (Depósito)
                    </span>
                @else
                    <span class="badge bg-primary p-2">
                        <i class="bi bi-file-earmark-text"></i> REMITO ADMINISTRATIVO (Menú)
                    </span>
                @endif
                
                {{-- Badge de Estado --}}
                <span class="badge {{ $remito->status == 'active' ? 'bg-success' : 'bg-danger' }} p-2 ms-2">
                    {{ strtoupper($remito->status == 'active' ? 'VIGENTE' : 'ANULADO') }}
                </span>
            </div>
        </div>
        
        <div class="card-body p-4">
            <div class="row mb-4">
                {{-- Datos del Cliente / Escuela --}}
                <div class="col-md-6">
                    <h6 class="text-muted text-uppercase small fw-bold mb-3">Datos del Destinatario</h6>
                    <div class="p-3 bg-light rounded border">
                        <h5 class="mb-1">{{ $remito->client->name }}</h5>
                        <span class="badge bg-secondary mb-2">{{ strtoupper($remito->client->level) }}</span>
                        <p class="mb-1 text-muted small">
                            <i class="bi bi-geo-alt"></i> {{ $remito->client->address ?? 'Sin dirección registrada' }}
                        </p>
                    </div>
                </div>

                {{-- Observaciones --}}
                <div class="col-md-6">
                    <h6 class="text-muted text-uppercase small fw-bold mb-3">Observaciones</h6>
                    <div class="p-3 bg-light rounded border h-100">
                        <p class="mb-0 small text-muted">
                            {{ $remito->observation ?: 'Sin observaciones adicionales para este documento.' }}
                        </p>
                    </div>
                </div>
            </div>

            {{-- Tabla de Mercadería --}}
            <h6 class="text-muted text-uppercase small fw-bold mb-3">Detalle de Mercadería</h6>
            <div class="table-responsive">
                <table class="table table-hover border">
                    <thead class="table-light">
                        <tr>
                            <th>Artículo / Ingrediente</th>
                            <th class="text-center" style="width: 200px;">Cantidad Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($remito->details as $detail)
                            <tr>
                                <td>
                                    @if($detail->ingredient_id)
                                        {{-- Si es un ingrediente de menú --}}
                                        <div class="fw-bold">{{ $detail->ingredient->name }}</div>
                                        <small class="text-muted">{{ $detail->ingredient->description ?? 'Ingrediente de receta' }}</small>
                                    @else
                                        {{-- Si es un producto de stock --}}
                                        <div class="fw-bold">{{ $detail->product->name }}</div>
                                        <small class="text-muted">{{ $detail->product->brand }} | {{ $detail->product->presentation }}</small>
                                    @endif
                                </td>
                                <td class="text-center fs-5 fw-bold">
                                    {{ number_format($detail->quantity, 3, ',', '.') }}
                                    <small class="text-muted fs-6">un/kg</small>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        
        {{-- Acciones Finales --}}
        <div class="card-footer bg-white py-3 text-end no-print">
            <button onclick="window.print();" class="btn btn-outline-dark me-2">
                <i class="bi bi-printer"></i> Imprimir Pantalla
            </button>
            @if(Route::has('remitos.print'))
            <a href="{{ route('remitos.print', $remito->id) }}" target="_blank" class="btn btn-primary px-4">
                <i class="bi bi-file-pdf"></i> Generar PDF Oficial
            </a>
            @endif
        </div>
    </div>
</div>

<style>
    @media print {
        .no-print, .btn, .sidebar, .navbar, .card-footer {
            display: none !important;
        }
        .card {
            border: none !important;
            box-shadow: none !important;
        }
        .container {
            width: 100% !important;
            max-width: 100% !important;
            margin: 0 !important;
            padding: 0 !important;
        }
    }
</style>
@endsection