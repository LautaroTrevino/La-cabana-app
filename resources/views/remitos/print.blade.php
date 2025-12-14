<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Remito N° {{ $remito->numero_remito }}</title>
    <style>
        /* CONFIGURACIÓN DEL PAPEL: TAMAÑO CARTA */
        @page {
            size: letter;
            margin: 10mm;
        }
        
        /* ESTILOS GENERALES PARA IMPRESIÓN Y VISTA PREVIA */
        body {
            font-family: Arial, sans-serif;
            font-size: 11px;
            color: #000;
            margin: 0;
            padding: 0;
            line-height: 1.3;
            background-color: #f0f0f0;
        }

        /* Contenedor principal tamaño Carta (simulación en pantalla) */
        .remito-container {
            /* Dimensiones de papel Carta (8.5in x 11in) */
            width: 216mm;
            min-height: 279mm;
            margin: 20px auto; /* Centra la hoja en la pantalla */
            border: 1px solid #ccc;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            background: white;
            padding: 30px 40px;
        }

        /* 1. ENCABEZADO Y DATOS DE EMPRESA */
        .header-main {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 25px;
        }
        .logo-box { line-height: 1.1; }
        .company-logo {
            font-size: 28px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .company-subtitle {
            font-size: 14px;
            font-weight: normal;
            letter-spacing: 2px;
        }
        .company-details {
            text-align: right;
            font-size: 10px;
        }
        .company-details strong { font-size: 11px; }

        /* 2. BLOQUE DE CLIENTE Y DIRECCIONES */
        /* Eliminamos el flex para permitir que floten y se vea el espacio central */
        .info-client-row {
            margin-bottom: 5px;
            text-align: center; /* Alineamos las cajas al centro del contenedor padre */
        }
        .info-client-box {
            /* Hacemos que flote a la izquierda (la primera) y a la derecha (la segunda) */
            width: 45%; 
            line-height: 1.3;
            padding-bottom: 10px;
            display: inline-block; /* Permite que se vean una al lado de la otra */
            text-align: left; /* Restablecemos la alineación de texto interna */
        }
        .info-client-box:first-child {
            float: left;
        }
        .info-client-box:last-child {
            float: right;
        }
        
        .info-title {
            font-size: 10px;
            font-weight: bold;
            display: block;
            margin-bottom: 4px;
        }
        .info-content {
            font-size: 11px;
            text-transform: uppercase;
            white-space: pre-wrap;
        }

        /* 3. BLOQUE DE REMITO NRO */
        .remito-block {
            /* Clearfix para que el remito NRO no suba por el float */
            clear: both;
            border: 1px solid #000;
            padding: 5px 10px;
            margin-top: 15px;
            margin-bottom: 15px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .remito-nro {
            font-size: 16px;
            font-weight: bold;
        }
        .remito-date {
            font-size: 11px;
        }
        
        /* 4. TABLA DE PRODUCTOS */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        th, td {
            border: 1px solid #000;
            padding: 5px;
            text-align: left;
            font-size: 11px;
        }
        th {
            background-color: #f0f0f0;
            font-weight: bold;
            text-transform: uppercase;
            text-align: center;
        }
        .col-prod { width: 70%; }
        .col-cant { width: 30%; text-align: right; }
        
        /* 5. FOOTER CAI */
        .footer-cai {
            text-align: center;
            font-size: 10px;
            margin-top: 50px;
            border-top: 1px solid #000;
            padding-top: 10px;
            display: block; /* Aseguramos que ocupe todo el ancho para centrar el texto */
        }

        /* Ocultar elementos para la impresión */
        .no-print { 
            position: sticky; 
            top: 0;
            z-index: 100;
            text-align: right; 
            padding: 10px;
            background: #f0f0f0;
        }
        
        /* Eliminamos bordes y fondos al imprimir */
        @media print {
            .no-print { display: none; }
            body { margin: 0; padding: 0; background: white; }
            .remito-container {
                width: 100%;
                border: none;
                box-shadow: none;
                margin: 0;
                padding: 0;
                min-height: auto;
            }
        }
    </style>
</head>
<body>

    {{-- BOTONES NO IMPRIMIBLES --}}
    <div class="no-print">
        <a href="{{ route('remitos.index') }}" class="btn btn-back" style="background: #6c757d; color: white; padding: 5px 10px; text-decoration: none; margin-right: 10px;">Volver</a>
        <button onclick="window.print()" class="btn" style="background: #007bff; color: white; padding: 5px 10px; border: none; cursor: pointer;">IMPRIMIR</button>
    </div>

    <div class="remito-container">
        
        {{-- ENCABEZADO DE LA COMPAÑÍA --}}
        <div class="header-main">
            <div class="logo-box">
                <div class="company-logo">LA CABAÑA</div>
                <div class="company-subtitle">PROVEEDURÍA INTEGRAL</div>
            </div>
            <div class="company-details">
                de Treviño Luis Alberto<br>
                Bolivar 5459, Mar del Plata - Buenos Aires 7600<br>
                CUIT: 20-12201833-9<br>
                IIBB: 20-12201833-9<br>
                INIC. ACT: 02/2010<br><br>
                <strong>IVA - RESPONSABLE INSCRIPTO</strong>
            </div>
        </div>

        {{-- BLOQUE DE CLIENTE Y DIRECCIONES (Corregido para centrado visual) --}}
        <div class="info-client-row">
            <div class="info-client-box">
                <span class="info-title">Dirección del Cliente:</span>
                <div class="info-content">
                    {{ $remito->cliente }}<br>
                    DIRECCION GENERAL DE<br>
                    CULTURA Y EDUCACION DE<br>
                    LA PROVINCIA DE BS AS<br>
                    CALLE X Y Z (Datos fijos, o buscar en la tabla clients)<br>
                    LA PLATA 1900, Buenos Aires.<br>
                    Argentina<br>
                    CUIT: 30-627393713
                </div>
            </div>

            <div class="info-client-box">
                <span class="info-title">Dirección de Envío:</span>
                <div class="info-content">
                    DIRECCION GENERAL DE CULTURA Y<br>
                    EDUCACION DE LA PROVINCIA DE BS AS<br>
                    PPSB<br>
                    CALLE 13/E 56 Y 57<br>
                    LA PLATA 1900, Buenos Aires, Argentina
                </div>
            </div>
        </div>

        {{-- REMITO NRO Y FECHA --}}
        <div class="remito-block">
            <div class="remito-date">
                Fecha de envío:<br>
                <strong>{{ \Carbon\Carbon::parse($remito->fecha)->format('d/m/Y') }} {{ \Carbon\Carbon::parse($remito->created_at)->format('H:i') }}</strong>
            </div>
            <div class="remito-nro">
                REMITO NRO 0001-{{ $remito->numero_remito }}
            </div>
            {{-- Espacio vacío para balancear el diseño --}}
            <div style="width: 30%;"></div>
            <div style="text-align: right; font-weight: bold; width: 25%;">
                PRODUCTO<br>
                <div style="margin-top: 5px;">ENTREGADO</div>
            </div>
        </div>

        {{-- TABLA DE PRODUCTOS --}}
        <table>
            <thead>
                <tr>
                    <th class="col-prod" style="text-align: left;">PRODUCTO</th>
                    <th class="col-cant" style="text-align: right;">ENTREGADO</th>
                </tr>
            </thead>
            <tbody>
                @foreach($remito->details as $detail)
                    <tr>
                        <td class="col-prod">{{ $detail->product->name }}</td>
                        <td class="col-cant">{{ $detail->quantity }} Unidades</td>
                    </tr>
                @endforeach
                
                {{-- Relleno para que la tabla sea visualmente larga (10 filas) --}}
                @for($i = 0; $i < (10 - $remito->details->count()); $i++)
                    <tr><td style="height: 20px;"></td><td></td></tr>
                @endfor
            </tbody>
        </table>

        {{-- PIE DE PÁGINA CAI --}}
        <div class="footer-cai">
            C.A.I: 5110521136218<br>
            FECHA VTO.: 05/03/2026
        </div>

    </div>
</body>
</html>