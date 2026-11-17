@extends('backend.layout.main') @section('content')

<x-success-message key="message" />
<x-error-message key="not_permitted" />

<section>
    <div class="container-fluid">
        <div class="card">
            <div class="card-header mt-2">
                <h3 class="text-center">{{__('db.Quotation List')}}</h3>
            </div>
            {!! Form::open(['route' => 'quotations.index', 'method' => 'get']) !!}
            <div class="row mb-3">
                <div class="col-md-4 offset-md-2 mt-3">
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
                <div class="col-md-4 mt-3 @if(\Auth::user()->role_id > 2){{'d-none'}}@endif">
                    <div class="form-group row">
                        <label class="d-tc mt-2"><strong>{{__('db.Choose Warehouse')}}</strong> &nbsp;</label>
                        <div class="d-tc">
                            <select id="warehouse_id" name="warehouse_id" class="selectpicker form-control" data-live-search="true" data-live-search-style="begins" >
                                <option value="0">{{__('db.All Warehouse')}}</option>
                                @foreach($lims_warehouse_list as $warehouse)
                                    @if($warehouse->id == $warehouse_id)
                                        <option selected value="{{$warehouse->id}}">{{$warehouse->name}}</option>
                                    @else
                                        <option value="{{$warehouse->id}}">{{$warehouse->name}}</option>
                                    @endif
                                @endforeach
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
        @if(in_array("quotes-add", $all_permission))
            <a href="{{route('quotations.create')}}" class="btn btn-info"><i class="dripicons-plus"></i> {{__('db.Add Quotation')}}</a>&nbsp;
        @endif
    </div>
    <div class="table-responsive">
        <table id="quotation-table" class="table quotation-list" style="width: 100%">
            <thead>
                <tr>
                    <th class="not-exported"></th>
                    <th>{{__('db.date')}}</th>
                    <th>{{__('db.reference')}}</th>
                    <th>{{__('db.Warehouse')}}</th>
                    <th>{{__('db.Biller')}}</th>
                    <th>{{__('db.customer')}}</th>
                    <th>{{__('db.Supplier')}}</th>
                    <th>{{__('db.Quotation Status')}}</th>
                    <th>{{__('db.grand total')}}</th>
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
            </tfoot>
        </table>
    </div>
</section>

