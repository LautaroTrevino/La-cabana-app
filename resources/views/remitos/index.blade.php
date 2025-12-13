@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Historial de Remitos</h1>
        <a href="{{ route('remitos.create') }}" class="btn btn-primary">
            <i class="bi bi-file-earmark-plus"></i> Nuevo Remito
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="card shadow-sm">
        <div class="card-body">
            <table class="table table-striped align-middle">
                <thead>
                    <tr>
                        <th>N° Remito</th>
                        <th>Fecha</th>
                        <th>Cliente</th>
                        <th>Cant. Items</th>
                        <th class="text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($remitos as $remito)
                        <tr>
                            <td>{{ $remito->number }}</td>
                            <td>{{ \Carbon\Carbon::parse($remito->date)->format('d/m/Y') }}</td>
                            <td>{{ $remito->client->name }}</td>
                            <td>{{ $remito->details->count() }} productos</td>
                            <td class="text-end">
                                <a href="{{ route('remitos.show', $remito->id) }}" class="btn btn-sm btn-outline-dark" target="_blank">
                                    <i class="bi bi-printer"></i> Imprimir
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center">No hay remitos generados.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection