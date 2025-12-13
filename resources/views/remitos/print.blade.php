<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Remito {{ $remito->numero_remito }}</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 14px; margin: 40px; }
        .header { display: flex; justify-content: space-between; margin-bottom: 30px; border-bottom: 2px solid #000; padding-bottom: 20px; }
        .company-name { font-size: 24px; font-weight: bold; text-transform: uppercase; }
        .remito-title { font-size: 20px; font-weight: bold; margin-top: 20px; text-align: center; background: #eee; padding: 5px; border: 1px solid #000; }
        .info-box { margin-bottom: 20px; }
        .info-box label { font-weight: bold; }
        
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #000; padding: 8px; text-align: left; }
        th { background-color: #f0f0f0; text-align: center; }
        
        .footer { margin-top: 50px; text-align: center; font-size: 12px; }
        .firma-box { margin-top: 80px; display: flex; justify-content: space-around; }
        .firma { border-top: 1px solid #000; padding-top: 5px; width: 200px; text-align: center; }

        @media print {
            .no-print { display: none; }
        }
    </style>
</head>
<body>

    <button onclick="window.print()" class="no-print" style="padding: 10px 20px; font-size: 16px; margin-bottom: 20px; cursor: pointer;">🖨️ Imprimir ahora</button>

    <div class="header">
        <div>
            <div class="company-name">LA CABAÑA</div>
            <div>PROVEEDURÍA INTEGRAL</div>
            <br>
            <div>Bolivar 5459, Mar del Plata</div>
            <div>Buenos Aires, 7600</div>
        </div>
        <div style="text-align: right;">
            <div><strong>REMITO "R"</strong></div>
            <div>N°: {{ $remito->numero_remito }}</div>
            <br>
            <div>Fecha: {{ \Carbon\Carbon::parse($remito->fecha)->format('d/m/Y') }}</div>
        </div>
    </div>

    <div class="info-box">
        <label>Cliente / Destinatario:</label>
        <div style="border: 1px solid #ccc; padding: 10px; margin-top: 5px;">
            {{ $remito->cliente }}
        </div>
    </div>

    <div class="remito-title">DETALLE DE ENTREGA</div>

    <table>
        <thead>
            <tr>
                <th>PRODUCTO</th>
                <th width="150">CANTIDAD</th>
            </tr>
        </thead>
        <tbody>
            @foreach($remito->details as $detail)
                <tr>
                    <td>{{ $detail->product->name }}</td>
                    <td style="text-align: center;">{{ $detail->quantity }}</td>
                </tr>
            @endforeach
            @for($i = 0; $i < (10 - count($remito->details)); $i++)
                <tr><td style="color: white;">.</td><td></td></tr>
            @endfor
        </tbody>
    </table>

    <div class="footer">
        <div class="firma-box">
            <div class="firma">Firma Entregó</div>
            <div class="firma">Firma Recibió</div>
        </div>
        <br><br>
        <p>Documento no válido como factura.</p>
    </div>

    <script>
        // Imprimir automáticamente al abrir
        window.onload = function() { window.print(); }
    </script>
</body>
</html>