<div id="quotation-details" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true" class="modal fade text-left">
    <div role="document" class="modal-dialog modal-xl">
      <div class="modal-content">
        <div class="container mt-3 pb-2 border-bottom d-print-none">
            <div class="row">
                <div class="col-md-6">
                    <button id="print-btn" type="button" class="btn btn-default btn-sm"><i class="dripicons-print"></i> {{__('db.Print')}}</button>
                    <a id="quotation-pdf-link" href="#" class="btn btn-default btn-sm" target="_blank"><i class="fa fa-file-pdf-o"></i> PDF</a>
                    {{ Form::open(['route' => 'quotation.sendmail', 'method' => 'post', 'class' => 'sendmail-form', 'style' => 'display:inline'] ) }}
                        <input type="hidden" name="quotation_id">
                        <button type="button" id="email-btn" class="btn btn-default btn-sm"><i class="dripicons-mail"></i> {{__('db.Email')}}</button>
                        <div id="email-input-section" style="display: none; margin-top: 5px;">
                            <input type="email" name="email" class="form-control form-control-sm" placeholder="Enter email..." style="width: 200px; display: inline-block;">
                            <button type="submit" class="btn btn-primary btn-sm">Send</button>
                        </div>
                    {{ Form::close() }}
                </div>
                <div class="col-md-6 text-right">
                    <button type="button" id="close-btn" data-dismiss="modal" aria-label="Close" class="close"><span aria-hidden="true"><i class="dripicons-cross"></i></span></button>
                </div>
            </div>
        </div>
        
        <!-- Quotation Print View -->
        <div class="modal-body" id="quotation-print-view" style="padding: 20px; font-family: Arial, sans-serif;">
            <!-- Header with Logo, Title and Company Info -->
            <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 20px; border-bottom: 2px solid #000; padding-bottom: 15px;">
                <div style="flex: 1; text-align: left;">
                    <img src="{{ $general_setting->invoice_logo ? url('logo', $general_setting->invoice_logo) : url('logo', $general_setting->site_logo) }}" alt="Logo" style="max-height: 120px;">
                </div>
                <div style="flex: 1; text-align: center; padding-top: 20px;">
                    <h2 style="margin: 0; font-size: 28px; font-weight: bold; text-transform: uppercase;">QUOTATION</h2>
                </div>
                <div id="company-info" style="flex: 1; text-align: right; font-size: 11px; line-height: 1.5;">
                    <strong style="font-size: 14px;">GLOBAL PRACTISE ENTERPRISE (CA-0260377-P)</strong><br>
                    3050, JALAN AIR PUTIH,<br>
                    TAMAN MOHD NOOR, CUKAI,<br>
                    24000 KEMAMAN, TERENGGANU.<br>
                    <strong>TEL: 0111018081.</strong>
                </div>
            </div>


            <!-- Recipient and Quotation Details -->
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
                        <strong>Ref No:</strong> <span id="ref-no"></span>
                    </div>
                    <div style="margin-bottom: 10px;">
                        <strong>Date:</strong> <span id="quotation-date"></span>
                    </div>
                    <div>
                        <strong>Terms:</strong> <span id="quotation-terms"></span>
                    </div>
                </div>
            </div>

            <!-- Products Table -->
            <table class="table table-bordered product-quotation-list" style="width: 100%; border-collapse: collapse; margin-bottom: 20px;">
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

            <!-- Shipping/Transport Cost (shown when > 0) -->
            <div id="shipping-cost-row" style="display: none; text-align: right; margin-bottom: 10px;">
                <strong>TRANSPORT/SHIPPING COST (RM):</strong> <span id="shipping-cost-amount"></span>
            </div>

            <!-- Total Amount in Words -->
            <div style="margin-bottom: 20px;">
                <strong>Total Amount in Words:</strong> <span id="total-in-words" style="text-transform: uppercase;"></span>
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

            <!-- Terms & Conditions -->
            <div style="margin-bottom: 20px; font-size: 12px;">
                <strong>Terms & Conditions:</strong>
                <ul style="margin: 5px 0; padding-left: 20px;">
                    <li><strong>Payment:</strong> <span id="payment-terms"></span></li>
                    <li><strong>Delivery:</strong> <span id="delivery-terms"></span></li>
                    <li><strong>Warranty:</strong> <span id="warranty-terms"></span></li>
                    <li><strong>Validity:</strong> <span id="validity-terms"></span></li>
                </ul>
            </div>

            <!-- Signature Blocks -->
            <div style="display: flex; justify-content: space-between; margin-top: 40px;">
                <div style="flex: 1; text-align: left;">
                    <p><strong>FOR <span id="company-name-signature"></span></strong></p>
                    <p style="margin-top: 50px; border-top: 1px solid #000; padding-top: 5px; width: 200px;">Authorised Signature</p>
                </div>
                <div style="flex: 1; text-align: right;">
                    <p><strong>We agree & accept as per above</strong></p>
                    <p style="margin-top: 50px; border-top: 1px solid #000; padding-top: 5px; width: 200px; margin-left: auto;">Customer Signature & Company Stamp</p>
                </div>
            </div>
        </div>
      </div>
    </div>
</div>

@endsection

