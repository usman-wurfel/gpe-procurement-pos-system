@extends('backend.layout.main') @section('content')

<x-success-message key="message" />
<x-error-message key="not_permitted" />

<section>
    <div class="container-fluid">
        <div class="card">
            <div class="card-header mt-2">
                <h3 class="text-center">Invoice List</h3>
            </div>
            {!! Form::open(['route' => 'invoices.index', 'method' => 'get']) !!}
            <div class="row mb-3">
                <div class="col-md-2 offset-md-1 mt-3">
                    <div class="form-group row">
                        <label class="d-tc mt-2"><strong>{{__('db.Choose Your Date')}}</strong> &nbsp;</label>
                        <div class="d-tc">
                            <div class="input-group">
                                <input type="text" class="daterangepicker-field form-control" value="{{$starting_date}} To {{$ending_date}}" required />
                                <input type="hidden" name="starting_date" value="{{$starting_date}}" />
                                <input type="hidden" name="ending_date" value="{{$ending_date}}" />
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-2 mt-3 @if(\Auth::user()->role_id > 2){{'d-none'}}@endif">
                    <div class="form-group row">
                        <label class="d-tc mt-2"><strong>{{__('db.Warehouse')}}</strong> &nbsp;</label>
                        <div class="d-tc">
                            <select id="warehouse_id" name="warehouse_id" class="selectpicker form-control" data-live-search="true" data-live-search-style="begins">
                                <option value="0">{{__('db.All')}}</option>
                                @foreach($lims_warehouse_list as $warehouse)
                                    <option value="{{$warehouse->id}}" {{$warehouse->id == $warehouse_id ? 'selected' : ''}}>{{$warehouse->name}}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
                <div class="col-md-2 mt-3">
                    <div class="form-group row">
                        <label class="d-tc mt-2"><strong>Status</strong> &nbsp;</label>
                        <div class="d-tc">
                            <select id="status" name="status" class="selectpicker form-control">
                                <option value="">{{__('db.All')}}</option>
                                <option value="pending" {{$status == 'pending' ? 'selected' : ''}}>Pending</option>
                                <option value="submitted" {{$status == 'submitted' ? 'selected' : ''}}>Submitted</option>
                                <option value="completed" {{$status == 'completed' ? 'selected' : ''}}>Completed</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="col-md-2 mt-3">
                    <div class="form-group row">
                        <label class="d-tc mt-2"><strong>Payment</strong> &nbsp;</label>
                        <div class="d-tc">
                            <select id="payment_status" name="payment_status" class="selectpicker form-control">
                                <option value="">{{__('db.All')}}</option>
                                <option value="unpaid" {{$payment_status == 'unpaid' ? 'selected' : ''}}>Unpaid</option>
                                <option value="partial" {{$payment_status == 'partial' ? 'selected' : ''}}>Partial</option>
                                <option value="paid" {{$payment_status == 'paid' ? 'selected' : ''}}>Paid</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="col-md-2 mt-3">
                    <div class="form-group">
                        <button class="btn btn-primary" id="filter-btn" type="submit">{{__('db.submit')}}</button>
                    </div>
                </div>
            </div>
            {!! Form::close() !!}
        </div>
        @if(in_array("invoices-add", $all_permission))
            <a href="{{route('invoices.create')}}" class="btn btn-info"><i class="dripicons-plus"></i> Add Invoice</a>&nbsp;
        @endif
    </div>
    <div class="table-responsive">
        <table id="invoice-table" class="table" style="width: 100%">
            <thead>
                <tr>
                    <th class="not-exported"></th>
                    <th>{{__('db.date')}}</th>
                    <th>Invoice No</th>
                    <th>{{__('db.Biller')}}</th>
                    <th>{{__('db.customer')}}</th>
                    <th>{{__('db.grand total')}}</th>
                    <th>Paid</th>
                    <th>Due</th>
                    <th>Status</th>
                    <th>Payment Status</th>
                    <th class="not-exported">{{__('db.action')}}</th>
                </tr>
            </thead>
            <tfoot class="tfoot active">
                <th></th>
                <th>{{__('db.Total')}}</th>
                <th></th>
                <th></th>
                <th></th>
                <th></th>
                <th></th>
                <th></th>
                <th></th>
                <th></th>
                <th></th>
            </tfoot>
        </table>
    </div>
