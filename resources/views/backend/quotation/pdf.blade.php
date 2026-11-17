@php
function numberToWords($num) {
    $ones = ['', 'ONE', 'TWO', 'THREE', 'FOUR', 'FIVE', 'SIX', 'SEVEN', 'EIGHT', 'NINE', 'TEN',
             'ELEVEN', 'TWELVE', 'THIRTEEN', 'FOURTEEN', 'FIFTEEN', 'SIXTEEN', 'SEVENTEEN', 'EIGHTEEN', 'NINETEEN'];
    $tens = ['', '', 'TWENTY', 'THIRTY', 'FORTY', 'FIFTY', 'SIXTY', 'SEVENTY', 'EIGHTY', 'NINETY'];
    $scales = ['', 'THOUSAND', 'MILLION', 'BILLION'];
    if ($num == 0) return 'ZERO';
    $num = (int)$num;
    $parts = [];
    $scaleIndex = 0;
    while ($num > 0) {
        $chunk = $num % 1000;
        if ($chunk > 0) {
            $chunkWords = '';
            if ($chunk >= 100) {
                $chunkWords .= $ones[floor($chunk / 100)] . ' HUNDRED ';
                $chunk %= 100;
            }
            if ($chunk >= 20) {
                $chunkWords .= $tens[floor($chunk / 10)] . ' ';
                $chunk %= 10;
            }
            if ($chunk > 0) $chunkWords .= $ones[$chunk] . ' ';
            $chunkWords = trim($chunkWords);
            if ($scaleIndex > 0) $chunkWords .= ' ' . $scales[$scaleIndex];
            array_unshift($parts, $chunkWords);
        }
        $num = floor($num / 1000);
        $scaleIndex++;
    }
    return implode(' ', $parts);
}
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Quotation - {{ doc_number_display($quotation->reference_no, $general_setting->quotation_prefix ?? 'QTN') }}</title>
    <style>
        @page { size: A4; margin: 10mm; }
        body { font-family: Arial, sans-serif; font-size: 12px; width: 100%; max-width: 100%; margin: 0; padding: 0; box-sizing: border-box; }
        * { box-sizing: border-box; }
        table { width: 100%; border-collapse: collapse; }
    </style>
