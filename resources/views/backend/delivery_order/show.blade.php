@extends('backend.layout.main') @section('content')

<section class="forms">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between d-print-none">
                        <h4>Delivery Order #{{ doc_number_display($delivery_order->delivery_order_no, $general_setting->delivery_order_prefix ?? 'DO') }}</h4>
                        <div>
                            @if($delivery_order->status != 'completed')
                            <a href="{{route('delivery-orders.edit', $delivery_order->id)}}" class="btn btn-primary btn-sm"><i class="fa fa-edit"></i> {{__('db.edit')}}</a>
                            @endif
                            <button onclick="printDeliveryOrder()" class="btn btn-default btn-sm"><i class="fa fa-print"></i> {{__('db.Print')}}</button>
                            <a href="{{ route('delivery-orders.pdf', $delivery_order->id) }}" class="btn btn-default btn-sm" target="_blank"><i class="fa fa-file-pdf-o"></i> PDF</a>
                            <a href="{{route('delivery-orders.index')}}" class="btn btn-secondary btn-sm">{{__('db.Back')}}</a>
                        </div>
                    </div>
                    
                    <!-- Delivery Order Print View -->
                    <div class="card-body" id="delivery-order-print-view" style="padding: 20px; font-family: Arial, sans-serif;">
                        @include('backend.delivery_order.partials.print_content')
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

@endsection

@push('scripts')
<script type="text/javascript">
    $("ul#delivery-order").siblings('a').attr('aria-expanded','true');
    $("ul#delivery-order").addClass("show");
    $("ul#delivery-order #delivery-order-menu").addClass("active");
    
    function printDeliveryOrder() {
        var divContents = document.getElementById("delivery-order-print-view").innerHTML;
        var a = window.open('');
        a.document.write('<html><head><title>Delivery Order #{{ doc_number_display($delivery_order->delivery_order_no, $general_setting->delivery_order_prefix ?? 'DO') }}</title>');
        a.document.write('<style>');
        a.document.write('* { margin: 0; padding: 0; box-sizing: border-box; }');
        a.document.write('body { font-family: Arial, sans-serif; font-size: 12px; padding: 20px; }');
        a.document.write('.d-print-none { display: none !important; }');
        a.document.write('table { width: 100%; border-collapse: collapse; margin: 20px 0; font-size: 12px; }');
        a.document.write('th, td { border: 1px solid #000; padding: 8px; }');
        a.document.write('table.print-no-border th, table.print-no-border td { border: none !important; }');
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