</section>

<!-- Invoice View/Print Modal -->
<div id="invoice-details" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true" class="modal fade text-left">
    <div role="document" class="modal-dialog modal-dialog-centered modal-lg">
      <div class="modal-content">
        <div class="modal-header d-print-none">
            <div class="row w-100">
                <div class="col-md-6">
                    <h5 id="exampleModalLabel" class="modal-title">Invoice Details</h5>
                </div>
                <div class="col-md-6 text-right">
                    <button type="button" id="print-btn" class="btn btn-default btn-sm"><i class="dripicons-print"></i> {{__('db.Print')}}</button>
                    <button type="button" id="close-btn" data-dismiss="modal" aria-label="Close" class="close"><span aria-hidden="true"><i class="dripicons-cross"></i></span></button>
                </div>
            </div>
        </div>
        
        <!-- Invoice Print View -->
        <div class="modal-body" id="invoice-print-view" style="padding: 20px; font-family: Arial, sans-serif;">
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
                        <strong>TO:</strong><br>
                        <div id="customer-info" style="margin-top: 5px;">
                            <!-- Customer info will be populated by JavaScript -->
                        </div>
                    </div>
                    <div style="margin-bottom: 10px;">
                        <strong>ATTN:</strong> <span id="customer-attn"></span>
                    </div>
                    <div style="margin-bottom: 10px;">
                        <strong>TEL:</strong> <span id="customer-tel"></span>
                    </div>
                    <div>
                        <strong>Delivery To:</strong> <span id="delivery-to"></span>
                    </div>
                </div>
                <div style="flex: 1; text-align: right;">
                    <div style="margin-bottom: 10px;">
                        <strong>Page No:</strong> <span id="page-no">1 / 1</span>
                    </div>
                    <div style="margin-bottom: 10px;">
                        <strong>Invoice No:</strong> <span id="invoice-no"></span>
                    </div>
                    <div style="margin-bottom: 10px;">
                        <strong>Date:</strong> <span id="invoice-date"></span>
                    </div>
                    <div style="margin-bottom: 10px;">
                        <strong>Terms:</strong> <span id="invoice-terms"></span>
                    </div>
                    <div style="margin-bottom: 10px;">
                        <strong>P.O No:</strong> <span id="po-no"></span>
                    </div>
                    <div style="margin-bottom: 10px;">
                        <strong>D.O No:</strong> <span id="do-no"></span>
                    </div>
                    <div style="margin-bottom: 10px;">
                        <strong>QUO No:</strong> <span id="quo-no"></span>
                    </div>
                    <div>
                        <strong>Salesperson:</strong> <span id="salesperson"></span>
                    </div>
                </div>
            </div>

            <!-- Products Table -->
            <table class="table table-bordered product-invoice-list" style="width: 100%; border-collapse: collapse; margin-bottom: 20px;">
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
                </tbody>
            </table>

            <!-- Total Amount in Words (smaller font) -->
            <div style="margin-bottom: 20px; font-size: 11px;">
                <strong>Total Amount in Words:</strong><br>
                <span id="total-in-words" style="text-transform: uppercase;"></span>
            </div>

            <!-- Total Box -->
            <div style="text-align: right; margin-bottom: 20px;">
                <div style="display: inline-block; border: 2px solid #000; padding: 10px 20px; font-size: 18px; font-weight: bold;">
                    <div>TOTAL (RM)</div>
                    <div id="grand-total" style="font-size: 24px;"></div>
                </div>
            </div>

            <!-- Payment Instructions -->
            <div style="margin-bottom: 20px; font-size: 12px;">
                <p><strong>Payment Instructions:</strong> Payment by cheque should be made payable to <span id="company-name-payment"></span></p>
                <p><strong>Bank Details:</strong> <span id="bank-details"></span></p>
            </div>

            <!-- Notes -->
            <div style="margin-bottom: 20px; font-size: 12px;">
                <strong>Notes:</strong>
                <div id="invoice-notes" style="margin-top: 5px;">
                    *Goods sold are not returnable
                </div>
            </div>

            <!-- Signature Blocks -->
            <div style="display: flex; justify-content: space-between; margin-top: 40px;">
                <div style="flex: 1; text-align: left;">
                    <p><strong>FOR <span id="company-name-signature"></span></strong></p>
                    <p style="margin-top: 50px; border-top: 1px solid #000; padding-top: 5px; width: 200px;">Authorised Signature</p>
                </div>
                <div style="flex: 1; text-align: right;">
                    <p><strong>RECEIVED BY</strong></p>
                    <p style="margin-top: 50px; border-top: 1px solid #000; padding-top: 5px; width: 200px; margin-left: auto;">Customer Signature & Stamp</p>
                </div>
            </div>
        </div>
      </div>
    </div>
