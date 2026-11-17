<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Quotation Details</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            color: #000;
            line-height: 1.4;
            margin: 0;
            padding: 20px;
        }
        .header-table {
            width: 100%;
            border-bottom: 2px solid #000;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }
        .header-table td {
            border: none;
            padding: 0;
            vertical-align: top;
        }
        .logo-cell {
            width: 33%;
            text-align: left;
        }
        .title-cell {
            width: 33%;
            text-align: center;
        }
        .company-cell {
            width: 33%;
            text-align: right;
            font-size: 11px;
        }
        .details-table {
            width: 100%;
            margin-bottom: 20px;
        }
        .details-table td {
            border: none;
            padding: 0;
            vertical-align: top;
        }
        .customer-cell {
            width: 50%;
        }
        .quotation-cell {
            width: 50%;
            text-align: right;
        }
        table.product-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        table.product-table th {
            background-color: #f0f0f0;
            border: 1px solid #000;
            padding: 8px;
            text-align: center;
        }
        table.product-table td {
            border: 1px solid #000;
            padding: 8px;
        }
        .total-container {
            text-align: right;
            margin-bottom: 20px;
        }
        .grand-total-box {
            display: inline-block;
            border: 2px solid #000;
            padding: 10px 20px;
            font-size: 18px;
            font-weight: bold;
            text-align: right;
        }
        .footer-table {
            width: 100%;
            margin-top: 40px;
        }
        .footer-table td {
            border: none;
            padding: 0;
            vertical-align: top;
        }
        .signature-line {
            width: 200px;
            border-top: 1px solid #000;
            padding-top: 5px;
            margin-top: 50px;
        }
    </style>
</head>
<body>
    <table class="header-table">
        <tr>
            <td class="logo-cell">
                @if($general_setting->invoice_logo)
                    <img src="{{ isset($pdf_mode) ? public_path('logo/' . $general_setting->invoice_logo) : url('logo', $general_setting->invoice_logo) }}" style="max-height: 80px;">
                @elseif($general_setting->site_logo)
                    <img src="{{ isset($pdf_mode) ? public_path('logo/' . $general_setting->site_logo) : url('logo', $general_setting->site_logo) }}" style="max-height: 80px;">
                @endif
            </td>
            <td class="title-cell">
                <h2 style="margin: 0; font-size: 24px; text-transform: uppercase; padding-top: 10px;">QUOTATION</h2>
            </td>
            <td class="company-cell">
                <strong style="font-size: 13px;">{{ $quotation_data['biller_company'] ?? $general_setting->company_name }}</strong><br>
                {!! nl2br(e($quotation_data['biller_address'] ?? '')) !!}<br>
                {{ $quotation_data['biller_city'] ?? '' }}<br>
                <strong>TEL: {{ $quotation_data['biller_phone'] ?? '' }}</strong>
            </td>
        </tr>
    </table>

    <table class="details-table">
        <tr>
            <td class="customer-cell">
                <strong>TO:</strong><br>
                {{ $quotation_data['customer_name'] }}<br>
                {!! nl2br(e($quotation_data['customer_address'] ?? '')) !!}<br>
                {{ $quotation_data['customer_city'] ?? '' }}<br><br>
                <strong>ATTN:</strong> {{ $quotation_data['customer_name'] }}<br>
                <strong>TEL:</strong> {{ $quotation_data['customer_phone'] ?? '' }}
            </td>
            <td class="quotation-cell">
                <strong>Ref No:</strong> {{ $quotation_data['reference_no'] }}<br>
                <strong>Date:</strong> {{ $quotation_data['created_at'] }}<br>
                <strong>Terms:</strong> CREDIT CARD
            </td>
        </tr>
    </table>

    <table class="product-table">
        <thead>
            <tr>
                <th style="width: 5%;">NO.</th>
                <th style="width: 15%;">ITEM CODE</th>
                <th style="width: 40%;">DESCRIPTION</th>
                <th style="width: 10%;">QTY</th>
                <th style="width: 15%;">PRICE (RM)</th>
                <th style="width: 15%;">TOTAL (RM)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($quotation_data['products'] as $key => $product)
            <tr>
                <td style="text-align: center;">{{ $key + 1 }}</td>
                <td>{{ $quotation_data['product_codes'][$key] ?? '' }}</td>
                <td>{{ $product }}</td>
                <td style="text-align: center;">{{ $quotation_data['qty'][$key] }} {{ $quotation_data['unit'][$key] }}</td>
                <td style="text-align: right;">{{ number_format($quotation_data['total'][$key] / $quotation_data['qty'][$key], $general_setting->decimal) }}</td>
                <td style="text-align: right;">{{ number_format($quotation_data['total'][$key], $general_setting->decimal) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div style="margin-bottom: 20px;">
        <strong>Total Amount in Words:</strong> <span style="text-transform: uppercase;">{{ $quotation_data['total_in_words'] ?? '' }} ONLY</span>
    </div>

    <div class="total-container">
        <div class="grand-total-box">
            <div style="font-size: 14px;">TOTAL (RM)</div>
            <div style="font-size: 22px;">{{ number_format($quotation_data['grand_total'], $general_setting->decimal) }}</div>
        </div>
    </div>

    <div style="font-size: 11px; margin-bottom: 20px;">
        <p><strong>Payment Instructions:</strong> Payment by cheque should be made payable to {{ $quotation_data['biller_company'] ?? $general_setting->company_name }}</p>
        <p><strong>Bank Details:</strong> {{ $general_setting->bank_details ?? "" }}</p>
    </div>

    <div style="font-size: 11px; margin-bottom: 20px;">
        <strong>Terms & Conditions:</strong>
        <ul style="margin: 5px 0; padding-left: 20px;">
            <li><strong>Payment:</strong> 30 days. 100% charge on order cancellation penalty once PO issued to GP.</li>
            <li><strong>Delivery:</strong> &lt; 4 weeks</li>
            <li><strong>Warranty:</strong> Manufacturer's warranty Where Applicable</li>
            <li><strong>Validity:</strong> 45 days</li>
        </ul>
    </div>

    <table class="footer-table">
        <tr>
            <td>
                <strong>FOR {{ $quotation_data['biller_company'] ?? $general_setting->company_name }}</strong>
                <div class="signature-line">Authorised Signature</div>
            </td>
            <td style="text-align: right;">
                <strong>We agree & accept as per above</strong>
                <div class="signature-line" style="margin-left: auto;">Customer Signature & Company Stamp</div>
            </td>
        </tr>
    </table>
</body>
</html>