@php
    $logo_src = isset($logo_url) ? $logo_url : ($general_setting->invoice_logo ? url('logo', $general_setting->invoice_logo) : url('logo', $general_setting->site_logo));
    $rawDoNameFs = $general_setting->delivery_order_product_name_font_size ?? '12';
    $doProductNameFontPx = is_numeric($rawDoNameFs) ? max(6, min(32, (float) $rawDoNameFs)) : 12;
@endphp
<!-- Header: Logo | Title | Company - side by side with table -->
<table class="print-no-border" cellpadding="0" cellspacing="0" style="width: 100%; border-bottom: 2px solid #000; padding-bottom: 10px; margin-bottom: 13px;">
    <tr>
        <td style="width: 33%; vertical-align: top; text-align: left;">
            @if(!empty($logo_src))<img src="{{ $logo_src }}" alt="Logo" style="max-height: 90px;">@endif
        </td>
        <td style="width: 34%; vertical-align: middle; text-align: center; padding-top: 10px;">
            <strong style="font-size: 18px;">DELIVERY ORDER</strong>
        </td>
        <td style="width: 33%; vertical-align: top; text-align: right; font-size: 11px; line-height: 1.4; padding-bottom: 15px;">
            <strong style="font-size: 14px;">GLOBAL PRACTISE ENTERPRISE (CA-0260377-P)</strong><br>
            3050, JALAN AIR PUTIH,<br>
            TAMAN MOHD NOOR, CUKAI,<br>
            24000 KEMAMAN, TERENGGANU.<br>
            <strong>TEL: 0111018081</strong>
        </td>
    </tr>
    <tr><td colspan="3" style="height: 19px; padding: 0; margin: 0; border: none; line-height: 0; font-size: 0;">&nbsp;</td></tr>
</table>
<!-- TO | DELIVERY TO | PAGE NO, DATE, D.O NO etc - side by side with table -->
<table class="print-no-border" cellpadding="0" cellspacing="0" style="width: 100%; margin-bottom: 7px;">
    <tr valign="top">
        <td style="width: 33%; padding-right: 12px;">
            <strong>TO :-</strong><br>
            <strong>{{ $delivery_order->customer->name ?? '' }}</strong><br>
            @if($delivery_order->customer && $delivery_order->customer->company_name){{ $delivery_order->customer->company_name }}<br>@endif
            @if($delivery_order->customer && $delivery_order->customer->address){{ $delivery_order->customer->address }}<br>@endif
            @if($delivery_order->customer && $delivery_order->customer->city){{ $delivery_order->customer->city }}<br>@endif
            <strong>ATTN:</strong> {{ $delivery_order->customer->name ?? '' }}<br>
            <strong>TEL:</strong> {{ $delivery_order->customer->phone_number ?? '' }}
        </td>
        <td style="width: 34%; padding-right: 12px;">
            <strong>DELIVERY TO :-</strong><br>
            <span style="font-size: 13px; font-weight: bold;">{!! nl2br(e($delivery_order->delivery_address ?? $delivery_order->customer->address ?? "")) !!}</span>
        </td>
        <td style="width: 33%; text-align: right;">
            @php
                $relatedInvoice = \App\Models\Invoice::where('delivery_order_id', $delivery_order->id)->first();
                if(!$relatedInvoice && $delivery_order->quotation_id) {
                    $relatedInvoice = \App\Models\Invoice::where('quotation_id', $delivery_order->quotation_id)->first();
                }
            @endphp
            <strong>PAGE NO:</strong> 1 / 1<br>
            @if(isset($relatedInvoice) && $relatedInvoice)<strong>INVOICE NO:</strong> {{ doc_number_display($relatedInvoice->invoice_no, $general_setting->invoice_prefix ?? 'INV') }}<br>@endif
            <strong>DATE:</strong> {{ date($general_setting->date_format, strtotime($delivery_order->created_at)) }}<br>
            <strong>TERMS:</strong> {{ $relatedInvoice ? strtoupper($relatedInvoice->payment_method ?? 'CASH') : 'CASH' }}<br>
            @if($delivery_order->po_number || (isset($related_purchase) && $related_purchase))<strong>P.O NO:</strong> {{ doc_number_display($delivery_order->po_number ?? ($related_purchase ? $related_purchase->reference_no : ''), $general_setting->purchase_order_prefix ?? 'PO') }}<br>@endif
            <strong>D.O NO:</strong> <strong style="font-size: 16px;">{{ doc_number_display($delivery_order->delivery_order_no, $general_setting->delivery_order_prefix ?? 'DO') }}</strong><br>
            <strong>QUO NO:</strong> {{ doc_number_display($delivery_order->quotation_number ?? ($delivery_order->quotation ? $delivery_order->quotation->reference_no : ''), $general_setting->quotation_prefix ?? 'QTN') }}
        </td>
    </tr>
