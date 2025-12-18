<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        @page { 
            size: letter; /* Tamaño Carta */
            margin: 0.8cm; 
        }
        body { font-family: 'Helvetica', sans-serif; font-size: 11px; color: #333; }
        
        /* Encabezado Principal */
        .header { width: 100%; border-bottom: 2px solid #000; padding-bottom: 10px; margin-bottom: 15px; }
        .empresa-nombre { font-size: 24px; font-weight: bold; margin: 0; }
        
        /* Contenedor de Direcciones en una sola fila */
        .tabla-direcciones { width: 100%; margin-bottom: 20px; border-collapse: collapse; }
        .tabla-direcciones td { width: 50%; vertical-align: top; border: none; padding: 0; }
        .caja-datos { 
            border: 1px solid #000; 
            padding: 10px; 
            margin: 5px; 
            min-height: 85px; 
        }
        .titulo-caja { font-weight: bold; font-size: 10px; text-transform: uppercase; margin-bottom: 4px; display: block; }

        /* Título del Remito */
        .remito-titulo { 
            text-align: center; font-size: 16px; font-weight: bold; 
            border: 2px solid #000; padding: 5px; margin: 15px 0; 
        }

        /* Tabla de Productos */
        .tabla-items { width: 100%; border-collapse: collapse; }
        .tabla-items th { background-color: #f2f2f2; border: 1px solid #000; padding: 8px; text-align: left; font-size: 10px; }
        .tabla-items td { border: 1px solid #000; padding: 8px; vertical-align: middle; }
        
        .texto-descripcion { font-size: 9px; color: #555; }

        /* Pie de página */
        .footer-cai { position: fixed; bottom: 0; width: 100%; text-align: center; font-size: 9px; padding-top: 10px; }
    </style>
</head>
<body>
    {{-- LOGO Y DATOS DE EMPRESA --}}
    <div class="header">
        <table style="width:100%; border:none;">
            <tr>
                <td style="border:none;">
                    <h1 class="empresa-nombre">LA CABAÑA</h1>
                    <small>PROVEEDURIA INTEGRAL</small>
                </td>
                <td style="border:none; text-align: right; font-size: 10px;">
                    de Treviño Luis Alberto<br>
                    Bolivar 5459, Mar del Plata<br>
                    CUIT: 20-12201833-5 | IIBB: 20-12201833-5<br>
                    IVA RESPONSABLE INSCRIPTO
                </td>
            </tr>
        </table>
    </div>

    {{-- FILA DE DIRECCIONES (IZQUIERDA Y DERECHA) --}}
    <table class="tabla-direcciones">
        <tr>
            <td>
                <div class="caja-datos" style="margin-right: 10px;">
                    <span class="titulo-caja">Dirección del Cliente:</span>
                    <strong>{{ $remito->client->name }}</strong><br>
                    {{ $remito->client->address ?? 'Sin dirección' }}<br>
                    CUIT: {{ $remito->client->cuit ?? 'N/A' }}
                </div>
            </td>
            <td>
                <div class="caja-datos" style="margin-left: 10px;">
                    <span class="titulo-caja">Dirección de Envío:</span>
                    <strong>DIRECCION GENERAL DE CULTURA Y EDUCACION</strong><br>
                    ESTABLECIMIENTO: {{ $remito->client->name }}<br>
                    {{ $remito->client->location ?? 'Provincia de Buenos Aires' }}
                </div>
            </td>
        </tr>
    </table>

    <div class="remito-titulo">REMITO NRO {{ $remito->number }}</div>

    <p style="margin-bottom: 10px;"><strong>Fecha de envío:</strong> {{ \Carbon\Carbon::parse($remito->date)->format('d/m/Y') }}</p>

    {{-- TABLA DE INGREDIENTES CON DESCRIPCION A LA DERECHA --}}
    <table class="tabla-items">
        <thead>
            <tr>
                <th style="width: 30%;">INGREDIENTE / ARTÍCULO</th>
                <th style="width: 45%;">DESCRIPCIÓN</th>
                <th style="width: 25%; text-align: center;">CANTIDAD ENTREGADA</th>
            </tr>
        </thead>
        <tbody>
            @foreach($remito->details as $detail)
            <tr>
                <td>
                    @if($detail->ingredient)
                        <strong>{{ $detail->ingredient->name }}</strong>
                    @else
                        <strong>{{ $detail->product->name ?? 'N/A' }}</strong>
                    @endif
                </td>
                <td class="texto-descripcion">
                    @if($detail->ingredient)
                        {{ $detail->ingredient->description ?? '-' }}
                    @else
                        {{ $detail->product->brand ?? '' }} {{ $detail->product->presentation ?? '' }}
                    @endif
                </td>
                <td style="text-align: center;">
                    <span style="font-size: 12px; font-weight: bold;">{{ number_format($detail->quantity, 2, ',', '.') }}</span> 
                    <span style="font-size: 9px;">Un/Kg</span>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer-cai">
        C.A.I: 51105211826128 | FECHA VTO: 05/03/2026<br>
        Documento no válido como factura.
    </div>
</body>
</html>