@push('scripts')
<script type="text/javascript">

    $("ul#quotation").siblings('a').attr('aria-expanded','true');
    $("ul#quotation").addClass("show");
    $("ul#quotation #quotation-list-menu").addClass("active");

    $(".daterangepicker-field").daterangepicker({
      callback: function(startDate, endDate, period){
        var starting_date = startDate.format('YYYY-MM-DD');
        var ending_date = endDate.format('YYYY-MM-DD');
        var title = starting_date + ' To ' + ending_date;
        $(this).val(title);
        $('input[name="starting_date"]').val(starting_date);
        $('input[name="ending_date"]').val(ending_date);
      }
    });

    var all_permission = <?php echo json_encode($all_permission) ?>;
    var quotation_id = [];
    var user_verified = <?php echo json_encode(env('USER_VERIFIED')) ?>;

    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    function normalizeQuotationData(rawQuotation) {
        if (Array.isArray(rawQuotation)) {
            return rawQuotation;
        }
        if (typeof rawQuotation === 'string') {
            try {
                var parsed = JSON.parse(rawQuotation);
                if (Array.isArray(parsed)) {
                    return parsed;
                }
            } catch (e) {
                return [];
            }
        }
        return [];
    }

    function confirmDelete() {
        if (confirm("Are you sure want to delete?")) {
            return true;
        }
        return false;
    }

    $(document).on("click", "tr.quotation-link td:not(:first-child, :last-child)", function() {
        var quotation = normalizeQuotationData($(this).parent().data('quotation'));
        quotationDetails(quotation);
    });

    $(document).on("click", ".view", function() {
        var quotation = normalizeQuotationData($(this).parent().parent().parent().parent().parent().data('quotation'));
        quotationDetails(quotation);
    });

    $("#print-btn").on("click", function(){
        var divContents = document.getElementById("quotation-print-view").innerHTML;
        var a = window.open('');
        a.document.write('<html><head><title>Quotation</title>');
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

    var starting_date = $("input[name=starting_date]").val();
    var ending_date = $("input[name=ending_date]").val();
    var warehouse_id = $("#warehouse_id").val();
    $('#quotation-table').DataTable( {
        "processing": true,
        "serverSide": true,
        "ajax":{
            url:"quotations/quotation-data",
            data:{
                all_permission: all_permission,
                starting_date: starting_date,
                ending_date: ending_date,
                warehouse_id: warehouse_id
            },
            dataType: "json",
            type:"post",
            /*success:function(data){
                console.log(data);
            }*/
        },
        "createdRow": function( row, data, dataIndex ) {
            $(row).addClass('quotation-link');
            $(row).data('quotation', data['quotation']);
        },
        "columns": [
            {"data": "key"},
            {"data": "date"},
            {"data": "reference_no"},
            {"data": "warehouse"},
            {"data": "biller"},
            {"data": "customer"},
            {"data": "supplier"},
            {"data": "status"},
            {"data": "grand_total"},
            {"data": "options"},
        ],
        'language': {
            /*'searchPlaceholder': "{{__('db.Type date or quotation reference')}}",*/
            'lengthMenu': '_MENU_ {{__("db.records per page")}}',
             "info":      '<small>{{__("db.Showing")}} _START_ - _END_ (_TOTAL_)</small>',
            "search":  '{{__("db.Search")}}',
            'paginate': {
                    'previous': '<i class="dripicons-chevron-left"></i>',
                    'next': '<i class="dripicons-chevron-right"></i>'
            }
        },
        order:[['1', 'desc']],
        'columnDefs': [
            {
                "orderable": false,
                'targets': [0, 3, 4, 7, 8,9]
            },
            {
                'render': function(data, type, row, meta){
                    if(type === 'display'){
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
        'select': { style: 'multi',  selector: 'td:first-child'},
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
                action: function(e, dt, button, config) {
                    datatable_sum(dt, true);
                    $.fn.dataTable.ext.buttons.pdfHtml5.action.call(this, e, dt, button, config);
                    datatable_sum(dt, false);
                },
                footer:true
            },
            {
                extend: 'excel',
                text: '<i title="export to excel" class="dripicons-document-new"></i>',
                exportOptions: {
                    columns: ':visible:not(.not-exported)',
                    rows: ':visible'
                },
                action: function(e, dt, button, config) {
                    datatable_sum(dt, true);
                    $.fn.dataTable.ext.buttons.excelHtml5.action.call(this, e, dt, button, config);
                    datatable_sum(dt, false);
                },
                footer:true
            },
            {
                extend: 'csv',
                text: '<i title="export to csv" class="fa fa-file-text-o"></i>',
                exportOptions: {
                    columns: ':visible:not(.not-exported)',
                    rows: ':visible'
                },
                action: function(e, dt, button, config) {
                    datatable_sum(dt, true);
                    $.fn.dataTable.ext.buttons.csvHtml5.action.call(this, e, dt, button, config);
                    datatable_sum(dt, false);
                },
                footer:true
            },
            {
                extend: 'print',
                text: '<i title="print" class="fa fa-print"></i>',
                exportOptions: {
                    columns: ':visible:not(.not-exported)',
                    rows: ':visible'
                },
                action: function(e, dt, button, config) {
                    datatable_sum(dt, true);
                    $.fn.dataTable.ext.buttons.print.action.call(this, e, dt, button, config);
                    datatable_sum(dt, false);
                },
                footer:true
            },
            {
                text: '<i title="delete" class="dripicons-cross"></i>',
                className: 'buttons-delete',
                action: function ( e, dt, node, config ) {
                    if(user_verified == '1') {
                        quotation_id.length = 0;
                        $(':checkbox:checked').each(function(i){
                            if(i){
                                var quotation = normalizeQuotationData($(this).closest('tr').data('quotation'));
                                // Check if quotation data exists before accessing it
                                if(quotation && quotation[13] !== undefined) {
                                    quotation_id.push(quotation[13]);
                                }
                            }
                        });
                        if(quotation_id.length && confirm("Are you sure want to delete?")) {
                            $.ajax({
                                type:'POST',
                                url:'quotations/deletebyselection',
                                data:{
                                    quotationIdArray: quotation_id
                                },
                                success:function(data) {
                                    alert(data);
                                    //dt.rows({ page: 'current', selected: true }).deselect();
                                    dt.rows({ page: 'current', selected: true }).remove().draw(false);
                                }
                            });
                        }
                        else if(!quotation_id.length)
                            alert('Nothing is selected!');
                    }
                    else
                        alert('This feature is disable for demo!');
                }
            },
            {
                extend: 'colvis',
                text: '<i title="column visibility" class="fa fa-eye"></i>',
                columns: ':gt(0)'
            },
        ],
        drawCallback: function () {
            var api = this.api();
            datatable_sum(api, false);
        }
    } );

    function datatable_sum(dt_selector, is_calling_first) {
        if (dt_selector.rows( '.selected' ).any() && is_calling_first) {
            var rows = dt_selector.rows( '.selected' ).indexes();

            $( dt_selector.column( 8 ).footer() ).html(dt_selector.cells( rows, 8, { page: 'current' } ).data().sum().toFixed({{$general_setting->decimal}}));
        }
        else {
            $( dt_selector.column( 8 ).footer() ).html(dt_selector.cells( rows, 8, { page: 'current' } ).data().sum().toFixed({{$general_setting->decimal}}));
        }
    }

    if(all_permission.indexOf("quotes-delete") == -1)
        $('.buttons-delete').addClass('d-none');

    function quotationDetails(quotation){
        $('input[name="quotation_id"]').val(quotation[13]);
        $('#email-input-section').hide();
        
        // Populate company info (from warehouse if available, else biller)
        var strip = function(s) { return (s && typeof s === 'string') ? s.trim().replace(/^["']|["']$/g, '') : ''; };
        var warehouseName = strip(quotation[27] || '');
        var warehouseAddress = strip(quotation[28] || '');
        var warehousePhone = strip(quotation[29] || '');
        var companyName, companyInfo;
        if (warehouseName) {
            companyName = warehouseName;
            companyInfo = '<strong style="font-size: 14px;">' + companyName + '</strong><br>';
            if (warehouseAddress) companyInfo += warehouseAddress.replace(/\\n/g, '<br>') + '<br>';
            if (warehousePhone) companyInfo += '<strong>TEL: ' + warehousePhone + '</strong>';
        } else {
            companyName = quotation[4] || quotation[3] || '{{$general_setting->company_name}}';
            companyInfo = '<strong>' + companyName + '</strong><br>';
            @if($general_setting->vat_registration_number)
            companyInfo += 'Company Registration Number: {{$general_setting->vat_registration_number}}<br>';
            @endif
            var companyAddress = quotation[7] || '';
            var companyCity = quotation[8] || '';
            if(companyAddress) companyInfo += companyAddress + '<br>';
            if(companyCity) companyInfo += companyCity + '<br>';
            companyInfo += (quotation[6] || '{{$general_setting->phone ?? ""}}') + '<br>';
        }
        $('#company-info').html(companyInfo);
        $('#company-name-payment').text(companyName);
        $('#company-name-signature').text(companyName);
        $('#quotation-pdf-link').attr('href', '{{ url("quotations") }}/' + quotation[13] + '/pdf');
        
        // Populate customer info
        var customerInfo = (quotation[9] || '') + '<br>';
        customerInfo += (quotation[11] || '') + '<br>';
        customerInfo += (quotation[12] || '');
        $('#customer-info').html(customerInfo);
        $('#customer-attn').text(quotation[9] || '');
        $('#customer-tel').text(quotation[10] || '');
        $('#delivery-to').text(quotation[26] || '');
        
        // Populate quotation details
        $('#ref-no').text(quotation[1] || '');
        $('#quotation-date').text(quotation[0] || '');
        $('#quotation-terms').text((quotation[30] || 'CASH').toUpperCase());
        
        // Get products and populate table
        $.get('quotations/product_quotation/' + quotation[13], function(data){
            $(".product-quotation-list tbody").empty();
            var name_code = data[0];
            var qty = data[1];
            var unit_code = data[2];
            var tax = data[3];
            var tax_rate = data[4];
            var discount = data[5];
            var total = data[6] || []; // This is the total from ProductQuotation
            var batch_no = data[7];
            var net_unit_price = data[8] || []; // Net unit price per item
            
            var newBody = $("<tbody>");
            var grandTotal = 0;
            
            $.each(name_code, function(index){
                var productName = name_code[index];
                // Extract product code from format: "Product Name [CODE]"
                var productCode = '';
                var description = productName;
                if(productName.indexOf('[') !== -1 && productName.indexOf(']') !== -1) {
                    var codeMatch = productName.match(/\[([^\]]+)\]/);
                    if(codeMatch) {
                        productCode = codeMatch[1];
                        description = productName.replace(/\s*\[[^\]]+\]\s*/, '').trim();
                    }
                }
                
                // Get values
                var currentQty = parseFloat(qty[index] || 0);
                var currentNetUnitPrice = parseFloat(net_unit_price[index] || 0);
                var currentDiscount = parseFloat(discount[index] || 0);
                var currentTotal = parseFloat(total[index] || 0);
                
                // If total is 0 or not set, calculate it: (net_unit_price * qty) - discount
                if(currentTotal === 0 || !total[index]) {
                    currentTotal = (currentNetUnitPrice * currentQty) - currentDiscount;
                    if(currentTotal < 0) currentTotal = 0;
                }
                
                // Calculate unit price
                var unitPrice = 0;
                if(currentNetUnitPrice > 0) {
                    unitPrice = currentNetUnitPrice.toFixed({{$general_setting->decimal}});
                } else if(currentQty > 0) {
                    // Calculate unit price: (total + discount) / qty
                    unitPrice = parseFloat((currentTotal + currentDiscount) / currentQty).toFixed({{$general_setting->decimal}});
                }
                
                var totalPrice = currentTotal.toFixed({{$general_setting->decimal}});
                var discountAmount = currentDiscount.toFixed({{$general_setting->decimal}});
                
                grandTotal += currentTotal;
                
                var newRow = $("<tr>");
                var cols = '';
                cols += '<td style="border: 1px solid #000; padding: 8px; text-align: center;">' + (index+1) + '</td>';
                cols += '<td style="border: 1px solid #000; padding: 8px;">' + productCode + '</td>';
                cols += '<td style="border: 1px solid #000; padding: 8px;">' + description + '</td>';
                cols += '<td style="border: 1px solid #000; padding: 8px; text-align: center;">' + qty[index] + ' ' + unit_code[index] + '</td>';
                cols += '<td style="border: 1px solid #000; padding: 8px; text-align: right;">' + parseFloat(unitPrice).toLocaleString('en-US', {minimumFractionDigits: {{$general_setting->decimal}}, maximumFractionDigits: {{$general_setting->decimal}}}) + '</td>';
                cols += '<td style="border: 1px solid #000; padding: 8px; text-align: right;">' + parseFloat(discountAmount).toLocaleString('en-US', {minimumFractionDigits: {{$general_setting->decimal}}, maximumFractionDigits: {{$general_setting->decimal}}}) + '</td>';
                cols += '<td style="border: 1px solid #000; padding: 8px; text-align: right;">' + parseFloat(totalPrice).toLocaleString('en-US', {minimumFractionDigits: {{$general_setting->decimal}}, maximumFractionDigits: {{$general_setting->decimal}}}) + '</td>';
                newRow.append(cols);
                newBody.append(newRow);
            });
            
            // quotation[20] = shipping_cost, quotation[21] = grand_total
            var shippingCost = parseFloat(quotation[20] || 0);
            if (shippingCost > 0) {
                $('#shipping-cost-row').show();
                $('#shipping-cost-amount').text(parseFloat(shippingCost).toLocaleString('en-US', {minimumFractionDigits: {{$general_setting->decimal}}, maximumFractionDigits: {{$general_setting->decimal}}}));
            } else {
                $('#shipping-cost-row').hide();
            }
            
            // quotation[21] is already the GRAND TOTAL (includes order_tax + shipping - discount)
            var finalTotal = parseFloat(quotation[21] || grandTotal);
            
            // Round off the total for display
            var roundedTotal = Math.round(finalTotal);
            
            // Populate totals
            $('#grand-total').text(roundedTotal.toFixed(0).replace(/\B(?=(\d{3})+(?!\d))/g, ","));
            
            // Populate amount in words (using rounded amount)
            var amountInWords = numberToWords(roundedTotal);
            $('#total-in-words').text('RINGGIT MALAYSIA: ' + amountInWords + ' ONLY');
            
            var termsConditionsText = (quotation[31] || '').toString();
            var normalizedTermsText = termsConditionsText.replace(/<br\s*\/?>/gi, '\n');

            var paymentMatch = normalizedTermsText.match(/Payment:\s*(.*?)(?=\s*Delivery:|\s*Warranty:|\s*Validity:|$)/i);
            var deliveryMatch = normalizedTermsText.match(/Delivery:\s*(.*?)(?=\s*Warranty:|\s*Validity:|$)/i);
            var warrantyMatch = normalizedTermsText.match(/Warranty:\s*(.*?)(?=\s*Validity:|$)/i);
            var validityMatch = normalizedTermsText.match(/Validity:\s*(.*)$/i);

            var paymentText = paymentMatch ? paymentMatch[1].trim() : '';
            var deliveryText = deliveryMatch ? deliveryMatch[1].trim() : '';
            var warrantyText = warrantyMatch ? warrantyMatch[1].trim() : '';
            var validityText = validityMatch ? validityMatch[1].trim() : '';

            if (!paymentText && !deliveryText && !warrantyText && !validityText) {
                var termsLines = normalizedTermsText.split(/\n+/).filter(function(line) {
                    return line && line.trim() !== '';
                });
                paymentText = (termsLines[0] || '').replace(/^Payment:\s*/i, '').trim();
                deliveryText = (termsLines[1] || '').replace(/^Delivery:\s*/i, '').trim();
                warrantyText = (termsLines[2] || '').replace(/^Warranty:\s*/i, '').trim();
                validityText = (termsLines[3] || '').replace(/^Validity:\s*/i, '').trim();
            }

            if (!paymentText && !deliveryText && !warrantyText && !validityText) {
                paymentText = '30 days. 100% charge on order cancellation penalty once PO issued to GP.';
                deliveryText = '< 4 weeks';
                warrantyText = "Manufacturer's warranty Where Applicable";
                validityText = '45 days';
            }

            $('#payment-terms').text(paymentText);
            $('#delivery-terms').text(deliveryText);
            $('#warranty-terms').text(warrantyText);
            $('#validity-terms').text(validityText);
            
            // Bank details (you can make this dynamic from settings)
            $('#bank-details').text('{{ $general_setting->bank_details ?? "" }}');
            
            $("table.product-quotation-list").append(newBody);
        });
        
    $('#quotation-details').modal('show');
    }

    $("#email-btn").on("click", function(){
        $("#email-input-section").toggle();
    });
    
    // Simple number to words converter (for basic use)
    function numberToWords(num) {
        // Ensure num is an integer
        num = Math.floor(Math.abs(num));
        
        var ones = ['', 'ONE', 'TWO', 'THREE', 'FOUR', 'FIVE', 'SIX', 'SEVEN', 'EIGHT', 'NINE', 'TEN', 
                   'ELEVEN', 'TWELVE', 'THIRTEEN', 'FOURTEEN', 'FIFTEEN', 'SIXTEEN', 'SEVENTEEN', 'EIGHTEEN', 'NINETEEN'];
        var tens = ['', '', 'TWENTY', 'THIRTY', 'FORTY', 'FIFTY', 'SIXTY', 'SEVENTY', 'EIGHTY', 'NINETY'];
        var scales = ['', 'THOUSAND', 'MILLION', 'BILLION'];
        
        if (num === 0) return 'ZERO';
        if (num > 999999999) return 'AMOUNT TOO LARGE';
        
        function convertHundreds(n) {
            n = Math.floor(n);
            if (n === 0) return '';
            
            var result = '';
            
            // Handle hundreds
            if (n >= 100) {
                var hundreds = Math.floor(n / 100);
                if (ones[hundreds]) {
                    result += ones[hundreds] + ' HUNDRED ';
                }
                n = n % 100;
            }
            
            // Handle tens and ones
            if (n >= 20) {
                var tensPlace = Math.floor(n / 10);
                if (tens[tensPlace]) {
                    result += tens[tensPlace];
                }
                n = n % 10;
                if (n > 0) {
                    result += ' ' + ones[n];
                }
                result += ' ';
            } else if (n > 0) {
                if (ones[n]) {
                    result += ones[n] + ' ';
                }
            }
            
            return result.trim();
        }
        
        var parts = [];
        var scaleIndex = 0;
        var remaining = num;
        
        while (remaining > 0) {
            var chunk = remaining % 1000;
            if (chunk > 0) {
                var chunkWords = convertHundreds(chunk);
                if (chunkWords) {
                    if (scaleIndex > 0 && scales[scaleIndex]) {
                        chunkWords += ' ' + scales[scaleIndex];
                    }
                    parts.unshift(chunkWords);
                }
            }
            remaining = Math.floor(remaining / 1000);
            scaleIndex++;
        }
        
        return parts.join(' ').trim();
    }
</script>
@endpush