</div>

@endsection

@push('scripts')
<script type="text/javascript">
    $("ul#invoice").siblings('a').attr('aria-expanded','true');
    $("ul#invoice").addClass("show");
    $("ul#invoice #invoice-menu").addClass("active");

    var all_permission = <?php echo json_encode($all_permission); ?>;
    var invoice_id = [];

    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    function confirmDelete() {
        if (confirm("Are you sure want to delete?")) {
            return true;
        }
        return false;
    }

    var starting_date = $("input[name=starting_date]").val();
    var ending_date = $("input[name=ending_date]").val();
    var warehouse_id = $("#warehouse_id").val();
    var status = $("#status").val();
    var payment_status = $("#payment_status").val();

    $('#invoice-table').DataTable({
        "processing": true,
        "serverSide": true,
        "ajax": {
            url: "{{ route('invoices.data') }}",
            type: "post",
            data: {
                starting_date: starting_date,
                ending_date: ending_date,
                warehouse_id: warehouse_id,
                status: status,
                payment_status: payment_status
            }
        },
        "createdRow": function(row, data, dataIndex) {
            $(row).addClass('invoice-link');
            $(row).attr('data-invoice', JSON.stringify(data));
        },
        "columns": [
            {"data": "key"},
            {"data": "date"},
            {"data": "invoice_no"},
            {"data": "biller"},
            {"data": "customer"},
            {"data": "grand_total"},
            {"data": "paid_amount"},
            {"data": "due"},
            {"data": "status"},
            {"data": "payment_status"},
            {"data": "options"},
        ],
        'language': {
            'lengthMenu': '_MENU_ {{__("db.records per page")}}',
            "info": '<small>{{__("db.Showing")}} _START_ - _END_ (_TOTAL_)</small>',
            "search": '{{__("db.Search")}}',
            'paginate': {
                'previous': '<i class="dripicons-chevron-left"></i>',
                'next': '<i class="dripicons-chevron-right"></i>'
            }
        },
        order: [[1, 'desc']],
        'columnDefs': [
            {
                "orderable": false,
                'targets': [0, 10]
            },
            {
                'render': function(data, type, row, meta) {
                    if (type === 'display') {
                        data = '<div class="checkbox"><input type="checkbox" class="dt-checkboxes"><label></label></div>';
                    }
                    return data;
                },
                'checkboxes': {
                    'selectRow': true,
                    'selectAllRender': '<div class="checkbox"><input type="checkbox" class="dt-checkboxes"><label></label></div>'
                },
                'targets': [0]
            }
        ],
        'select': {style: 'multi', selector: 'td:first-child'},
        'lengthMenu': [[10, 25, 50, -1], [10, 25, 50, "All"]],
        dom: '<"row"lfB>rtip',
        buttons: [
            {
                extend: 'pdf',
                text: '<i title="export to pdf" class="fa fa-file-pdf-o"></i>',
                exportOptions: {
                    columns: ':visible:Not(.not-exported)',
                    rows: ':visible'
                },
                footer: true
            },
            {
                extend: 'excel',
                text: '<i title="export to excel" class="dripicons-document-new"></i>',
                exportOptions: {
                    columns: ':visible:Not(.not-exported)',
                    rows: ':visible'
                },
                footer: true
            },
            {
                extend: 'csv',
                text: '<i title="export to csv" class="fa fa-file-text-o"></i>',
                exportOptions: {
                    columns: ':visible:Not(.not-exported)',
                    rows: ':visible'
                },
                footer: true
            },
            {
                extend: 'print',
                text: '<i title="print" class="fa fa-print"></i>',
                exportOptions: {
                    columns: ':visible:Not(.not-exported)',
                    rows: ':visible'
                },
                footer: true
            },
            {
                extend: 'colvis',
                text: '<i title="column visibility" class="fa fa-eye"></i>',
                columns: ':gt(0)'
            },
        ],
    });

    $('.daterangepicker-field').daterangepicker({
        callback: function(startDate, endDate, period) {
            var starting_date = startDate.format('YYYY-MM-DD');
            var ending_date = endDate.format('YYYY-MM-DD');
            var title = starting_date + ' To ' + ending_date;
            $(this).val(title);
            $('input[name="starting_date"]').val(starting_date);
            $('input[name="ending_date"]').val(ending_date);
        }
    });

    // Click on row to view invoice
    $(document).on("click", "tr.invoice-link td:not(:first-child, :last-child)", function() {
        var invoiceData = $(this).parent().data('invoice');
        if(typeof invoiceData === 'string') {
            invoiceData = JSON.parse(invoiceData);
        }
        invoiceDetails(invoiceData.id);
    });

    // Print button
    $("#print-btn").on("click", function(){
        var divContents = document.getElementById("invoice-print-view").innerHTML;
        var a = window.open('');
        a.document.write('<html><head><title>Invoice</title>');
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
    });

    function invoiceDetails(invoiceId) {
        $.get('{{ url("invoices") }}/' + invoiceId + '/details', function(data) {
            var invoice = data.invoice;
            var products = data.products;
            var biller = data.biller;
            var customer = data.customer;
            
            // Populate company info (from biller)
            var companyName = biller.company_name || biller.name || '{{$general_setting->company_name}}';
            var companyInfo = '<strong>' + companyName + '</strong>';
            @if($general_setting->vat_registration_number)
            companyInfo += ' ({{$general_setting->vat_registration_number}})';
            @endif
            companyInfo += '<br>';
            if(biller.address) companyInfo += biller.address + '<br>';
            if(biller.city) companyInfo += biller.city + '<br>';
            companyInfo += 'TEL: ' + (biller.phone_number || '{{$general_setting->phone ?? ""}}');
            $('#company-info').html(companyInfo);
            $('#company-name-payment').text(companyName);
            $('#company-name-signature').text(companyName);
            
            // Populate customer info
            var customerInfo = '<strong>' + (customer.name || '') + '</strong><br>';
            if(customer.address) customerInfo += customer.address + '<br>';
            if(customer.city) customerInfo += customer.city + '<br>';
            $('#customer-info').html(customerInfo);
            $('#customer-attn').text(customer.name || '');
            $('#customer-tel').text(customer.phone_number || '');
            $('#delivery-to').text(customer.address || '');
            
            // Populate invoice details
            $('#invoice-no').text(invoice.invoice_no || '');
            $('#invoice-date').text(invoice.date || '');
            $('#invoice-terms').text(invoice.payment_status ? invoice.payment_status.toUpperCase() : 'CASH');
            $('#po-no').text(invoice.po_no || '');
            $('#do-no').text(invoice.delivery_order_no || '');
            $('#quo-no').text(invoice.quotation_no || '');
            $('#salesperson').text(invoice.salesperson || '');
            
            // Notes
            if(invoice.note) {
                $('#invoice-notes').html(invoice.note + '<br>*Goods sold are not returnable');
            }
            
            // Populate products table
            $(".product-invoice-list tbody").empty();
            var newBody = $("<tbody>");
            
            $.each(products, function(index, product) {
                var newRow = $("<tr>");
                var cols = '';
                cols += '<td style="border: 1px solid #000; padding: 8px; text-align: center;">' + (index + 1) + '</td>';
                cols += '<td style="border: 1px solid #000; padding: 8px;">' + (product.code || '') + '</td>';
                cols += '<td style="border: 1px solid #000; padding: 8px;">' + (product.name || '') + '</td>';
                cols += '<td style="border: 1px solid #000; padding: 8px; text-align: center;">' + product.qty + ' ' + (product.unit || '') + '</td>';
                cols += '<td style="border: 1px solid #000; padding: 8px; text-align: right;">' + parseFloat(product.net_unit_price).toLocaleString('en-US', {minimumFractionDigits: {{$general_setting->decimal}}, maximumFractionDigits: {{$general_setting->decimal}}}) + '</td>';
                cols += '<td style="border: 1px solid #000; padding: 8px; text-align: right;">' + parseFloat(product.discount || 0).toLocaleString('en-US', {minimumFractionDigits: {{$general_setting->decimal}}, maximumFractionDigits: {{$general_setting->decimal}}}) + '</td>';
                cols += '<td style="border: 1px solid #000; padding: 8px; text-align: right;">' + parseFloat(product.total).toLocaleString('en-US', {minimumFractionDigits: {{$general_setting->decimal}}, maximumFractionDigits: {{$general_setting->decimal}}}) + '</td>';
                newRow.append(cols);
                newBody.append(newRow);
            });
            
            $("table.product-invoice-list").append(newBody);
            
            // Populate totals
            var grandTotal = parseFloat(invoice.grand_total);
            $('#grand-total').text(grandTotal.toFixed({{$general_setting->decimal}}).replace(/\B(?=(\d{3})+(?!\d))/g, ","));
            
            // Amount in words
            var roundedTotal = Math.round(grandTotal);
            var amountInWords = numberToWords(roundedTotal);
            $('#total-in-words').text('RINGGIT MALAYSIA: ' + amountInWords + ' ONLY');
            
            // Bank details
            $('#bank-details').text('{{ $general_setting->bank_details ?? "" }}');
            
            $('#invoice-details').modal('show');
        });
    }
    
    // Number to words converter
    function numberToWords(num) {
        var ones = ['', 'ONE', 'TWO', 'THREE', 'FOUR', 'FIVE', 'SIX', 'SEVEN', 'EIGHT', 'NINE', 'TEN', 
                   'ELEVEN', 'TWELVE', 'THIRTEEN', 'FOURTEEN', 'FIFTEEN', 'SIXTEEN', 'SEVENTEEN', 'EIGHTEEN', 'NINETEEN'];
        var tens = ['', '', 'TWENTY', 'THIRTY', 'FORTY', 'FIFTY', 'SIXTY', 'SEVENTY', 'EIGHTY', 'NINETY'];
        var scales = ['', 'THOUSAND', 'MILLION', 'BILLION'];
        
        if (num === 0) return 'ZERO';
        
        function convertHundreds(n) {
            var result = '';
            if (n >= 100) {
                result += ones[Math.floor(n / 100)] + ' HUNDRED ';
                n %= 100;
            }
            if (n >= 20) {
                result += tens[Math.floor(n / 10)] + ' ';
                n %= 10;
            }
            if (n > 0) {
                result += ones[n] + ' ';
            }
            return result;
        }
        
        var parts = [];
        var scaleIndex = 0;
        while (num > 0) {
            var chunk = num % 1000;
            if (chunk > 0) {
                var chunkWords = convertHundreds(chunk).trim();
                if (scaleIndex > 0) {
                    chunkWords += ' ' + scales[scaleIndex];
                }
                parts.unshift(chunkWords);
            }
            num = Math.floor(num / 1000);
            scaleIndex++;
        }
        
        return parts.join(' ').trim();
    }
</script>
@endpush