</head>
<body>
    @php
        $logo_file = $general_setting->invoice_logo ?: $general_setting->site_logo;
        $logo_src = $logo_file ? public_path('logo/' . $logo_file) : '';
        $companyName = $quotation->warehouse && $quotation->warehouse->name ? $quotation->warehouse->name : ($quotation->biller->company_name ?? $quotation->biller->name ?? $general_setting->company_name ?? 'GLOBAL PRACTISE ENTERPRISE');
        $companyInfo = $quotation->warehouse && $quotation->warehouse->name
            ? ($quotation->warehouse->name . '<br>' . ($quotation->warehouse->address ? nl2br(e($quotation->warehouse->address)) . '<br>' : '') . ($quotation->warehouse->phone ? 'TEL: ' . $quotation->warehouse->phone : ''))
            : ($general_setting->company_name ?? 'GLOBAL PRACTISE ENTERPRISE (CA-0260377-P)') . '<br>3050, JALAN AIR PUTIH,<br>TAMAN MOHD NOOR, CUKAI,<br>24000 KEMAMAN, TERENGGANU.<br><strong>TEL: 0111018081.</strong>';
    @endphp
    <!-- Header: Logo | QUOTATION | Company - table layout -->
    <table cellpadding="0" cellspacing="0" style="width: 100%; border-bottom: 2px solid #000; padding-bottom: 10px; margin-bottom: 13px;">
        <tr>
            <td style="width: 33%; vertical-align: top; text-align: left;">
                @if(!empty($logo_src))<img src="{{ $logo_src }}" alt="Logo" style="max-height: 90px;">@endif
            </td>
            <td style="width: 34%; vertical-align: middle; text-align: center; padding-top: 10px;">
                <strong style="font-size: 22px;">QUOTATION</strong>
            </td>
            <td style="width: 33%; vertical-align: top; text-align: right; font-size: 11px; line-height: 1.4; padding-bottom: 15px;">
                {!! $companyInfo !!}
            </td>
        </tr>
        <tr><td colspan="3" style="height: 19px; padding: 0; border: none; line-height: 0; font-size: 0;">&nbsp;</td></tr>
    </table>

    <!-- TO | Ref No, Date, Terms - table layout with margin so TEL and Delivery To don't stick -->
    <table cellpadding="0" cellspacing="0" style="width: 100%; margin-bottom: 12px;">
        <tr valign="top">
            <td style="width: 50%; padding-right: 15px; padding-bottom: 12px;">
                <strong>TO:</strong><br>
                <strong>{{ $quotation->customer->name ?? '' }}</strong><br>
                @if($quotation->customer && $quotation->customer->company_name){{ $quotation->customer->company_name }}<br>@endif
                @if($quotation->customer && $quotation->customer->address){!! nl2br(e($quotation->customer->address)) !!}<br>@endif
                @if($quotation->customer && $quotation->customer->city){{ $quotation->customer->city }}<br>@endif
                <strong>ATTN:</strong> {{ $quotation->customer->name ?? '' }}<br>
                <strong>TEL:</strong> {{ $quotation->customer->phone_number ?? '' }}<br>
                <strong>Delivery To:</strong> {!! nl2br(e($quotation->delivery_address ?? $quotation->customer->address ?? '')) !!}
            </td>
            <td style="width: 50%; text-align: right;">
                <strong>Page No:</strong> 1 / 1<br>
                <strong>Ref No:</strong> {{ doc_number_display($quotation->reference_no, $general_setting->quotation_prefix ?? 'QTN') }}<br>
                <strong>Date:</strong> {{ date($general_setting->date_format ?? 'd/m/Y', strtotime($quotation->created_at)) }}<br>
                <strong>Terms:</strong> {{ strtoupper($quotation->terms ?? 'CASH') }}
            </td>
        </tr>
    </table>

    <table style="width: 100%; border-collapse: collapse; margin-bottom: 12px;">
        <thead>
            <tr style="background-color: #f0f0f0;">
                <th style="border: 1px solid #000; padding: 8px; text-align: center; width: 5%;">NO.</th>
                <th style="border: 1px solid #000; padding: 8px; text-align: left; width: 10%;">ITEM CODE</th>
                <th style="border: 1px solid #000; padding: 8px; text-align: left; width: 40%;">DESCRIPTION</th>
                <th style="border: 1px solid #000; padding: 8px; text-align: center; width: 10%;">QTY</th>
                <th style="border: 1px solid #000; padding: 8px; text-align: right; width: 12%;">PRICE (RM)</th>
                <th style="border: 1px solid #000; padding: 8px; text-align: right; width: 11%;">DISCOUNT(RM)</th>
                <th style="border: 1px solid #000; padding: 8px; text-align: right; width: 12%;">TOTAL(RM)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($quotation->products as $key => $row)
            @php
                $unitCode = optional($row->unit)->unit_code ?? '';
                $computedLineTotal = ((float) $row->net_unit_price * (float) $row->qty) - (float) $row->discount + (float) $row->tax;
                $lineTotal = (is_numeric($row->total) && (float) $row->total != 0.0) ? (float) $row->total : $computedLineTotal;
            @endphp
            <tr>
                <td style="border: 1px solid #000; padding: 8px; text-align: center;">{{ $key + 1 }}</td>
                <td style="border: 1px solid #000; padding: 8px;">{{ optional($row->product)->code ?? '' }}</td>
                <td style="border: 1px solid #000; padding: 8px;">{{ optional($row->product)->name ?? 'N/A' }}</td>
                <td style="border: 1px solid #000; padding: 8px; text-align: center;">{{ $row->qty }} {{ $unitCode }}</td>
                <td style="border: 1px solid #000; padding: 8px; text-align: right;">{{ number_format($row->net_unit_price, $general_setting->decimal ?? 2, '.', ',') }}</td>
                <td style="border: 1px solid #000; padding: 8px; text-align: right;">{{ number_format($row->discount, $general_setting->decimal ?? 2, '.', ',') }}</td>
                <td style="border: 1px solid #000; padding: 8px; text-align: right;">{{ number_format($lineTotal, $general_setting->decimal ?? 2, '.', ',') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    @if($quotation->shipping_cost > 0)
    <div style="text-align: right; margin-bottom: 8px;">
        <strong>TRANSPORT/SHIPPING COST (RM):</strong> {{ number_format($quotation->shipping_cost, $general_setting->decimal ?? 2, '.', ',') }}
    </div>
    @endif

    <div style="margin-bottom: 12px;">
        <strong>Total Amount in Words:</strong> <span style="text-transform: uppercase;">RINGGIT MALAYSIA: {{ numberToWords(round($quotation->grand_total)) }} ONLY</span>
    </div>

    <div style="text-align: right; margin-bottom: 12px;">
        <div style="display: inline-block; border: 2px solid #000; padding: 10px 20px; font-size: 18px; font-weight: bold;">
            <div>TOTAL (RM)</div>
            <div style="font-size: 24px;">{{ number_format($quotation->grand_total, $general_setting->decimal ?? 2, '.', ',') }}</div>
        </div>
    </div>

    <div style="margin-bottom: 12px; font-size: 12px;">
        <p style="margin: 0 0 4px 0;"><strong>Payment Instructions:</strong> Payment by cheque should be made payable to {{ $companyName }}</p>
        <p style="margin: 0;"><strong>Bank Details:</strong> {{ $general_setting->bank_details ?? '' }}</p>
    </div>

    <div style="margin-bottom: 12px; font-size: 12px;">
        <strong>Terms & Conditions:</strong><br>
        {!! nl2br(e($quotation->terms_conditions ?? "Payment: 30 days. 100% charge on order cancellation penalty once PO issued to GP.\nDelivery: < 4 weeks\nWarranty: Manufacturer's warranty Where Applicable\nValidity: 45 days")) !!}
    </div>

    <table cellpadding="0" cellspacing="0" style="width: 100%; margin-top: 20px;">
        <tr>
            <td style="width: 50%; vertical-align: top; text-align: left;">
                <p style="margin: 0 0 25px 0;"><strong>FOR {{ strtoupper($companyName) }}</strong></p>
                <p style="border-top: 1px solid #000; padding-top: 5px; width: 200px; margin: 0;">Authorised Signature</p>
            </td>
            <td style="width: 50%; vertical-align: top; text-align: right;">
                <p style="margin: 0 0 25px 0;"><strong>We agree & accept as per above</strong></p>
                <p style="border-top: 1px solid #000; padding-top: 5px; width: 200px; margin: 0 0 0 auto;">Customer Signature & Company Stamp</p>
            </td>
        </tr>
    </table>
</body>
</html>
