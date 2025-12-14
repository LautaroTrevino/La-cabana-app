@extends('layouts.app')

@section('header')
    <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
        Detalle del Remito: {{ $remito->numero_remito }}
    </h2>
@endsection

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="container-fluid">
            
            <a href="{{ route('remitos.index') }}" class="btn btn-secondary mb-3">
                <i class="bi bi-arrow-left"></i> Volver al listado
            </a>

            <div class="card shadow-sm">
                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                    <div>
                        {{-- CORREGIDO: Usamos la columna 'cliente' que es un string --}}
                        <h5 class="mb-0">Cliente/Escuela: <strong>{{ $remito->cliente }}</strong></h5>
                        <small class="text-muted">Fecha: {{ \Carbon\Carbon::parse($remito->fecha)->format('d/m/Y') }}</small>
                    </div>
                    <div>
                        <span class="badge {{ $remito->estado == 'pendiente' ? 'bg-warning text-dark' : 'bg-success' }}">
                            {{ strtoupper($remito->estado) }}
                        </span>
                    </div>
                </div>
                
                <div class="card-body">
                    {{-- Bloque de Dirección (Usamos el nombre de la escuela como referencia) --}}
                    <div class="mb-4 p-3 bg-light border rounded">
                        <h6 class="text-primary mb-1">Información del Destino</h6>
                        <p class="mb-0 small">
                            **Destino:** {{ $remito->cliente }}<br>
                            **Dirección:** (La dirección no está guardada en el remito, solo el nombre)<br>
                            **Notas:** Remito generado el {{ $remito->created_at->format('d/m/Y H:i') }}.
                        </p>
                    </div>


                    <h6 class="card-title text-decoration-underline mb-3">Productos Incluidos:</h6>
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>Producto</th>
                                <th class="text-end" style="width: 150px;">Cantidad</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($remito->details as $detail)
                                <tr>
                                    <td>{{ $detail->product->name }}</td>
                                    <td class="text-end fw-bold">{{ $detail->quantity }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                
                <div class="card-footer text-end">
                    <a href="{{ route('remitos.print', $remito->id) }}" target="_blank" class="btn btn-primary">
                        <i class="bi bi-printer"></i> Imprimir Comprobante
                    </a>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection