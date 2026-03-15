@extends('layouts.app')

@section('content')
<div class="container py-4">
    
    {{-- Botones de Acción (No salen en la impresión) --}}
    <div class="d-flex justify-content-between mb-4 no-print">
        <a href="{{ route('remitos.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Volver al listado
        </a>
        <div>
            <button onclick="window.print();" class="btn btn-dark me-2">
                <i class="bi bi-printer"></i> Imprimir Rápido
            </button>
            <a href="{{ route('remitos.print', $remito->id) }}" target="_blank" class="btn btn-primary fw-bold shadow">
                <i class="bi bi-file-pdf"></i> Descargar PDF Oficial
            </a>
        </div>
    </div>

    {{-- LA HOJA DE PAPEL --}}
    <div class="card shadow-lg border-0 print-area">
        {{-- Cabecera --}}
        <div class="card-header bg-white py-4 border-bottom-2">
            <div class="row align-items-center">
                <div class="col-6">
                    <h2 class="mb-0 fw-bold text-uppercase">Remito</h2>
                    <p class="text-muted mb-0 fs-5">Nº {{ $remito->number }}</p>
                </div>
                <div class="col-6 text-end">
                    <h4 class="mb-0 fw-bold">LA CABAÑA</h4>
                    <small class="text-muted d-block">Control de Stock y Distribución</small>
                    <small class="fw-bold">Fecha: {{ \Carbon\Carbon::parse($remito->date)->format('d/m/Y') }}</small>
                </div>
            </div>
        </div>
        
        <div class="card-body p-5">
            {{-- Datos del Cliente --}}
            <div class="row mb-5">
                <div class="col-md-6">
                    <h6 class="text-uppercase text-muted fw-bold small ls-1">Destinatario</h6>
                    <div class="p-3 border rounded bg-light-subtle">
                        <h4 class="mb-1 text-primary fw-bold">{{ $remito->client->name }}</h4>
                        <p class="mb-0"><i class="bi bi-geo-alt-fill text-danger"></i> {{ $remito->client->address ?? 'Dirección no registrada' }}</p>
                    </div>
                </div>
                <div class="col-md-6">
                    <h6 class="text-uppercase text-muted fw-bold small ls-1">Detalle del Pedido</h6>
                    <div class="p-3 border rounded bg-light-subtle h-100">
                        <p class="mb-0 fst-italic text-muted">
                            {{ $remito->observation ?: 'Entrega de mercadería según menús planificados.' }}
                        </p>
                    </div>
                </div>
            </div>

            {{-- Tabla --}}
            <h6 class="text-uppercase text-muted fw-bold small ls-1 mb-3">Ítems a Entregar</h6>
            <table class="table table-bordered border-dark align-middle">
                <thead class="bg-light text-center">
                    <tr>
                        <th style="width: 50%;">Descripción / Ingrediente</th>
                        <th style="width: 20%;">Cantidad</th>
                        <th style="width: 30%;">Referencia</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($remito->items as $item)
                        <tr>
                            <td class="ps-3 fw-bold">{{ $item->name }}</td>
                            <td class="text-center fs-5">
                                {{ $item->formatted_quantity }}
                            </td>
                            <td class="text-muted small ps-3">{{ $item->observation }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="text-center py-4 text-muted">No hay ítems registrados.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            {{-- Firmas (Solo visible al imprimir o en pantalla grande) --}}
            <div class="row mt-5 pt-5 signature-section">
                <div class="col-6 text-center">
                    <div class="border-top border-dark w-75 mx-auto pt-2">Firma Entregó</div>
                </div>
                <div class="col-6 text-center">
                    <div class="border-top border-dark w-75 mx-auto pt-2">Firma Recibió (Conforme)</div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    /* ESTILOS PARA IMPRESIÓN LIMPIA */
    @media print {
        /* Ocultar TODO lo que no sea el área de impresión */
        body * {
            visibility: hidden;
        }
        .print-area, .print-area * {
            visibility: visible;
        }
        
        /* Posicionar la hoja en la esquina superior izquierda */
        .print-area {
            position: absolute;
            left: 0;
            top: 0;
            width: 100%;
            margin: 0;
            padding: 0;
            box-shadow: none !important;
            border: none !important;
        }

        /* Ocultar elementos específicos de la interfaz de Laravel */
        .no-print, nav, header, footer, .sidebar {
            display: none !important;
        }

        /* Ajustes de tabla para tinta */
        .table {
            border-color: #000 !important;
        }
        .bg-light-subtle {
            background-color: #f8f9fa !important; /* Forzar gris claro en impresión */
            -webkit-print-color-adjust: exact;
        }
    }
</style>
@endsection