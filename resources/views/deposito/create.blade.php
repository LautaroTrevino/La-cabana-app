@extends('layouts.app')

@section('content')
<div class="container py-4" x-data="scannerApp()">
    
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold text-gray-800">
            <i class="bi bi-upc-scan text-primary"></i> Salida de Mercadería (Depósito)
        </h2>
        <a href="{{ route('products.index') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Volver
        </a>
    </div>

    <div class="row g-4">
        <div class="col-md-4">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-primary text-white fw-bold py-3">
                    <i class="bi bi-geo-alt-fill"></i> Datos de Entrega
                </div>
                <div class="card-body bg-light">
                    
                    <div class="mb-4">
                        <label class="fw-bold form-label">Escuela / Destino</label>
                        <select class="form-select form-select-lg" x-model="form.client_id">
                            <option value="">Seleccionar Escuela...</option>
                            @foreach($clients as $client)
                                <option value="{{ $client->id }}">{{ $client->name }}</option>
                            @endforeach
                        </select>
                        <div class="form-text small">¿A dónde va la mercadería?</div>
                    </div>

                    <div class="mb-4">
                        <label class="fw-bold form-label">Fecha de Entrega</label>
                        <input type="date" class="form-control" x-model="form.date">
                    </div>

                    <div class="mb-3">
                        <label class="fw-bold form-label">Observaciones</label>
                        <textarea class="form-control" rows="4" x-model="form.observation" 
                                  placeholder="Ej: Refuerzo solicitado por la directora..."></textarea>
                    </div>
                </div>
                <div class="card-footer bg-white border-0 p-3">
                    <button class="btn btn-success w-100 py-3 fw-bold fs-5 shadow-sm" 
                            @click="submitOrder" 
                            :disabled="cart.length === 0 || !form.client_id">
                        <i class="bi bi-check-lg"></i> CONFIRMAR SALIDA
                    </button>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-dark text-white p-3">
                    <label class="form-label mb-1 fw-bold"><i class="bi bi-barcode-scan"></i> Escanear Producto</label>
                    <div class="input-group input-group-lg">
                        <span class="input-group-text bg-white text-dark"><i class="bi bi-search"></i></span>
                        <input type="text" 
                               id="scannerInput" 
                               class="form-control" 
                               placeholder="Haz clic y escanea el código..." 
                               list="productList" 
                               @change="addProduct($event.target.value)"
                               autocomplete="off">
                        
                        <datalist id="productList">
                            @foreach($products as $prod)
                                <option value="{{ $prod->code }}">{{ $prod->name }}</option>
                            @endforeach
                        </datalist>
                        
                        <button class="btn btn-secondary" type="button" @click="document.getElementById('scannerInput').value = ''; document.getElementById('scannerInput').focus();">
                            Limpiar
                        </button>
                    </div>
                </div>

                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover mb-0 align-middle">
                            <thead class="table-light text-uppercase small">
                                <tr>
                                    <th class="ps-4">Producto</th>
                                    <th class="text-center" style="width: 150px;">Cantidad</th>
                                    <th class="text-end pe-4">Acción</th>
                                </tr>
                            </thead>
                            <tbody>
                                <template x-for="(item, index) in cart" :key="index">
                                    <tr>
                                        <td class="ps-4 fw-bold text-dark" x-text="item.name"></td>
                                        <td class="text-center">
                                            <input type="number" step="0.01" class="form-control text-center fw-bold text-primary" x-model="item.qty">
                                        </td>
                                        <td class="text-end pe-4">
                                            <button class="btn btn-outline-danger btn-sm" @click="remove(index)" title="Quitar">
                                                <i class="bi bi-trash-fill"></i>
                                            </button>
                                        </td>
                                    </tr>
                                </template>
                                
                                <tr x-show="cart.length === 0">
                                    <td colspan="3" class="text-center py-5 text-muted">
                                        <div class="mb-2"><i class="bi bi-box-arrow-right fs-1 opacity-25"></i></div>
                                        <p class="mb-0">Lista vacía.</p>
                                        <small>Escanea un código de barras o busca por nombre.</small>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer bg-light text-end small text-muted">
                    Total de ítems: <span x-text="cart.length" class="fw-bold"></span>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function scannerApp() {
    return {
        form: {
            client_id: '',
            date: new Date().toISOString().slice(0, 10),
            observation: ''
        },
        cart: [],
        // Mapeamos los productos para que JS los entienda
        products: @json($products->map(fn($p) => ['id' => $p->id, 'code' => $p->code, 'name' => $p->name])), 

        addProduct(val) {
            if (!val) return;

            // Buscamos por código O por nombre
            let product = this.products.find(p => p.code == val || p.name == val);

            if (product) {
                // Si ya está en la lista, sumamos 1
                let existing = this.cart.find(c => c.id == product.id);
                if (existing) {
                    existing.qty = parseFloat(existing.qty) + 1;
                } else {
                    // Si no, lo agregamos
                    this.cart.unshift({ 
                        id: product.id, 
                        name: product.name, 
                        qty: 1 
                    });
                }
                
                // Limpiamos el input y devolvemos el foco para el siguiente escaneo
                let input = document.getElementById('scannerInput');
                input.value = ''; 
                input.focus();
            } else {
                alert('Producto no encontrado: ' + val);
                document.getElementById('scannerInput').value = '';
            }
        },

        remove(index) {
            this.cart.splice(index, 1);
        },

        submitOrder() {
            if(!this.form.client_id) {
                alert('⚠️ Por favor, selecciona una Escuela.');
                return;
            }

            if(!confirm('¿Confirmar salida? Se descontará del stock inmediatamente.')) return;

            let payload = {
                client_id: this.form.client_id,
                date: this.form.date,
                observation: this.form.observation,
                items: this.cart,
                _token: '{{ csrf_token() }}'
            };

            fetch('{{ route("ordenes.storeReal") }}', {
                method: 'POST',
                headers: { 
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify(payload)
            })
            .then(res => res.json())
            .then(data => {
                if(data.success) {
                    alert('✅ ENTREGA REGISTRADA EXITOSAMENTE');
                    // Reseteamos el formulario
                    this.cart = [];
                    this.form.client_id = '';
                    this.form.observation = '';
                } else {
                    alert('❌ Error: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error de conexión con el servidor.');
            });
        }
    }
}
</script>
@endsection