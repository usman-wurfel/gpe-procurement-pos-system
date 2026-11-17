@extends('backend.layout.main') @section('content')

<section class="forms">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between d-print-none">
                        <h4>Invoice #{{ doc_number_display($invoice->invoice_no, $general_setting->invoice_prefix ?? 'INV') }}</h4>
                        <div>
                            @if($invoice->status != 'completed')
                            <a href="{{route('invoices.edit', $invoice->id)}}" class="btn btn-primary btn-sm"><i class="fa fa-edit"></i> {{__('db.edit')}}</a>
                            @endif
                            <button onclick="printInvoice()" class="btn btn-default btn-sm"><i class="fa fa-print"></i> {{__('db.Print')}}</button>
                            <a href="{{ route('invoices.pdf', $invoice->id) }}" class="btn btn-default btn-sm" target="_blank"><i class="fa fa-file-pdf-o"></i> PDF</a>
                            <a href="{{route('invoices.index')}}" class="btn btn-secondary btn-sm">{{__('db.Back')}}</a>
                        </div>
                    </div>
                    
                    <!-- Invoice Print View -->
                    <div class="card-body" id="invoice-print-view" style="padding: 20px; font-family: Arial, sans-serif;">
                        <!-- Header with Logo, Title and Company Info -->
                        <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 20px; border-bottom: 2px solid #000; padding-bottom: 15px;">
                            <div style="flex: 1; text-align: left;">
                                <img src="{{ $general_setting->invoice_logo ? url('logo', $general_setting->invoice_logo) : url('logo', $general_setting->site_logo) }}" alt="Logo" style="max-height: 120px;">
                            </div>
                            <div style="flex: 1; text-align: center; padding-top: 20px;">
                                <h2 style="margin: 0; font-size: 28px; font-weight: bold; text-transform: uppercase;">INVOICE</h2>
                            </div>
                            <div style="flex: 1; text-align: right; font-size: 11px; line-height: 1.5;">
                                <strong style="font-size: 14px;">GLOBAL PRACTISE ENTERPRISE (CA-0260377-P)</strong><br>
                                3050, JALAN AIR PUTIH,<br>
                                TAMAN MOHD NOOR, CUKAI,<br>
                                24000 KEMAMAN, TERENGGANU.<br>
                                <strong>TEL: 0111018081</strong>
                            </div>
                        </div>


                        <!-- Recipient and Invoice Details -->
                        <div style="display: flex; justify-content: space-between; margin-bottom: 20px;">
                            <div style="flex: 1; padding-right: 20px;">
                                <div style="margin-bottom: 15px;">
                                    <strong>TO :-</strong><br>
                                    <div style="margin-top: 5px;">
                                        <strong>{{ $invoice->customer->name ?? '' }}</strong><br>
                                        @if($invoice->customer && $invoice->customer->address)
                                        {{ $invoice->customer->address }}<br>
                                        @endif
                                        @if($invoice->customer && $invoice->customer->city)
                                        {{ $invoice->customer->city }}<br>
                                        @endif
                                    </div>
                                </div>
                                <div style="margin-bottom: 10px;">
                                    <strong>ATTN:</strong> {{ $invoice->customer->name ?? '' }}
                                </div>
                                <div style="margin-bottom: 10px;">
                                    <strong>TEL:</strong> {{ $invoice->customer->phone_number ?? '' }}
                                </div>
                            </div>
                            <div style="flex: 1;">
                                <strong>DELIVERY TO :-</strong><br>
                                <div style="margin-top: 5px;">
                                    {!! nl2br(e($invoice->delivery_address ?? $invoice->customer->address ?? "")) !!}
                                </div>
                            </div>
                            <div style="flex: 1; text-align: right;">
                                <div style="margin-bottom: 10px;">
                                    <strong>PAGE NO:</strong> 1 / 1
                                </div>
                                <div style="margin-bottom: 10px;">
                                    <strong>INVOICE NO:</strong> {{ doc_number_display($invoice->invoice_no, $general_setting->invoice_prefix ?? 'INV') }}
                                </div>
                                <div style="margin-bottom: 10px;">
                                    <strong>DATE:</strong> {{ date($general_setting->date_format, strtotime($invoice->created_at)) }}
                                </div>
                                <div style="margin-bottom: 10px;">
                                    <strong>TERMS:</strong> {{ strtoupper($invoice->terms ?? $invoice->payment_method ?? 'CASH') }}
                                    @if($invoice->payment_details)
                                    <br><small>{{ $invoice->payment_details }}</small>
                                    @endif
                                </div>
                                @php
                                    // Get D.O from invoice directly OR from quotation
                                    $do_no = '';
                                    if($invoice->deliveryOrder) {
                                        $do_no = $invoice->deliveryOrder->delivery_order_no;
                                    } elseif($invoice->quotation) {
                                        $relatedDO = \App\Models\DeliveryOrder::where('quotation_id', $invoice->quotation_id)->first();
                                        $do_no = $relatedDO ? $relatedDO->delivery_order_no : '';
                                    }
                                @endphp
                                @if($invoice->po_number || (isset($related_purchase) && $related_purchase))
                                <div style="margin-bottom: 10px;">
                                    <strong>P.O NO:</strong> {{ doc_number_display($invoice->po_number ?? ($related_purchase ? $related_purchase->reference_no : ''), $general_setting->purchase_order_prefix ?? 'PO') }}
                                </div>
                                @endif
                                @if($do_no)
                                <div style="margin-bottom: 10px;">
                                    <strong>D.O NO:</strong> {{ doc_number_display($do_no, $general_setting->delivery_order_prefix ?? 'DO') }}
                                </div>
                                @endif
                                <div style="margin-bottom: 10px;">
                                    <strong>QUO NO:</strong> {{ doc_number_display($invoice->quotation_number ?? ($invoice->quotation ? $invoice->quotation->reference_no : ''), $general_setting->quotation_prefix ?? 'QTN') }}
                                </div>
                                <div>
                                    <strong>SALESPERSON:</strong> {{ $invoice->user->name ?? '' }}
                                </div>
                            </div>
                        </div>

                        <!-- Products Table -->
                        <table style="width: 100%; border-collapse: collapse; margin-bottom: 0;">
                            <thead>
                                <tr style="background-color: #f0f0f0;">
                                    <th style="border: 1px solid #000; padding: 8px; text-align: center; width: 5%;">NO.</th>
                                    <th style="border: 1px solid #000; padding: 8px; text-align: left; width: 12%;">ITEM CODE</th>
                                    <th style="border: 1px solid #000; padding: 8px; text-align: left; width: 38%;">DESCRIPTION</th>
                                    <th style="border: 1px solid #000; padding: 8px; text-align: center; width: 10%;">QTY</th>
                                    <th style="border: 1px solid #000; padding: 8px; text-align: right; width: 12%;">PRICE (RM)</th>
                                    <th style="border: 1px solid #000; padding: 8px; text-align: right; width: 11%;">DISCOUNT(RM)</th>
                                    <th style="border: 1px solid #000; padding: 8px; text-align: right; width: 12%;">TOTAL(RM)</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($invoice->products as $key => $product)
                                <tr>
                                    <td style="border: 1px solid #000; padding: 8px; text-align: center;">{{ $key + 1 }}</td>
                                    <td style="border: 1px solid #000; padding: 8px;">{{ optional($product->product)->code ?? '' }}</td>
                                    <td style="border: 1px solid #000; padding: 8px;">{{ optional($product->product)->name ?? 'N/A' }}</td>
                                    <td style="border: 1px solid #000; padding: 8px; text-align: center;">{{ $product->qty }} {{ optional($product->unit)->unit_code ?? '' }}</td>
                                    <td style="border: 1px solid #000; padding: 8px; text-align: right;">{{ number_format($product->net_unit_price, $general_setting->decimal, '.', '') }}</td>
                                    <td style="border: 1px solid #000; padding: 8px; text-align: right;">{{ number_format($product->discount, $general_setting->decimal, '.', '') }}</td>
                                    <td style="border: 1px solid #000; padding: 8px; text-align: right;">{{ number_format($product->total, $general_setting->decimal, '.', '') }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <!-- Total Row with colspan -->
                                <tr>
                                    <td colspan="6" style="border: 1px solid #000; padding: 8px; text-align: right; font-weight: bold;">TOTAL (RM)</td>
                                    <td style="border: 1px solid #000; padding: 8px; text-align: right; font-weight: bold; font-size: 16px;">{{ number_format($invoice->grand_total, $general_setting->decimal, '.', ',') }}</td>
                                </tr>
                            </tfoot>
                        </table>

                        @if($invoice->shipping_cost > 0)
                        <div style="text-align: right; margin-bottom: 10px;">
                            <strong>TRANSPORT/SHIPPING COST (RM):</strong> {{ number_format($invoice->shipping_cost, $general_setting->decimal, '.', ',') }}
                        </div>
                        @endif

                        <!-- Total Amount in Words - Outside Table (smaller font) -->
                        <div style="margin: 15px 0; font-size: 11px;">
                            <strong>Total Amount in Words:</strong><br>
                            <span style="text-transform: uppercase;">RINGGIT MALAYSIA: {{ numberToWords(round($invoice->grand_total)) }} ONLY</span>
                        </div>

                        <!-- Payment Instructions -->
                        <div style="margin-bottom: 20px; font-size: 12px;">
                            <p><strong>Payment by cheque should be made payable to {{ $general_setting->company_name }}</strong><br>
                            <strong>{{ $general_setting->bank_details ?? '' }}</strong></p>
                        </div>

                        <!-- Terms & Conditions -->
                        <div style="margin-bottom: 20px; font-size: 12px;">
                            <strong>Terms & Conditions:</strong><br>
                            {!! nl2br(e($invoice->terms_conditions ?? "*Goods sold are not returnable")) !!}
                        </div>

                        <!-- Signature Blocks -->
                        <div style="display: flex; justify-content: space-between; margin-top: 40px;">
                            <div style="flex: 1; text-align: left;">
                                <p><strong>FOR {{ strtoupper($invoice->biller->company_name ?? $invoice->biller->name ?? $general_setting->company_name) }}</strong></p>
                                <br><br><br>
                                <p style="border-top: 1px solid #000; padding-top: 5px; width: 200px;">Authorised Signature</p>
                            </div>
                            <div style="flex: 1; text-align: right;">
                                <p><strong>RECEIVED BY</strong></p>
                                <br><br><br>
                                <p style="border-top: 1px solid #000; padding-top: 5px; width: 200px; margin-left: auto;">Customer Signature & Stamp</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

@endsection

@push('scripts')
<script type="text/javascript">
    $("ul#invoice").siblings('a').attr('aria-expanded','true');
    $("ul#invoice").addClass("show");
    $("ul#invoice #invoice-menu").addClass("active");
    
    function printInvoice() {
        var divContents = document.getElementById("invoice-print-view").innerHTML;
        var a = window.open('');
        a.document.write('<html><head><title>Invoice #{{ doc_number_display($invoice->invoice_no, $general_setting->invoice_prefix ?? 'INV') }}</title>');
        a.document.write('<style>');
        a.document.write('* { margin: 0; padding: 0; box-sizing: border-box; }');
        a.document.write('body { font-family: Arial, sans-serif; font-size: 12px; padding: 20px; }');
        a.document.write('.d-print-none { display: none !important; }');
        a.document.write('table { width: 100%; border-collapse: collapse; margin: 20px 0; }');
        a.document.write('th, td { border: 1px solid #000; padding: 8px; }');
        a.document.write('th { background-color: #f0f0f0; text-align: left; font-weight: bold; }');
        a.document.write('td { text-align: left; }');
        a.document.write('@media print { @page { margin: 1cm; } body { padding: 0; } }');
        a.document.write('</style></head><body>');
        a.document.write(divContents);
        a.document.write('</body></html>');
        a.document.close();
        setTimeout(function(){a.print();}, 250);
    }
</script>
@endpush

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
            if ($chunk > 0) {
                $chunkWords .= $ones[$chunk] . ' ';
            }
            $chunkWords = trim($chunkWords);
            if ($scaleIndex > 0) {
                $chunkWords .= ' ' . $scales[$scaleIndex];
            }
            array_unshift($parts, $chunkWords);
        }
        $num = floor($num / 1000);
        $scaleIndex++;
    }
    
    return implode(' ', $parts);
}
@endphp