</table>

<!-- Products Table -->
@php $totalQty = 0; @endphp
<table style="width: 100%; border-collapse: collapse; margin-bottom: 0; font-size: 12px;">
    <thead>
        <tr style="background-color: #f0f0f0;">
            <th style="border: 1px solid #000; padding: 8px; text-align: center; width: 5%; font-size: 14px;">NO.</th>
            <th style="border: 1px solid #000; padding: 8px; text-align: left; width: 15%;">ITEM CODE</th>
            <th style="border: 1px solid #000; padding: 8px; text-align: left; width: 65%;">DESCRIPTION</th>
            <th style="border: 1px solid #000; padding: 8px; text-align: center; width: 15%;">QUANTITY</th>
        </tr>
    </thead>
    <tbody>
        @foreach($delivery_order->products as $key => $product)
        @php $totalQty += $product->qty; @endphp
        <tr>
            <td style="border: 1px solid #000; padding: 8px; text-align: center; font-size: 15px; font-weight: bold;">{{ $key + 1 }}</td>
            <td style="border: 1px solid #000; padding: 8px;">{{ optional($product->product)->code ?? '' }}</td>
            <td style="border: 1px solid #000; padding: 8px; font-size: {{ $doProductNameFontPx }}px; line-height: 1.35;">{{ optional($product->product)->name ?? 'N/A' }}</td>
            <td style="border: 1px solid #000; padding: 8px; text-align: center;">{{ $product->qty }} {{ optional($product->unit)->unit_code ?? '' }}</td>
        </tr>
        @endforeach
        @for($i = count($delivery_order->products); $i < 10; $i++)
        <tr>
            <td style="border: 1px solid #000; padding: 8px; height: 30px;">&nbsp;</td>
            <td style="border: 1px solid #000; padding: 8px;"></td>
            <td style="border: 1px solid #000; padding: 8px;"></td>
            <td style="border: 1px solid #000; padding: 8px;"></td>
        </tr>
        @endfor
    </tbody>
    <tfoot>
        <tr>
            <td colspan="3" style="border: 1px solid #000; padding: 8px; text-align: right; font-weight: bold;">TOTAL QUANTITY</td>
            <td style="border: 1px solid #000; padding: 8px; text-align: center; font-weight: bold;">{{ $totalQty }}</td>
        </tr>
    </tfoot>
</table>

@if($delivery_order->note)
<div style="margin: 10px 0;">
    <strong>Notes:</strong> {{ $delivery_order->note }}
</div>
@endif

<table cellpadding="0" cellspacing="0" style="width: 100%; margin-top: 15px;">
    <tr>
        <td style="width: 50%; vertical-align: top; text-align: left;">
            <strong>FOR GLOBAL PRACTISE ENTERPRISE</strong><br>
            <div style="height: 40px; width: 210px; border-bottom: 1px solid #000; margin: 5px 0;"></div>
            <span style="font-size: 11px;">Authorised Signature</span>
        </td>
        <td style="width: 50%; vertical-align: top; text-align: right;">
            <strong>RECEIVED BY</strong><br>
            <div style="height: 40px; width: 210px; border-bottom: 1px solid #000; margin: 5px 0 5px auto;"></div>
            <span style="font-size: 11px;">Customer Signature & Stamp</span>
        </td>
    </tr>
</table>
