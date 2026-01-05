@extends('layouts.app')

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg pb-10">
            
            {{-- Encabezado Distintivo para Depósito --}}
            <div class="border-b border-gray-200 bg-gray-800 px-6 py-4">
                <h2 class="text-xl font-bold text-white">
                    Nueva Orden de Entrega (Depósito)
                </h2>
                <p class="text-xs text-gray-400 mt-1">Esta acción descontará mercadería del stock real.</p>
            </div>

            <div class="p-6">
                {{-- CORRECCIÓN IMPORTANTE: La ruta apunta a deposito.store --}}
                <form action="{{ route('deposito.store') }}" method="POST" id="entrega-form">
                    @csrf

                    <div class="mb-6 grid grid-cols-1 gap-6 md:grid-cols-4">
                        <div class="md:col-span-3">
                            <label for="client_id" class="block text-sm font-medium text-gray-700">Cliente / Destino</label>
                            <select name="client_id" id="client_id" required
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                <option value="">-- Seleccionar --</option>
                                @foreach($clients as $client)
                                    <option value="{{ $client->id }}">{{ $client->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="md:col-span-1">
                            <label for="date" class="block text-sm font-medium text-gray-700">Fecha</label>
                            <input type="date" name="date" id="date" value="{{ date('Y-m-d') }}" required
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        </div>
                    </div>

                    <hr class="my-6 border-gray-100">

                    <div class="mb-6 grid grid-cols-1 gap-4 md:grid-cols-2 bg-gray-50 p-4 rounded-lg">
                        
                        <div>
                            <label for="barcode_scanner" class="block text-sm font-bold text-gray-700 mb-1">Opción A: Escáner</label>
                            <div class="relative rounded-md shadow-sm">
                                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                                    <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M5 20h14a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                </div>
                                <input type="text" id="barcode_scanner" 
                                    class="block w-full rounded-md border-gray-300 pl-10 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" 
                                    placeholder="Haga clic y escanee..." autofocus>
                            </div>
                        </div>

                        <div>
                            <label for="manual_select" class="block text-sm font-bold text-gray-700 mb-1">Opción B: Búsqueda Manual</label>
                            <div class="flex gap-2">
                                <select id="manual_select" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                    <option value="">-- Buscar producto (Stock) --</option>
                                    @foreach($products as $p)
                                        <option value="{{ $p->id }}">{{ $p->name }} (Stock: {{ $p->stock ?? '-' }})</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="mb-6 overflow-hidden border border-gray-200 shadow-sm sm:rounded-lg">
                        <table class="min-w-full divide-y divide-gray-200" id="items-table">
                            <thead class="bg-gray-800 text-white">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Producto</th>
                                    <th scope="col" class="w-32 px-6 py-3 text-center text-xs font-medium uppercase tracking-wider">Cantidad</th>
                                    <th scope="col" class="w-20 px-6 py-3 text-right text-xs font-medium uppercase tracking-wider"></th>
                                </tr>
                            </thead>
                            <tbody id="table-body" class="divide-y divide-gray-200 bg-white">
                            </tbody>
                        </table>
                        
                        <div id="empty-state" class="flex flex-col items-center justify-center py-12 text-gray-400">
                            <span class="text-sm">La lista está vacía. Escanee o seleccione productos.</span>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-6 md:grid-cols-3 items-end">
                        <div class="md:col-span-2">
                            <label for="observation" class="block text-sm font-medium text-gray-700">Observaciones (Opcional)</label>
                            <textarea name="observation" id="observation" rows="3"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"></textarea>
                        </div>
                        <div class="flex justify-end pb-2">
                            <button type="submit"
                                class="w-auto inline-flex items-center justify-center rounded-md border border-transparent bg-gray-800 p-2.5 text-base font-medium text-white shadow-sm hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 mb-2">
                                Confirmar Entrega (Descontar Stock)
                            </button>
                        </div>
                    </div>

                </form>
            </div>
        </div>
    </div>
</div>

<script>
    // Datos pasados desde el controlador
    const products = @json($products);
    let itemIndex = 0;

    const scannerInput = document.getElementById('barcode_scanner');
    const tableBody = document.getElementById('table-body');
    const emptyState = document.getElementById('empty-state');
    const manualSelect = document.getElementById('manual_select');

    function updateEmptyState() {
        emptyState.style.display = tableBody.children.length === 0 ? 'flex' : 'none';
    }

    // Función simplificada para agregar filas
    function addOrUpdateRow(item) {
        const identifier = item.id;
        
        // 1. Buscar si ya existe la fila por ID
        const existingRow = Array.from(tableBody.querySelectorAll('.item-row'))
            .find(row => row.dataset.id == identifier);

        if (existingRow) {
            // 2. Si existe, sumar 1 a la cantidad
            const qtyInput = existingRow.querySelector('.qty-input');
            let currentQty = parseInt(qtyInput.value) || 0;
            qtyInput.value = currentQty + 1;
            
            // Efecto visual
            existingRow.classList.add('bg-indigo-50');
            setTimeout(() => existingRow.classList.remove('bg-indigo-50'), 300);
        } else {
            // 3. Si no existe, crear fila nueva
            const row = document.createElement('tr');
            row.className = 'item-row hover:bg-gray-50';
            row.dataset.id = identifier; 
            
            // Input oculto
            const inputName = `items[${itemIndex}][product_id]`;
            const valueId = item.id;

            row.innerHTML = `
                <td class="px-6 py-4">
                    <div class="text-sm font-medium text-gray-900">${item.name}</div>
                    <div class="text-xs text-gray-500">Stock Actual: ${item.stock ?? 0}</div>
                    <input type="hidden" name="${inputName}" value="${valueId}">
                </td>
                <td class="px-6 py-4">
                    <input type="number" name="items[${itemIndex}][quantity]" 
                        class="qty-input block w-full rounded-md border-gray-300 text-center text-sm focus:border-indigo-500 focus:ring-indigo-500" 
                        value="1" min="1" required>
                </td>
                <td class="px-6 py-4 text-right">
                    <button type="button" class="remove-item text-gray-400 hover:text-red-600 p-2">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                    </button>
                </td>
            `;
            tableBody.appendChild(row);
            itemIndex++;
        }
        updateEmptyState();
    }

    // --- EVENTOS ---

    // 1. Escáner
    scannerInput.addEventListener('keydown', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault(); 
            const code = this.value.trim();
            if (!code) return;

            // Buscar en el array de productos
            let found = products.find(p => p.package_code == code || p.id == code);

            if (found) {
                addOrUpdateRow(found);
            } else {
                alert('Producto no encontrado en depósito: ' + code);
            }
            this.value = ''; 
        }
    });

    // 2. Selección Manual (Automático al cambiar)
    manualSelect.addEventListener('change', function() {
        const selectedId = this.value;
        if (!selectedId) return;

        // Buscar producto por ID
        const found = products.find(p => p.id == selectedId);

        if (found) {
            addOrUpdateRow(found);
            this.value = ""; // Resetear select
        }
    });

    // 3. Eliminar Fila
    tableBody.addEventListener('click', function(e) {
        if (e.target.closest('.remove-item')) {
            e.target.closest('tr').remove();
            updateEmptyState();
        }
    });
</script>
@endsection