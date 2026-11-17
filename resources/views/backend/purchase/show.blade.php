@extends('backend.layout.main') @section('content')

<section class="forms">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between d-print-none">
                        <h4>Purchase Order #{{ doc_number_display($purchase->reference_no, $general_setting->purchase_order_prefix ?? 'PO') }}</h4>
                        <div>
                            <a href="{{route('purchases.edit', $purchase->id)}}" class="btn btn-primary btn-sm"><i class="fa fa-edit"></i> {{__('db.edit')}}</a>
                            <button onclick="printPurchase()" class="btn btn-default btn-sm"><i class="fa fa-print"></i> {{__('db.Print')}}</button>
                            <a href="{{ route('purchases.pdf', $purchase->id) }}" class="btn btn-default btn-sm" target="_blank"><i class="fa fa-file-pdf-o"></i> PDF</a>
                            <a href="{{route('purchases.index')}}" class="btn btn-secondary btn-sm">{{__('db.Back')}}</a>
                        </div>
                    </div>
                    
                    <!-- Purchase Print View -->
                    <div class="card-body" id="purchase-print-view" style="padding: 20px; font-family: Arial, sans-serif;">
                        <!-- Header with Logo, Title and Company Info -->
                        <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 20px; border-bottom: 2px solid #000; padding-bottom: 15px;">
                            <div style="flex: 1; text-align: left;">
                                <img src="{{ $general_setting->invoice_logo ? url('logo', $general_setting->invoice_logo) : url('logo', $general_setting->site_logo) }}" alt="Logo" style="max-height: 120px;">
                            </div>
                            <div style="flex: 1; text-align: center; padding-top: 20px;">
                                <h2 style="margin: 0; font-size: 28px; font-weight: bold; text-transform: uppercase;">PURCHASE ORDER</h2>
                            </div>
                            <div style="flex: 1; text-align: right; font-size: 11px; line-height: 1.5;">
                                @if($purchase->warehouse)
                                <strong style="font-size: 14px;">{{ $purchase->warehouse->name }}</strong><br>
                                @if($purchase->warehouse->address)
                                {!! nl2br(e($purchase->warehouse->address)) !!}<br>
                                @endif
                                @if($purchase->warehouse->phone)
                                <strong>TEL: {{ $purchase->warehouse->phone }}</strong>
                                @endif
                                @if($purchase->warehouse->email)
                                <br><strong>EMAIL: {{ $purchase->warehouse->email }}</strong>
                                @endif
                                @else
                                <strong style="font-size: 14px;">{{ $general_setting->company_name ?? 'GLOBAL PRACTISE ENTERPRISE' }}</strong><br>
                                3050, JALAN AIR PUTIH,<br>
                                TAMAN MOHD NOOR, CUKAI,<br>
                                24000 KEMAMAN, TERENGGANU.<br>
                                <strong>TEL: 0111018081</strong>
                                @endif
                            </div>
                        </div>


                        <!-- Supplier and Purchase Details -->
                        <div style="display: flex; justify-content: space-between; margin-bottom: 20px;">
                            <div style="flex: 1; padding-right: 20px;">
                                <div style="margin-bottom: 15px;">
                                    <strong>SUPPLIER :-</strong><br>
                                    <div style="margin-top: 5px;">
                                        <strong>{{ $purchase->supplier->name ?? '' }}</strong><br>
                                        @if($purchase->supplier && $purchase->supplier->company_name)
                                        {{ $purchase->supplier->company_name }}<br>
                                        @endif
                                        @if($purchase->supplier && $purchase->supplier->address)
                                        {{ $purchase->supplier->address }}<br>
                                        @endif
                                        @if($purchase->supplier && $purchase->supplier->city)
                                        {{ $purchase->supplier->city }}<br>
                                        @endif
                                    </div>
                                </div>
                                <div style="margin-bottom: 10px;">
                                    <strong>CONTACT:</strong> {{ $purchase->supplier->name ?? '' }}
                                </div>
                                <div style="margin-bottom: 10px;">
                                    <strong>TEL:</strong> {{ $purchase->supplier->phone_number ?? '' }}
                                </div>
                                @if($purchase->supplier && $purchase->supplier->email)
                                <div style="margin-bottom: 10px;">
                                    <strong>EMAIL:</strong> {{ $purchase->supplier->email }}
                                </div>
                                @endif
                            </div>
                            <div style="flex: 1;">
                                <strong>DELIVER TO :-</strong><br>
                                <div style="margin-top: 5px;">
                                    @if($purchase->delivery_address)
                                        {!! nl2br(e($purchase->delivery_address)) !!}
                                    @else
                                        <strong>{{ $purchase->warehouse->name ?? '' }}</strong><br>
                                        {{ $purchase->warehouse->address ?? '' }}<br>
                                        TEL: {{ $purchase->warehouse->phone ?? '' }}
                                    @endif
                                </div>
                            </div>
                            <div style="flex: 1; text-align: right;">
                                <div style="margin-bottom: 10px;">
                                    <strong>PAGE NO:</strong> 1 / 1
                                </div>
                                <div style="margin-bottom: 10px;">
                                    <strong style="font-size: 16px;">P.O NO:</strong> <span style="font-size: 16px; font-weight: bold;">{{ doc_number_display($purchase->reference_no, $general_setting->purchase_order_prefix ?? 'PO') }}</span>
                                </div>
                                <div style="margin-bottom: 10px;">
                                    <strong>DATE:</strong> {{ date($general_setting->date_format, strtotime($purchase->created_at)) }}
                                </div>
                                <div style="margin-bottom: 10px;">
                                    <strong>TERMS:</strong> {{ strtoupper($purchase->terms ?? 'CASH') }}
                                </div>
                                <div style="margin-bottom: 10px;" class="d-print-none">
                                    <strong>STATUS:</strong> 
                                    @if($purchase->status == 1)
                                        <span style="color: #28a745;">Received</span>
                                    @elseif($purchase->status == 2)
                                        <span style="color: #ffc107;">Partial</span>
                                    @elseif($purchase->status == 3)
                                        <span style="color: #17a2b8;">Pending</span>
                                    @elseif($purchase->status == 4)
                                        <span style="color: #dc3545;">Ordered</span>
                                    @endif
                                </div>
                                <div class="d-print-none">
                                    <strong>CREATED BY:</strong> {{ $purchase->user->name ?? '' }}
                                </div>
                            </div>
                        </div>

                        <!-- Products Table -->
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
                                    <td style="border: 1px solid #000; padding: 8px; text-align: right;">{{ number_format($product->net_unit_cost, $general_setting->decimal, '.', '') }}</td>
                                    <td style="border: 1px solid #000; padding: 8px; text-align: right;">{{ number_format($product->tax, $general_setting->decimal, '.', '') }}</td>
                                    <td style="border: 1px solid #000; padding: 8px; text-align: right;">{{ number_format($product->total, $general_setting->decimal, '.', '') }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <!-- Subtotal -->
                                <tr>
                                    <td colspan="6" style="border: 1px solid #000; padding: 8px; text-align: right;">Subtotal</td>
                                    <td style="border: 1px solid #000; padding: 8px; text-align: right;">{{ number_format($purchase->total_cost, $general_setting->decimal, '.', ',') }}</td>
                                </tr>
                                <!-- Order Tax -->
                                @if($purchase->order_tax > 0)
                                <tr>
                                    <td colspan="6" style="border: 1px solid #000; padding: 8px; text-align: right;">Order Tax ({{ $purchase->order_tax_rate }}%)</td>
                                    <td style="border: 1px solid #000; padding: 8px; text-align: right;">{{ number_format($purchase->order_tax, $general_setting->decimal, '.', ',') }}</td>
                                </tr>
                                @endif
                                <!-- Order Discount -->
                                @if($purchase->order_discount > 0)
                                <tr>
                                    <td colspan="6" style="border: 1px solid #000; padding: 8px; text-align: right;">Order Discount</td>
                                    <td style="border: 1px solid #000; padding: 8px; text-align: right;">-{{ number_format($purchase->order_discount, $general_setting->decimal, '.', ',') }}</td>
                                </tr>
                                @endif
                                <!-- Grand Total Row -->
                                <tr>
                                    <td colspan="6" style="border: 1px solid #000; padding: 8px; text-align: right; font-weight: bold; background-color: #f0f0f0;">GRAND TOTAL (RM)</td>
                                    <td style="border: 1px solid #000; padding: 8px; text-align: right; font-weight: bold; font-size: 16px; background-color: #f0f0f0;">{{ number_format($purchase->grand_total, $general_setting->decimal, '.', ',') }}</td>
                                </tr>
                                <!-- Paid Amount -->
                                <tr>
                                    <td colspan="6" style="border: 1px solid #000; padding: 8px; text-align: right;">Paid Amount</td>
                                    <td style="border: 1px solid #000; padding: 8px; text-align: right; color: #28a745;">{{ number_format($purchase->paid_amount, $general_setting->decimal, '.', ',') }}</td>
                                </tr>
                                <!-- Due Amount -->
                                <tr>
                                    <td colspan="6" style="border: 1px solid #000; padding: 8px; text-align: right; font-weight: bold;">Due Amount</td>
                                    <td style="border: 1px solid #000; padding: 8px; text-align: right; font-weight: bold; color: #dc3545;">{{ number_format($purchase->grand_total - $purchase->paid_amount, $general_setting->decimal, '.', ',') }}</td>
                                </tr>
                            </tfoot>
                        </table>

                        @if($purchase->shipping_cost > 0)
                        <div style="text-align: right; margin-bottom: 10px;">
                            <strong>TRANSPORT/SHIPPING COST (RM):</strong> {{ number_format($purchase->shipping_cost, $general_setting->decimal, '.', ',') }}
                        </div>
                        @endif

                        <!-- Total Amount in Words -->
                        <div style="margin: 20px 0;">
                            <strong>RINGGIT MALAYSIA:</strong> <span style="text-transform: uppercase;">{{ numberToWords(floor($purchase->grand_total)) }} ONLY</span>
                        </div>

                        <!-- Notes -->
                        @if($purchase->note)
                        <div style="margin-bottom: 20px; font-size: 12px;">
                            <strong>Notes:</strong><br>
                            {{ $purchase->note }}
                        </div>
                        @endif

                        <!-- Payment Instructions -->
                        <div style="margin-bottom: 20px; font-size: 11px;">
                            <p><strong>Payment Instructions:</strong> Payment by cheque should be made payable to <strong>{{ $general_setting->company_name ?? 'GLOBAL PRACTISE ENTERPRISE' }}</strong></p>
                            @if($general_setting->bank_details)
                            <p><strong>Bank Details:</strong> {{ $general_setting->bank_details }}</p>
                            @else
                            <p><strong>Bank Details:</strong> MAYBANK, Account No: 563028623337</p>
                            @endif
                        </div>

                        <!-- Terms & Conditions -->
                        <div style="margin-bottom: 20px; font-size: 11px;">
                            <strong>Terms & Conditions:</strong><br>
                            {!! nl2br(e($purchase->terms_conditions ?? "Payment: " . ($purchase->terms ?? 'CASH') . "\nDelivery: As per quotation from vendor\nCancellation: Cancel after PO will be charged/claimed 100% of PO value\nRefund: Refund has to be made within 1 week")) !!}
                        </div>

                        <!-- Signature Blocks -->
                        <div style="display: flex; justify-content: space-between; margin-top: 40px;">
                            <div style="flex: 1; text-align: left;">
                                <p><strong>FOR {{ strtoupper($general_setting->company_name) }}</strong></p>
                                <br><br><br>
                                <p style="border-top: 1px solid #000; padding-top: 5px; width: 200px;">Authorised Signature</p>
                            </div>
                            <div style="flex: 1; text-align: right;">
                                <p><strong>SUPPLIER ACKNOWLEDGEMENT</strong></p>
                                <br><br><br>
                                <p style="border-top: 1px solid #000; padding-top: 5px; width: 200px; margin-left: auto;">Supplier Signature & Stamp</p>
                            </div>
                        </div>

                        <!-- Footer -->
                        <div style="margin-top: 30px; text-align: center; font-size: 10px; color: #666;">
                            <p>This is a computer-generated document. No signature is required.</p>
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
    $("ul#purchase").siblings('a').attr('aria-expanded','true');
    $("ul#purchase").addClass("show");
    $("ul#purchase #purchase-list-menu").addClass("active");
    
    function printPurchase() {
        var divContents = document.getElementById("purchase-print-view").innerHTML;
        var a = window.open('');
        a.document.write('<html><head><title>Purchase Order #{{ doc_number_display($purchase->reference_no, $general_setting->purchase_order_prefix ?? 'PO') }}</title>');
        a.document.write('<style>');
        a.document.write('* { margin: 0; padding: 0; box-sizing: border-box; }');
        a.document.write('body { font-family: Arial, sans-serif; font-size: 12px; padding: 20px; }');
        a.document.write('.d-print-none { display: none !important; }');
        a.document.write('table { width: 100%; border-collapse: collapse; margin: 20px 0; }');
        a.document.write('th, td { border: 1px solid #000; padding: 8px; }');
        a.document.write('th { background-color: #f0f0f0; text-align: left; font-weight: bold; }');
        a.document.write('td { text-align: left; }');
        a.document.write('@media print { @page { margin: 1cm; } body { padding: 0; } .d-print-none { display: none !important; } }');
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
