@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1>Historial de Movimientos</h1>
    <a href="{{ route('products.index') }}" class="btn btn-outline-secondary">Volver al Stock</a>
</div>

<div class="card card-body bg-light mb-4">
    <form action="{{ route('history.index') }}" method="GET" class="row g-3">
        <div class="col-md-3">
            <label class="form-label">Fecha</label>
            <input type="date" name="date" class="form-control" value="{{ request('date') }}">
        </div>
        <div class="col-md-3">
            <label class="form-label">Cliente</label>
            <input type="text" name="client" class="form-control" placeholder="Buscar cliente..." value="{{ request('client') }}">
        </div>
        <div class="col-md-3">
            <label class="form-label">Tipo</label>
            <select name="type" class="form-select">
                <option value="">Todos</option>
                <option value="entry" {{ request('type') == 'entry' ? 'selected' : '' }}>Entradas</option>
                <option value="exit" {{ request('type') == 'exit' ? 'selected' : '' }}>Salidas</option>
            </select>
        </div>
        <div class="col-md-3 d-flex align-items-end">
            <button type="submit" class="btn btn-primary w-100">Filtrar</button>
        </div>
    </form>
</div>

<div class="card shadow-sm">
    <div class="card-body p-0">
        <table class="table table-striped mb-0">
            <thead class="table-dark">
                <tr>
                    <th>Fecha / Hora</th>
                    <th>Producto</th>
                    <th>Tipo</th>
                    <th>Cantidad</th>
                    <th>Cliente / Detalle</th>
                </tr>
            </thead>
            <tbody>
                @forelse($movements as $mov)
                    <tr>
                        <td>{{ $mov->created_at->format('d/m/Y H:i') }}</td>
                        <td>
                            {{ $mov->product->name }} <br>
                            <small class="text-muted">{{ $mov->product->brand }}</small>
                        </td>
                        <td>
                            @if($mov->type == 'entry')
                                <span class="badge bg-success">Entrada</span>
                            @else
                                <span class="badge bg-danger">Salida</span>
                            @endif
                        </td>
                        <td class="fw-bold">
                            {{ $mov->quantity }} u.
                        </td>
                        <td>
                            @if($mov->client)
                                <i class="bi bi-person-fill"></i> {{ $mov->client }}
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center p-4">No se encontraron movimientos.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
<div class="mt-3">
    {{ $movements->links() }}
</div>
@endsection