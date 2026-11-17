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
    <title>Purchase Order - {{ doc_number_display($purchase->reference_no, $general_setting->purchase_order_prefix ?? 'PO') }}</title>
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
    @endphp
    <!-- Header: Logo | PURCHASE ORDER | Company - table layout -->
    <table cellpadding="0" cellspacing="0" style="width: 100%; border-bottom: 2px solid #000; padding-bottom: 10px; margin-bottom: 13px;">
        <tr>
            <td style="width: 33%; vertical-align: top; text-align: left;">
                @if(!empty($logo_src))<img src="{{ $logo_src }}" alt="Logo" style="max-height: 90px;">@endif
            </td>
            <td style="width: 34%; vertical-align: middle; text-align: center; padding-top: 10px;">
                <strong style="font-size: 22px;">PURCHASE ORDER</strong>
            </td>
            <td style="width: 33%; vertical-align: top; text-align: right; font-size: 11px; line-height: 1.4; padding-bottom: 15px;">
                @if($purchase->warehouse)
                <strong style="font-size: 14px;">{{ $purchase->warehouse->name }}</strong><br>
                @if($purchase->warehouse->address){!! nl2br(e($purchase->warehouse->address)) !!}<br>@endif
                @if($purchase->warehouse->phone)<strong>TEL: {{ $purchase->warehouse->phone }}</strong>@endif
                @if($purchase->warehouse->email)<br><strong>EMAIL: {{ $purchase->warehouse->email }}</strong>@endif
                @else
                <strong style="font-size: 14px;">{{ $general_setting->company_name ?? 'GLOBAL PRACTISE ENTERPRISE' }}</strong><br>
                3050, JALAN AIR PUTIH,<br>
                TAMAN MOHD NOOR, CUKAI,<br>
                24000 KEMAMAN, TERENGGANU.<br>
                <strong>TEL: 0111018081</strong>
                @endif
            </td>
        </tr>
        <tr><td colspan="3" style="height: 19px; padding: 0; border: none; line-height: 0; font-size: 0;">&nbsp;</td></tr>
    </table>

    <!-- SUPPLIER | DELIVER TO | P.O NO, DATE, TERMS - table layout -->
    <table cellpadding="0" cellspacing="0" style="width: 100%; margin-bottom: 12px;">
        <tr valign="top">
            <td style="width: 33%; padding-right: 12px; padding-bottom: 12px;">
                <strong>SUPPLIER :-</strong><br>
                <strong>{{ $purchase->supplier->name ?? '' }}</strong><br>
                @if($purchase->supplier && $purchase->supplier->company_name){{ $purchase->supplier->company_name }}<br>@endif
                @if($purchase->supplier && $purchase->supplier->address){{ $purchase->supplier->address }}<br>@endif
                @if($purchase->supplier && $purchase->supplier->city){{ $purchase->supplier->city }}<br>@endif
                <strong>CONTACT:</strong> {{ $purchase->supplier->name ?? '' }}<br>
                <strong>TEL:</strong> {{ $purchase->supplier->phone_number ?? '' }}<br>
                @if($purchase->supplier && $purchase->supplier->email)<strong>EMAIL:</strong> {{ $purchase->supplier->email }}@endif
            </td>
            <td style="width: 34%; padding-left: 8px; padding-right: 12px; padding-top: 4px;">
                <strong>DELIVER TO :-</strong><br>
                @if($purchase->delivery_address)
                    {!! nl2br(e($purchase->delivery_address)) !!}
                @else
                    <strong>{{ $purchase->warehouse->name ?? '' }}</strong><br>
                    {{ $purchase->warehouse->address ?? '' }}<br>
                    TEL: {{ $purchase->warehouse->phone ?? '' }}
                @endif
            </td>
            <td style="width: 33%; text-align: right;">
                <strong>PAGE NO:</strong> 1 / 1<br>
                <strong>P.O NO:</strong> <strong style="font-size: 16px;">{{ doc_number_display($purchase->reference_no, $general_setting->purchase_order_prefix ?? 'PO') }}</strong><br>
                <strong>DATE:</strong> {{ date($general_setting->date_format, strtotime($purchase->created_at)) }}<br>
                <strong>TERMS:</strong> {{ strtoupper($purchase->terms ?? 'CASH') }}
            </td>
        </tr>
    </table>

    <table style="width: 100%; border-collapse: collapse; margin-bottom: 0;">
        <thead>
            <tr style="background-color: #f0f0f0;">
                <th style="border: 1px solid #000; padding: 8px; text-align: center; width: 5%;">NO.</th>
                <th style="border: 1px solid #000; padding: 8px; text-align: left; width: 12%;">ITEM CODE</th>
                <th style="border: 1px solid #000; padding: 8px; text-align: left; width: 33%;">DESCRIPTION</th>
                <th style="border: 1px solid #000; padding: 8px; text-align: center; width: 10%;">QTY</th>
                <th style="border: 1px solid #000; padding: 8px; text-align: right; width: 13%;">UNIT COST (RM)</th>
                <th style="border: 1px solid #000; padding: 8px; text-align: right; width: 12%;">TAX (RM)</th>
                <th style="border: 1px solid #000; padding: 8px; text-align: right; width: 15%;">TOTAL (RM)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($products as $key => $product)
            <tr>
                <td style="border: 1px solid #000; padding: 8px; text-align: center;">{{ $key + 1 }}</td>
                <td style="border: 1px solid #000; padding: 8px;">{{ optional($product->product)->code ?? '' }}</td>
                <td style="border: 1px solid #000; padding: 8px;">{{ optional($product->product)->name ?? 'N/A' }}</td>
                <td style="border: 1px solid #000; padding: 8px; text-align: center;">{{ $product->qty }} {{ optional($product->unit)->unit_code ?? '' }}</td>
                <td style="border: 1px solid #000; padding: 8px; text-align: right;">{{ number_format($product->net_unit_cost, $general_setting->decimal, '.', ',') }}</td>
                <td style="border: 1px solid #000; padding: 8px; text-align: right;">{{ number_format($product->tax, $general_setting->decimal, '.', ',') }}</td>
                <td style="border: 1px solid #000; padding: 8px; text-align: right;">{{ number_format($product->total, $general_setting->decimal, '.', ',') }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="6" style="border: 1px solid #000; padding: 8px; text-align: right;">Subtotal</td>
                <td style="border: 1px solid #000; padding: 8px; text-align: right;">{{ number_format($purchase->total_cost, $general_setting->decimal, '.', ',') }}</td>
            </tr>
            @if($purchase->order_tax > 0)
            <tr>
                <td colspan="6" style="border: 1px solid #000; padding: 8px; text-align: right;">Order Tax ({{ $purchase->order_tax_rate }}%)</td>
                <td style="border: 1px solid #000; padding: 8px; text-align: right;">{{ number_format($purchase->order_tax, $general_setting->decimal, '.', ',') }}</td>
            </tr>
            @endif
            @if($purchase->order_discount > 0)
            <tr>
                <td colspan="6" style="border: 1px solid #000; padding: 8px; text-align: right;">Order Discount</td>
                <td style="border: 1px solid #000; padding: 8px; text-align: right;">-{{ number_format($purchase->order_discount, $general_setting->decimal, '.', ',') }}</td>
            </tr>
            @endif
            <tr>
                <td colspan="6" style="border: 1px solid #000; padding: 8px; text-align: right; font-weight: bold; background-color: #f0f0f0;">GRAND TOTAL (RM)</td>
                <td style="border: 1px solid #000; padding: 8px; text-align: right; font-weight: bold; font-size: 16px; background-color: #f0f0f0;">{{ number_format($purchase->grand_total, $general_setting->decimal, '.', ',') }}</td>
            </tr>
            <tr>
                <td colspan="6" style="border: 1px solid #000; padding: 8px; text-align: right;">Paid Amount</td>
                <td style="border: 1px solid #000; padding: 8px; text-align: right;">{{ number_format($purchase->paid_amount, $general_setting->decimal, '.', ',') }}</td>
            </tr>
            <tr>
                <td colspan="6" style="border: 1px solid #000; padding: 8px; text-align: right; font-weight: bold;">Due Amount</td>
                <td style="border: 1px solid #000; padding: 8px; text-align: right; font-weight: bold;">{{ number_format($purchase->grand_total - $purchase->paid_amount, $general_setting->decimal, '.', ',') }}</td>
            </tr>
        </tfoot>
    </table>

    @if($purchase->shipping_cost > 0)
    <div style="text-align: right; margin: 8px 0;">
        <strong>TRANSPORT/SHIPPING COST (RM):</strong> {{ number_format($purchase->shipping_cost, $general_setting->decimal, '.', ',') }}
    </div>
    @endif

    <div style="margin: 12px 0;">
        <strong>RINGGIT MALAYSIA:</strong> <span style="text-transform: uppercase;">{{ numberToWords(floor($purchase->grand_total)) }} ONLY</span>
    </div>

    @if($purchase->note)
    <div style="margin-bottom: 12px; font-size: 12px;">
        <strong>Notes:</strong><br>{{ $purchase->note }}
    </div>
    @endif

    <div style="margin-bottom: 12px; font-size: 11px;">
        <p><strong>Payment Instructions:</strong> Payment by cheque should be made payable to <strong>{{ $general_setting->company_name ?? 'GLOBAL PRACTISE ENTERPRISE' }}</strong></p>
        @if($general_setting->bank_details)
        <p><strong>Bank Details:</strong> {{ $general_setting->bank_details }}</p>
        @else
        <p><strong>Bank Details:</strong> MAYBANK, Account No: 563028623337</p>
        @endif
    </div>

    <div style="margin-bottom: 12px; font-size: 11px;">
        <strong>Terms & Conditions:</strong><br>
        {!! nl2br(e($purchase->terms_conditions ?? "Payment: " . ($purchase->terms ?? 'CASH') . "\nDelivery: As per quotation from vendor\nCancellation: Cancel after PO will be charged/claimed 100% of PO value\nRefund: Refund has to be made within 1 week")) !!}
    </div>

    <table cellpadding="0" cellspacing="0" style="width: 100%; margin-top: 20px;">
        <tr>
            <td style="width: 50%; vertical-align: top; text-align: left;">
                <p style="margin: 0 0 25px 0;"><strong>FOR {{ strtoupper($general_setting->company_name ?? '') }}</strong></p>
                <p style="border-top: 1px solid #000; padding-top: 5px; width: 200px; margin: 0;">Authorised Signature</p>
            </td>
            <td style="width: 50%; vertical-align: top; text-align: right;">
                <p style="margin: 0 0 25px 0;"><strong>SUPPLIER ACKNOWLEDGEMENT</strong></p>
                <p style="border-top: 1px solid #000; padding-top: 5px; width: 200px; margin: 0 0 0 auto;">Supplier Signature & Stamp</p>
            </td>
        </tr>
    </table>

    <div style="margin-top: 20px; text-align: center; font-size: 10px; color: #666;">
        <p>This is a computer-generated document. No signature is required.</p>
    </div>
</body>
</html>
