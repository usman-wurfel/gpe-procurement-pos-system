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
    <title>Invoice - {{ doc_number_display($invoice->invoice_no, $general_setting->invoice_prefix ?? 'INV') }}</title>
    <style>
        @page { size: A4; margin: 10mm; }
        body { font-family: Arial, sans-serif; font-size: 11px; line-height: 1.25; width: 100%; max-width: 100%; margin: 0; padding: 0; box-sizing: border-box; }
        * { box-sizing: border-box; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 4px 6px; vertical-align: top; }
    </style>
</head>
<body>
    @php
        $logo_file = $general_setting->invoice_logo ?: $general_setting->site_logo;
        $logo_src = $logo_file ? public_path('logo/' . $logo_file) : '';
    @endphp
    <!-- Header: Logo | INVOICE | Company - table layout for PDF -->
    <table cellpadding="0" cellspacing="0" style="width: 100%; border-bottom: 2px solid #000; padding-bottom: 8px; margin-bottom: 13px;">
        <tr>
            <td style="width: 33%; vertical-align: top; text-align: left;">
                @if(!empty($logo_src))<img src="{{ $logo_src }}" alt="Logo" style="max-height: 90px;">@endif
            </td>
            <td style="width: 34%; vertical-align: middle; text-align: center; padding-top: 8px;">
                <strong style="font-size: 22px;">INVOICE</strong>
            </td>
            <td style="width: 33%; vertical-align: top; text-align: right; font-size: 10px; line-height: 1.4; padding-bottom: 15px;">
                <strong style="font-size: 14px;">GLOBAL PRACTISE ENTERPRISE (CA-0260377-P)</strong><br>
                3050, JALAN AIR PUTIH,<br>
                TAMAN MOHD NOOR, CUKAI,<br>
                24000 KEMAMAN, TERENGGANU.<br>
                <strong>TEL: 0111018081</strong>
            </td>
        </tr>
        <tr><td colspan="3" style="height: 19px; padding: 0; border: none; line-height: 0; font-size: 0;">&nbsp;</td></tr>
    </table>

    <!-- TO | DELIVERY TO | Invoice details - table layout, with margin so TEL and DELIVERY TO don't stick -->
    <table cellpadding="0" cellspacing="0" style="width: 100%; margin-bottom: 10px;">
        <tr valign="top">
            <td style="width: 33%; padding-right: 12px; padding-bottom: 12px;">
                <strong>TO :-</strong><br>
                <strong>{{ $invoice->customer->name ?? '' }}</strong><br>
                @if($invoice->customer && $invoice->customer->address){{ $invoice->customer->address }}<br>@endif
                @if($invoice->customer && $invoice->customer->city){{ $invoice->customer->city }}<br>@endif
                <strong>ATTN:</strong> {{ $invoice->customer->name ?? '' }}<br>
                <strong>TEL:</strong> {{ $invoice->customer->phone_number ?? '' }}
            </td>
            <td style="width: 34%; padding-left: 8px; padding-right: 12px; padding-top: 4px;">
                <strong>DELIVERY TO :-</strong><br>
                <span style="margin-top: 6px; display: inline-block;">{!! nl2br(e($invoice->delivery_address ?? $invoice->customer->address ?? "")) !!}</span>
            </td>
            <td style="width: 33%; text-align: right;">
                <strong>PAGE NO:</strong> 1 / 1<br>
                <strong>INVOICE NO:</strong> {{ doc_number_display($invoice->invoice_no, $general_setting->invoice_prefix ?? 'INV') }}<br>
                <strong>DATE:</strong> {{ date($general_setting->date_format, strtotime($invoice->created_at)) }}<br>
                <strong>TERMS:</strong> {{ strtoupper($invoice->terms ?? $invoice->payment_method ?? 'CASH') }}@if($invoice->payment_details)<br><small>{{ $invoice->payment_details }}</small>@endif<br>
                @php
                    $do_no = '';
                    if($invoice->deliveryOrder) { $do_no = $invoice->deliveryOrder->delivery_order_no; }
                    elseif($invoice->quotation) { $relatedDO = \App\Models\DeliveryOrder::where('quotation_id', $invoice->quotation_id)->first(); $do_no = $relatedDO ? $relatedDO->delivery_order_no : ''; }
                @endphp
                @if($invoice->po_number || (isset($related_purchase) && $related_purchase))<strong>P.O NO:</strong> {{ doc_number_display($invoice->po_number ?? ($related_purchase ? $related_purchase->reference_no : ''), $general_setting->purchase_order_prefix ?? 'PO') }}<br>@endif
                @if($do_no)<strong>D.O NO:</strong> {{ doc_number_display($do_no, $general_setting->delivery_order_prefix ?? 'DO') }}<br>@endif
                <strong>QUO NO:</strong> {{ doc_number_display($invoice->quotation_number ?? ($invoice->quotation ? $invoice->quotation->reference_no : ''), $general_setting->quotation_prefix ?? 'QTN') }}<br>
                <strong>SALESPERSON:</strong> {{ $invoice->user->name ?? '' }}
            </td>
        </tr>
    </table>

    <table style="width: 100%; border-collapse: collapse; margin-bottom: 4px;">
        <thead>
            <tr style="background-color: #f0f0f0;">
                <th style="border: 1px solid #000; text-align: center; width: 5%;">NO.</th>
                <th style="border: 1px solid #000; text-align: left; width: 12%;">ITEM CODE</th>
                <th style="border: 1px solid #000; text-align: left; width: 38%;">DESCRIPTION</th>
                <th style="border: 1px solid #000; text-align: center; width: 10%;">QTY</th>
                <th style="border: 1px solid #000; text-align: right; width: 12%;">PRICE (RM)</th>
                <th style="border: 1px solid #000; text-align: right; width: 11%;">DISCOUNT(RM)</th>
                <th style="border: 1px solid #000; text-align: right; width: 12%;">TOTAL(RM)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($invoice->products as $key => $product)
            <tr>
                <td style="border: 1px solid #000; text-align: center;">{{ $key + 1 }}</td>
                <td style="border: 1px solid #000;">{{ optional($product->product)->code ?? '' }}</td>
                <td style="border: 1px solid #000; font-size: 10px; line-height: 1.2;">{{ optional($product->product)->name ?? 'N/A' }}</td>
                <td style="border: 1px solid #000; text-align: center;">{{ $product->qty }} {{ optional($product->unit)->unit_code ?? '' }}</td>
                <td style="border: 1px solid #000; text-align: right;">{{ number_format($product->net_unit_price, $general_setting->decimal, '.', ',') }}</td>
                <td style="border: 1px solid #000; text-align: right;">{{ number_format($product->discount, $general_setting->decimal, '.', ',') }}</td>
                <td style="border: 1px solid #000; text-align: right;">{{ number_format($product->total, $general_setting->decimal, '.', ',') }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="6" style="border: 1px solid #000; text-align: right; font-weight: bold;">TOTAL (RM)</td>
                <td style="border: 1px solid #000; text-align: right; font-weight: bold; font-size: 14px;">{{ number_format($invoice->grand_total, $general_setting->decimal, '.', ',') }}</td>
            </tr>
        </tfoot>
    </table>

    @if($invoice->shipping_cost > 0)
    <div style="text-align: right; margin-bottom: 8px; font-size: 10px;">
        <strong>TRANSPORT/SHIPPING COST (RM):</strong> {{ number_format($invoice->shipping_cost, $general_setting->decimal, '.', ',') }}
    </div>
    @endif

    <div style="margin: 6px 0; font-size: 10px;">
        <strong>Total Amount in Words:</strong><br>
        <span style="text-transform: uppercase;">RINGGIT MALAYSIA: {{ numberToWords(round($invoice->grand_total)) }} ONLY</span>
    </div>

    <div style="margin-bottom: 8px; font-size: 10px;">
        <p style="margin: 0 0 2px 0;"><strong>Payment by cheque should be made payable to {{ $general_setting->company_name }}</strong><br>
        <strong>{{ $general_setting->bank_details ?? '' }}</strong></p>
    </div>

    <div style="margin-bottom: 8px; font-size: 10px;">
        <strong>Terms & Conditions:</strong><br>
        {!! nl2br(e($invoice->terms_conditions ?? "*Goods sold are not returnable")) !!}
    </div>

    <table cellpadding="0" cellspacing="0" style="width: 100%; margin-top: 15px;">
        <tr>
            <td style="width: 50%; vertical-align: top; text-align: left;">
                <p style="margin: 0 0 25px 0;"><strong>FOR {{ strtoupper($invoice->biller->company_name ?? $invoice->biller->name ?? $general_setting->company_name) }}</strong></p>
                <p style="border-top: 1px solid #000; padding-top: 4px; width: 200px; margin: 0; font-size: 10px;">Authorised Signature</p>
            </td>
            <td style="width: 50%; vertical-align: top; text-align: right;">
                <p style="margin: 0 0 25px 0;"><strong>RECEIVED BY</strong></p>
                <p style="border-top: 1px solid #000; padding-top: 4px; width: 200px; margin: 0 0 0 auto; font-size: 10px;">Customer Signature & Stamp</p>
            </td>
        </tr>
    </table>
</body>
</html>
