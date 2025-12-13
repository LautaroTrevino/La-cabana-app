<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Remito {{ $remito->number }}</title>
    <style>
        /* Estilos generales para simular papel impreso */
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            color: #333;
            margin: 0;
            padding: 20px;
        }

        /* Contenedor principal tamaño A4 */
        .remito-container {
            max-width: 210mm;
            margin: 0 auto;
            border: 1px solid #fff; /* Borde invisible en pantalla */
        }

        /* 1. Encabezado (Logo y Datos Empresa) */
        .header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
            border-bottom: 1px solid #000;
            padding-bottom: 10px;
        }
        .company-logo {
            font-size: 28px;
            font-weight: bold;
            text-transform: uppercase;
            line-height: 1.2;
        }
        .company-subtitle {
            font-size: 14px;
            font-weight: normal;
            letter-spacing: 2px;
        }
        .company-details {
            text-align: right;
            font-size: 11px;
            line-height: 1.4;
        }

        /* 2. Bloque de Cliente y Envío */
        .addresses {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
            font-size: 11px;
        }
        .address-box {
            width: 48%;
        }
        .address-title {
            font-weight: bold;
            margin-bottom: 5px;
            display: block;
        }
        .address-content {
            color: #555;
            line-height: 1.4;
            text-transform: uppercase; /* Para que se vea como en la foto */
        }

        /* 3. Título del Remito */
        .remito-title {
            font-size: 22px;
            font-weight: bold;
            margin: 20px 0 5px 0;
            background-color: #e0e0e0; /* Fondo gris suave opcional */
            padding: 5px;
            border: 1px solid #999;
        }
        .remito-date {
            font-size: 11px;
            margin-bottom: 15px;
        }

        /* 4. Tabla de Productos */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        th {
            border: 1px solid #000;
            background-color: #f0f0f0;
            padding: 5px;
            text-align: left;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 11px;
        }
        td {
            border: 1px solid #000;
            padding: 5px;
            font-size: 12px;
            height: 20px; /* Altura mínima */
        }
        .col-prod { width: 70%; }
        .col-cant { width: 30%; }

        /* 5. Pie de Página (CAI) */
        .footer-cai {
            text-align: center;
            font-size: 10px;
            color: #777;
            margin-top: 50px;
            border-top: 1px solid #ccc;
            padding-top: 10px;
        }

        /* BOTONES (No se imprimen) */
        .no-print {
            padding: 10px;
            background: #f8f9fa;
            border-bottom: 1px solid #ddd;
            margin-bottom: 20px;
            text-align: right;
        }
        .btn {
            padding: 8px 15px;
            background: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            font-family: sans-serif;
            font-size: 14px;
            cursor: pointer;
            border: none;
        }
        .btn-back { background: #6c757d; }

        @media print {
            .no-print { display: none; }
            .remito-container { border: none; }
            body { padding: 0; }
        }
    </style>
</head>
<body>

    <div class="no-print">
        <a href="{{ route('remitos.index') }}" class="btn btn-back">Volver</a>
        <button onclick="window.print()" class="btn">IMPRIMIR</button>
    </div>

    <div class="remito-container">
        
        <div class="header">
            <div>
                <div class="company-logo">MI DEPÓSITO</div>
                <div class="company-subtitle">PROVEEDURÍA INTEGRAL</div>
            </div>
            <div class="company-details">
                <strong>de Tu Nombre / Empresa</strong><br>
                Calle Falsa 123, Mar del Plata - Buenos Aires<br>
                CUIT: 20-12345678-9<br>
                IIBB: 20-12345678-9<br>
                IVA - RESPONSABLE INSCRIPTO
            </div>
        </div>

        <div class="addresses">
            <div class="address-box">
                <span class="address-title">Dirección del Cliente:</span>
                <div class="address-content">
                    {{ $remito->client->name }}<br>
                    {{ $remito->client->address ?? 'SIN DIRECCIÓN REGISTRADA' }}<br>
                    CUIT: {{ $remito->client->cuit ?? '---' }}
                </div>
            </div>

            <div class="address-box">
                <span class="address-title">Dirección de Envío:</span>
                <div class="address-content">
                    {{ $remito->client->name }}<br>
                    {{ $remito->client->address ?? 'MISMOS DATOS' }}<br>
                    MAR DEL PLATA, BUENOS AIRES
                </div>
            </div>
        </div>

        <div class="remito-title">
            REMITO NRO {{ $remito->number }}
        </div>
        <div class="remito-date">
            Fecha de envío: <strong>{{ \Carbon\Carbon::parse($remito->date)->format('d/m/Y') }}</strong>
        </div>

        <table>
            <thead>
                <tr>
                    <th class="col-prod">PRODUCTO</th>
                    <th class="col-cant">ENTREGADO</th>
                </tr>
            </thead>
            <tbody>
                @foreach($remito->details as $detail)
                    <tr>
                        <td>{{ $detail->product->name }}</td>
                        
                        <td>{{ $detail->quantity }} Unidades</td>
                    </tr>
                @endforeach

                @for($i = 0; $i < (12 - $remito->details->count()); $i++)
                    <tr><td>&nbsp;</td><td>&nbsp;</td></tr>
                @endfor
            </tbody>
        </table>

        @if($remito->observation)
            <p style="font-size: 11px;"><strong>Observaciones:</strong> {{ $remito->observation }}</p>
        @endif

        <div class="footer-cai">
            C.A.I: 12345678901234 (Simulado)<br>
            FECHA VTO.: 01/01/2030
        </div>

    </div>

</body>
</html>