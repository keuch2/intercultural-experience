<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Factura {{ $invoice->invoice_number }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            line-height: 1.6;
            color: #333;
        }
        .header {
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 3px solid #667eea;
        }
        .company-info {
            float: left;
            width: 50%;
        }
        .invoice-info {
            float: right;
            width: 45%;
            text-align: right;
        }
        .clear {
            clear: both;
        }
        h1 {
            color: #667eea;
            margin: 0;
            font-size: 28px;
        }
        h2 {
            color: #667eea;
            font-size: 18px;
            margin-top: 20px;
            margin-bottom: 10px;
        }
        .client-info {
            background: #f8f9fa;
            padding: 15px;
            margin: 20px 0;
            border-left: 4px solid #667eea;
        }
        .concept-box {
            margin: 20px 0;
            padding: 15px;
            background: #f8f9fa;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        table th {
            background: #667eea;
            color: white;
            padding: 10px;
            text-align: left;
        }
        table td {
            padding: 10px;
            border-bottom: 1px solid #ddd;
        }
        .text-right {
            text-align: right;
        }
        .total-row {
            font-size: 16px;
            font-weight: bold;
            background: #f8f9fa;
        }
        .notes {
            margin-top: 30px;
            padding: 15px;
            background: #fff3cd;
            border-left: 4px solid #ffc107;
        }
        .footer {
            margin-top: 50px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
            text-align: center;
            font-size: 10px;
            color: #666;
        }
        .status-badge {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 3px;
            font-size: 11px;
            font-weight: bold;
        }
        .status-paid {
            background: #28a745;
            color: white;
        }
        .status-issued {
            background: #ffc107;
            color: #000;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <div class="company-info">
            <h1>Intercultural Experience</h1>
            <p>
                Asunción, Paraguay<br>
                Email: admin@interculturalexperience.com<br>
                Tel: +595 21 123 4567<br>
                RUC: 80012345-6
            </p>
        </div>
        <div class="invoice-info">
            <h1>FACTURA</h1>
            <p>
                <strong>Número:</strong> {{ $invoice->invoice_number }}<br>
                <strong>Fecha:</strong> {{ $invoice->issue_date->format('d/m/Y') }}<br>
                @if($invoice->due_date)
                    <strong>Vencimiento:</strong> {{ $invoice->due_date->format('d/m/Y') }}<br>
                @endif
                <span class="status-badge status-{{ $invoice->status }}">{{ $invoice->status_label }}</span>
            </p>
        </div>
        <div class="clear"></div>
    </div>

    <!-- Cliente -->
    <h2>FACTURAR A:</h2>
    <div class="client-info">
        <strong>{{ $invoice->billing_name }}</strong><br>
        {{ $invoice->billing_email }}<br>
        @if($invoice->billing_tax_id)
            RUC/Tax ID: {{ $invoice->billing_tax_id }}<br>
        @endif
        @if($invoice->billing_address)
            {{ $invoice->billing_address }}<br>
        @endif
        @if($invoice->billing_city || $invoice->billing_country)
            {{ $invoice->billing_city }}{{ $invoice->billing_city && $invoice->billing_country ? ', ' : '' }}{{ $invoice->billing_country }}
        @endif
    </div>

    <!-- Concepto -->
    <h2>CONCEPTO:</h2>
    <div class="concept-box">
        {{ $invoice->concept }}
        @if($invoice->application)
            <br><small><em>Programa: {{ $invoice->application->program->name }}</em></small>
        @endif
    </div>

    <!-- Detalle -->
    <table>
        <thead>
            <tr>
                <th>Descripción</th>
                <th class="text-right" style="width: 150px;">Monto</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Subtotal</td>
                <td class="text-right">
                    {{ $invoice->currency ? $invoice->currency->symbol : '₲' }} 
                    {{ number_format($invoice->subtotal, 2, ',', '.') }}
                </td>
            </tr>
            @if($invoice->tax_amount > 0)
            <tr>
                <td>Impuestos</td>
                <td class="text-right">
                    {{ $invoice->currency ? $invoice->currency->symbol : '₲' }} 
                    {{ number_format($invoice->tax_amount, 2, ',', '.') }}
                </td>
            </tr>
            @endif
            @if($invoice->discount_amount > 0)
            <tr>
                <td>Descuento</td>
                <td class="text-right" style="color: green;">
                    -{{ $invoice->currency ? $invoice->currency->symbol : '₲' }} 
                    {{ number_format($invoice->discount_amount, 2, ',', '.') }}
                </td>
            </tr>
            @endif
            <tr class="total-row">
                <td>TOTAL</td>
                <td class="text-right">
                    {{ $invoice->currency ? $invoice->currency->symbol : '₲' }} 
                    {{ number_format($invoice->total, 2, ',', '.') }}
                </td>
            </tr>
        </tbody>
    </table>

    @if($invoice->notes)
    <div class="notes">
        <strong>Notas:</strong><br>
        {{ $invoice->notes }}
    </div>
    @endif

    @if($invoice->paid_date)
    <div style="margin-top: 30px; padding: 15px; background: #d4edda; border-left: 4px solid #28a745;">
        <strong>✓ PAGADO EL {{ $invoice->paid_date->format('d/m/Y') }}</strong>
    </div>
    @endif

    <!-- Footer -->
    <div class="footer">
        <p>
            © {{ date('Y') }} Intercultural Experience - Todos los derechos reservados<br>
            Este documento fue generado electrónicamente y es válido sin firma
        </p>
    </div>
</body>
</html>
