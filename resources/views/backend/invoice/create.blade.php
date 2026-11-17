@extends('backend.layout.main') @section('content')

    @include('backend.partials.line_item_product_name_autosize')
    <x-error-message key="not_permitted" />

    <section class="forms">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header d-flex align-items-center">
                            <h4>Add Invoice</h4>
                        </div>
                        <div class="card-body">
                            <p class="italic">
                                <small>{{__('db.The field labels marked with * are required input fields')}}.</small></p>
                            {!! Form::open(['route' => 'invoices.store', 'method' => 'post', 'files' => true, 'id' => 'invoice-form']) !!}
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="row">
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label>Load from Quotation</label>
                                                <select name="quotation_id" id="quotation_id"
                                                    class="selectpicker form-control" data-live-search="true">
                                                    <option value="" {{!isset($quotation_id) || !$quotation_id ? 'selected' : ''}}>-- Manual Entry --</option>
                                                    @foreach($lims_quotation_list as $quotation)
                                                        <option value="{{$quotation->id}}" {{isset($quotation_id) && $quotation_id == $quotation->id ? 'selected' : ''}}>
                                                            {{$quotation->reference_no}}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label>Load from Purchase Order</label>
                                                <select name="purchase_id" id="purchase_id"
                                                    class="selectpicker form-control" data-live-search="true">
                                                    <option value="" {{!isset($purchase_id) || !$purchase_id ? 'selected' : ''}}>-- Manual Entry --</option>
                                                    @foreach($lims_purchase_list as $purchase)
                                                        <option value="{{$purchase->id}}" {{isset($purchase_id) && $purchase_id == $purchase->id ? 'selected' : ''}}>
                                                            {{$purchase->reference_no}}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <!-- PO No and Quotation No Fields (always visible) -->
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label>P.O No.</label>
                                                <input type="text" name="po_number" id="po_number" class="form-control"
                                                    placeholder="Enter Purchase Order Number" />
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label>Quotation No.</label>
                                                <input type="text" name="quotation_number" id="quotation_number"
                                                    class="form-control" placeholder="Enter Quotation Number" />
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Due Date, Customer, Status, Payment Status Row -->
                                    <div class="row mt-3">
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label>Due Date</label>
                                                <input type="text" name="due_date" class="form-control date"
                                                    placeholder="{{__('db.Choose date')}}" />
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label>{{__('db.customer')}} *</label>
                                                <div class="input-group">
                                                    <select id="customer_id" name="customer_id" required
                                                        class="selectpicker form-control" data-live-search="true"
                                                        title="Select customer...">
                                                        @foreach($lims_customer_list as $customer)
                                                            <option value="{{$customer->id}}" {{ (isset($prefill_source) && $prefill_source && $prefill_source['customer_id'] == $customer->id) ? 'selected' : '' }}>
                                                                {{$customer->name . ' (' . $customer->company_name . ')'}}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                    <div class="input-group-append">
                                                        <button type="button" class="btn btn-default btn-sm"
                                                            data-toggle="modal" data-target="#quickAddCustomerModal"><i
                                                                class="fa fa-plus"></i></button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label>Status</label>
                                                <select name="status" id="status" class="form-control selectpicker">
                                                    <option value="pending">Pending</option>
                                                    <option value="submitted">Submitted</option>
                                                    <option value="completed">Completed</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label>Payment Status</label>
                                                <select name="payment_status" id="payment_status"
                                                    class="form-control selectpicker">
                                                    <option value="unpaid">Unpaid</option>
                                                    <option value="partial">Partial</option>
                                                    <option value="paid">Paid</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row" style="display: none;">
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label>{{__('db.Biller')}} *</label>
                                                <div class="d-flex">
                                                    <select required name="biller_id" id="biller_id"
                                                        class="selectpicker form-control flex-grow-1"
                                                        data-live-search="true" title="Select Biller...">
                                                        @php
                                                            $default_biller = $lims_biller_list->firstWhere('company_name', 'GLOBAL PRACTISE ENTERPRISE') ?? $lims_biller_list->first();
                                                        @endphp
                                                        @foreach($lims_biller_list as $biller)
                                                            <option value="{{$biller->id}}" {{($default_biller && $biller->id == $default_biller->id) ? 'selected' : ''}}>
                                                                {{$biller->name . ' (' . $biller->company_name . ')'}}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label>{{__('db.Warehouse')}} *</label>
                                                <div class="input-group">
                                                    <select id="warehouse_id" name="warehouse_id" required
                                                        class="selectpicker form-control" data-live-search="true"
                                                        title="Select warehouse...">
                                                        @foreach($lims_warehouse_list as $warehouse)
                                                            <option value="{{$warehouse->id}}" {{ (isset($prefill_source) && $prefill_source && $prefill_source['warehouse_id'] == $warehouse->id) ? 'selected' : '' }}>{{$warehouse->name}}</option>
                                                        @endforeach
                                                    </select>
                                                    <div class="input-group-append">
                                                        <button type="button" class="btn btn-default btn-sm"
                                                            data-toggle="modal" data-target="#quickAddWarehouseModal"><i
                                                                class="fa fa-plus"></i></button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label>Paid Amount</label>
                                                <input type="number" name="paid_amount" id="paid_amount"
                                                    class="form-control" value="0" step="0.01" min="0" />
                                                <small class="text-muted">Pending: <span
                                                        id="pending_amount">0.00</span></small>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label>Payment Method</label>
                                                <select name="payment_method" id="payment_method"
                                                    class="form-control selectpicker">
                                                    <option value="cash">Cash</option>
                                                    <option value="card">Card</option>
                                                    <option value="online">Online Transfer</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-3" id="payment_details_wrapper" style="display: none;">
                                            <div class="form-group">
                                                <label>Payment Details <small class="text-muted">(Card No / Transaction ID /
                                                        Reference)</small></label>
                                                <input type="text" name="payment_details" id="payment_details"
                                                    class="form-control"
                                                    placeholder="Enter card number or transaction details" />
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label>TERMS *</label>
                                                @php $pf_terms = (isset($prefill_source) && $prefill_source) ? ($prefill_source['terms'] ?? 'CASH') : 'CASH'; @endphp
                                                <select name="terms" class="form-control" required>
                                                    <option value="CASH" {{ $pf_terms == 'CASH' ? 'selected' : '' }}>CASH</option>
                                                    <option value="NET 30" {{ $pf_terms == 'NET 30' ? 'selected' : '' }}>NET 30</option>
                                                    <option value="NET 14" {{ $pf_terms == 'NET 14' ? 'selected' : '' }}>NET 14</option>
                                                    <option value="B2B" {{ $pf_terms == 'B2B' ? 'selected' : '' }}>B2B</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row mt-2">
                                        <div class="col-md-12">
                                            <label>{{__('db.Select Product')}}</label>
                                            <div class="search-box input-group">
                                                <button class="btn btn-secondary"><i class="fa fa-barcode"></i></button>
                                                <input type="text" name="product_code_name" id="lims_productcodeSearch"
                                                    placeholder="{{__('db.Please type product code and select')}}"
                                                    class="form-control" />
                                                <div class="input-group-append">
                                                    <button type="button" class="btn btn-sm btn-success" data-toggle="modal"
                                                        data-target="#quickAddProductModal"><i
                                                            class="fa fa-plus"></i></button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row mt-5">
                                <div class="col-md-12">
                                    <h5>{{__('db.Order Table')}} *</h5>
                                    <div class="table-responsive mt-3">
                                        <table id="myTable" class="table table-hover order-list">
                                            <thead>
                                                <tr>
                                                    <th>{{__('db.name')}}</th>
                                                    <th>{{__('db.Code')}}</th>
                                                    <th>{{__('db.Batch No')}}</th>
                                                    <th>{{__('db.Quantity')}}</th>
                                                    <th>{{__('db.Net Unit Price')}}</th>
                                                    <th>{{__('db.Discount')}}</th>
                                                    <th>{{__('db.Tax')}}</th>
                                                    <th>{{__('db.Subtotal')}}</th>
                                                    <th><i class="dripicons-trash"></i></th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @if(!empty($prefill_products) && is_array($prefill_products))
                                                    @foreach($prefill_products as $product)
                                                        <tr>
                                                            <td>{{ $product['product_name'] ?? '' }}
                                                                <input type="hidden" class="product-name" name="product_name[]"
                                                                    value="{{ $product['product_name'] ?? '' }}" />
                                                                <input type="hidden" class="product-id" name="product_id[]"
                                                                    value="{{ $product['product_id'] ?? '' }}" />
                                                            </td>
                                                            <td>{{ $product['product_code'] ?? '' }}
                                                                <input type="hidden" class="product-code" name="product_code[]"
                                                                    value="{{ $product['product_code'] ?? '' }}" />
                                                            </td>
                                                            <td>
                                                                <input type="hidden" class="product-batch-id"
                                                                    name="product_batch_id[]"
                                                                    value="{{ $product['product_batch_id'] ?? '' }}" />
                                                                {{ !empty($product['product_batch_id']) ? 'Batch' : 'N/A' }}
                                                            </td>
                                                            <td>
                                                                <input type="number" class="form-control qty" name="qty[]"
                                                                    value="{{ $product['qty'] ?? 0 }}" step="any" />
                                                                <input type="hidden" class="sale-unit" name="sale_unit[]"
                                                                    value="{{ $product['sale_unit'] ?? '' }}" />
                                                            </td>
                                                            <td><input type="number" class="form-control net_unit_price"
                                                                    name="net_unit_price[]"
                                                                    value="{{ $product['net_unit_price'] ?? 0 }}" step="any" /></td>
                                                            <td><input type="number" class="form-control discount" name="discount[]"
                                                                    value="{{ $product['discount'] ?? 0 }}" step="any" /></td>
                                                            @php
                                                                $row_subtotal = (($product['net_unit_price'] ?? 0) * ($product['qty'] ?? 0)) - ($product['discount'] ?? 0);
                                                                $row_tax = $row_subtotal * ($product['tax_rate'] ?? 0) / 100;
                                                                $row_total = $row_subtotal + $row_tax;
                                                                $display_total = ($product['total'] ?? 0) > 0 ? $product['total'] : $row_total;
                                                            @endphp
                                                            <td class="tax">
                                                                {{ number_format($row_tax, $general_setting->decimal, '.', '') }}
                                                                <input type="hidden" class="tax-rate" name="tax_rate[]"
                                                                    value="{{ $product['tax_rate'] ?? 0 }}" />
                                                                <input type="hidden" name="tax[]"
                                                                    value="{{ number_format($row_tax, $general_setting->decimal, '.', '') }}" />
                                                            </td>
                                                            <td class="sub-total">
                                                                {{ number_format($display_total, $general_setting->decimal, '.', '') }}
                                                                <input type="hidden" class="subtotal-value" name="subtotal[]"
                                                                    value="{{ number_format($display_total, $general_setting->decimal, '.', '') }}" />
                                                            </td>
                                                            <td><button type="button" class="ibtnDel btn btn-md btn-danger"><i
                                                                        class="dripicons-trash"></i></button></td>
                                                        </tr>
                                                    @endforeach
                                                @endif
                                            </tbody>
                                            <tfoot class="tfoot active">
                                                <th colspan="2">{{__('db.Total')}}</th>
                                                <th></th>
                                                <th id="total-qty">0</th>
                                                <th></th>
                                                <th id="total-discount">
                                                    {{number_format(0, $general_setting->decimal, '.', '')}}</th>
                                                <th id="total-tax">{{number_format(0, $general_setting->decimal, '.', '')}}
                                                </th>
                                                <th id="total">{{number_format(0, $general_setting->decimal, '.', '')}}</th>
                                                <th><i class="dripicons-trash"></i></th>
                                            </tfoot>
                                        </table>
                                    </div>
                                    <div class="mt-2">
                                        <button type="button" id="add-line-item" class="btn btn-success">
                                            <i class="fa fa-plus"></i> Line Item
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-2">
                                    <input type="hidden" name="total_qty" />
                                </div>
                                <div class="col-md-2">
                                    <input type="hidden" name="total_discount" />
                                </div>
                                <div class="col-md-2">
                                    <input type="hidden" name="total_tax" />
                                </div>
                                <div class="col-md-2">
                                    <input type="hidden" name="total_price" />
                                </div>
                                <div class="col-md-2">
                                    <input type="hidden" name="item" />
                                    <input type="hidden" name="order_tax" />
                                </div>
                                <div class="col-md-2">
                                    <input type="hidden" name="grand_total" />
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>{{__('db.Order Tax')}}</label>
                                        <select class="form-control" name="order_tax_rate">
                                            <option value="0">{{__('db.No Tax')}}</option>
                                            @foreach($lims_tax_list as $tax)
                                                <option value="{{$tax->rate}}">{{$tax->name}}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>{{__('db.Order Discount')}}</label>
                                        <input type="number" name="order_discount" class="form-control" step="any" value="{{ (isset($prefill_source) && $prefill_source) ? ($prefill_source['order_discount'] ?? '') : '' }}">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>{{__('db.Shipping Cost')}}</label>
                                        <input type="number" name="shipping_cost" class="form-control" step="any" value="{{ (isset($prefill_source) && $prefill_source) ? ($prefill_source['shipping_cost'] ?? '') : '' }}">
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>{{__('db.Attach Document')}}</label>
                                        <input type="file" name="document" class="form-control" />
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label>{{__('db.Note')}}</label>
                                        <textarea rows="5" name="note" class="form-control">{{ (isset($prefill_source) && $prefill_source) ? ($prefill_source['note'] ?? '') : '' }}</textarea>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label>Terms & Conditions</label>
                                        <textarea rows="5" name="terms_conditions" class="form-control">{{ (isset($prefill_source) && $prefill_source && !empty($prefill_source['terms_conditions'])) ? $prefill_source['terms_conditions'] : "Payment: 30 days
Delivery: As per D.O / Quotation
Warranty: Manufacturer's warranty where applicable
Validity: Subject to invoice due date" }}</textarea>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label>Delivery To</label>
                                        <textarea name="delivery_address" class="form-control" rows="2">{{ (isset($prefill_source) && $prefill_source) ? ($prefill_source['delivery_address'] ?? '') : '' }}</textarea>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <input type="submit" value="{{__('db.submit')}}" class="btn btn-primary" id="submit-button">
                            </div>
                            {!! Form::close() !!}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @php
            $footer_items = 0;
            $footer_total = 0;
            $footer_total_tax = 0;
            $footer_total_discount = 0;
            if (!empty($prefill_products) && is_array($prefill_products)) {
                foreach ($prefill_products as $fp) {
                    $fp_subtotal = (($fp['net_unit_price'] ?? 0) * ($fp['qty'] ?? 0)) - ($fp['discount'] ?? 0);
                    $fp_tax = $fp_subtotal * ($fp['tax_rate'] ?? 0) / 100;
                    $fp_row_total = $fp_subtotal + $fp_tax;
                    $footer_total += ($fp['total'] ?? 0) > 0 ? $fp['total'] : $fp_row_total;
                    $footer_total_tax += $fp_tax;
                    $footer_total_discount += ($fp['discount'] ?? 0) * ($fp['qty'] ?? 0);
                    $footer_items++;
                }
            }
            $footer_order_tax_rate = ($prefill_source['order_tax_rate'] ?? 0);
            $footer_order_tax = $footer_total * $footer_order_tax_rate / 100;
            $footer_order_discount = ($prefill_source['order_discount'] ?? 0);
            $footer_shipping = ($prefill_source['shipping_cost'] ?? 0);
            $footer_grand_total = $footer_total + $footer_order_tax - $footer_order_discount + $footer_shipping;
        @endphp
        <div class="container-fluid">
            <table class="table table-bordered table-condensed totals">
                <td><strong>{{__('db.Items')}}</strong>
                    <span class="pull-right" id="item">{{ $footer_items > 0 ? $footer_items : number_format(0, $general_setting->decimal, '.', '') }}</span>
                </td>
                <td><strong>{{__('db.Total')}}</strong>
                    <span class="pull-right" id="subtotal">{{number_format($footer_total, $general_setting->decimal, '.', '')}}</span>
                </td>
                <td><strong>{{__('db.Order Tax')}}</strong>
                    <span class="pull-right" id="order_tax">{{number_format($footer_order_tax, $general_setting->decimal, '.', '')}}</span>
                </td>
                <td><strong>{{__('db.Order Discount')}}</strong>
                    <span class="pull-right"
                        id="order_discount">{{number_format($footer_order_discount, $general_setting->decimal, '.', '')}}</span>
                </td>
                <td><strong>{{__('db.Shipping Cost')}}</strong>
                    <span class="pull-right"
                        id="shipping_cost">{{number_format($footer_shipping, $general_setting->decimal, '.', '')}}</span>
                </td>
                <td><strong>{{__('db.grand total')}}</strong>
                    <span class="pull-right"
                        id="grand_total">{{number_format($footer_grand_total, $general_setting->decimal, '.', '')}}</span>
                </td>
            </table>
        </div>
    </section>

    @include('backend.quotation.partials.quick_add_modals')

@endsection

@push('css')
    <style>
        .bg-light {
            background-color: #f8f9fa !important;
        }
    </style>
@endpush

@push('scripts')
    <script type="text/javascript">
        $("ul#invoice").siblings('a').attr('aria-expanded', 'true');
        $("ul#invoice").addClass("show");
        $("ul#invoice #invoice-create-menu").addClass("active");

        var decimal = <?php echo json_encode($general_setting->decimal); ?>;
        var prefillSource = @json($prefill_source ?? null);
        var prefillProducts = @json($prefill_products ?? []);

        // Auto-load quotation if quotation_id is provided
        @if(isset($quotation_id) && $quotation_id)
            $(document).ready(function () {
                $('#quotation_id').val({{$quotation_id}}).selectpicker('refresh');
                // wait for selectpicker/UI to be ready before firing change
                setTimeout(function () {
                    $('#quotation_id').trigger('change');
                }, 200);
            });
        @endif

        // Auto-load purchase order if purchase_id is provided
        @if(isset($purchase_id) && $purchase_id)
            $(document).ready(function () {
                $('#purchase_id').val('{{$purchase_id}}').selectpicker('refresh');
                setTimeout(function () {
                    $('#purchase_id').trigger('change');
                }, 200);
            });
        @endif

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        // Function to enable manual entry mode
        function enableManualEntry() {
            $('#biller_id, #customer_id, #warehouse_id').prop('disabled', false).selectpicker('refresh');
            $('.quick-add-btn').show();
            // Clear products table for manual entry
            $('table.order-list tbody').html('');
            calculateTotal();
        }

        // Function to disable manual entry mode (when loading from source)
        function disableManualEntry() {
            $('#biller_id, #customer_id, #warehouse_id').prop('disabled', true).selectpicker('refresh');
            $('.quick-add-btn').hide();
        }

        // Function to update reference number fields based on selections
        function updateReferenceFields() {
            var quotation_id = $('#quotation_id').val();
            var purchase_id = $('#purchase_id').val();

            if (quotation_id) {
                // Quotation selected - make quotation number field readonly (user can still see/override)
                $('#quotation_number').prop('readonly', true).addClass('bg-light');
            } else {
                // No quotation selected - make quotation number field editable
                $('#quotation_number').prop('readonly', false).removeClass('bg-light');
            }

            if (purchase_id) {
                // Purchase order selected - make PO number field readonly (user can still see/override)
                $('#po_number').prop('readonly', true).addClass('bg-light');
            } else {
                // No purchase order selected - make PO number field editable
                $('#po_number').prop('readonly', false).removeClass('bg-light');
            }
        }

        // Initialize reference fields on page load
        updateReferenceFields();

        // Update reference fields whenever dropdown values change
        $('#quotation_id, #purchase_id').on('change', function () {
            var quotation_id = $('#quotation_id').val();
            var purchase_id = $('#purchase_id').val();

            // Mutual exclusion: If one is selected, clear the other
            if ($(this).attr('id') === 'quotation_id' && quotation_id) {
                $('#purchase_id').val('').selectpicker('refresh');
                loadFromSource('{{ url("invoices/load-quotation") }}/' + quotation_id);
                disableManualEntry();
            } else if ($(this).attr('id') === 'purchase_id' && purchase_id) {
                $('#quotation_id').val('').selectpicker('refresh');
                loadFromSource('{{ url("invoices/load-purchase") }}/' + purchase_id);
                disableManualEntry();
            } else {
                // Enable manual entry when deselected
                enableManualEntry();
            }

            // Update reference fields
            updateReferenceFields();
        });

        function loadFromSource(url) {
            $.ajax({
                url: url,
                type: "GET",
                dataType: "json",
                success: function (response) {
                    if (response.success) {
                        applyInvoicePrefill(response.quotation, response.products || []);
                    } else {
                        console.error("Response not successful:", response);
                    }
                },
                error: function (xhr) {
                    console.error("Error loading data:", xhr.responseText);
                }
            });
        }

        function applyInvoicePrefill(source, products) {
            if (!source) return;

            // Set form fields (handle null values for purchase order)
            if (source.biller_id) {
                $('#biller_id').val(source.biller_id).selectpicker('refresh');
                $('#biller_id').prop('disabled', true).selectpicker('refresh');
            }
            if (source.customer_id) {
                $('#customer_id').val(source.customer_id).selectpicker('refresh');
            }
            // Keep customer dropdown enabled for Purchase Order case
            $('#customer_id').prop('disabled', false).selectpicker('refresh');

            $('#warehouse_id').val(source.warehouse_id).selectpicker('refresh');
            $('#warehouse_id').prop('disabled', true).selectpicker('refresh');

            // Set order tax, discount, shipping, note from source
            if (source.order_tax_rate !== undefined) {
                $('select[name="order_tax_rate"]').val(source.order_tax_rate || 0).selectpicker('refresh');
            }
            if (source.order_discount !== undefined) {
                $('input[name="order_discount"]').val(source.order_discount || 0);
            }
            if (source.shipping_cost !== undefined) {
                $('input[name="shipping_cost"]').val(source.shipping_cost || 0);
            }
            if (source.note !== undefined) {
                $('textarea[name="note"]').val(source.note || '');
            }
            if (source.terms !== undefined) {
                $('select[name="terms"]').val(source.terms || 'CASH');
            }
            if (source.terms_conditions !== undefined) {
                $('textarea[name="terms_conditions"]').val(source.terms_conditions || '');
            }
            if (source.delivery_address !== undefined) {
                $('textarea[name="delivery_address"]').val(source.delivery_address || '');
            }

            // Hide + buttons
            $('.quick-add-btn').hide();

            // Clear existing products
            $('table.order-list tbody').html('');

            // Add products to table
            if (products && products.length > 0) {
                $.each(products, function (index, product) {
                    addProductRow(product);
                });
            }

            calculateTotal(true);
        }

        // Purchase-style server prefill (no AJAX) when coming from ?quotation_id=
        $(document).ready(function () {
            // If rows were already rendered server-side, just compute totals and disable manual entry
            if ($('table.order-list tbody tr').length > 0) {
                disableManualEntry();
                calculateTotal(true);
            } else if (prefillSource && prefillProducts && prefillProducts.length) {
                applyInvoicePrefill(prefillSource, prefillProducts);
                disableManualEntry();
            }
        });

        function addProductRow(product) {
            var newRow = '<tr>' +
                '<td>' + product.product_name + '<input type="hidden" class="product-name" name="product_name[]" value="' + product.product_name + '"/><input type="hidden" class="product-id" name="product_id[]" value="' + product.product_id + '"/></td>' +
                '<td>' + product.product_code + '<input type="hidden" class="product-code" name="product_code[]" value="' + product.product_code + '"/></td>' +
                '<td><input type="hidden" class="product-batch-id" name="product_batch_id[]" value="' + (product.product_batch_id || '') + '"/>' + (product.product_batch_id ? 'Batch' : 'N/A') + '</td>' +
                '<td><input type="number" class="form-control qty" name="qty[]" value="' + product.qty + '" step="any"/><input type="hidden" class="sale-unit" name="sale_unit[]" value="' + product.sale_unit + '"/></td>' +
                '<td><input type="number" class="form-control net_unit_price" name="net_unit_price[]" value="' + product.net_unit_price + '" step="any"/></td>' +
                '<td><input type="number" class="form-control discount" name="discount[]" value="' + product.discount + '" step="any"/></td>' +
                '<td class="tax">' + product.tax + '<input type="hidden" class="tax-rate" name="tax_rate[]" value="' + product.tax_rate + '"/><input type="hidden" name="tax[]" value="' + product.tax + '"/></td>' +
                '<td class="sub-total">' + product.total + '<input type="hidden" class="subtotal-value" name="subtotal[]" value="' + product.total + '"/></td>' +
                '<td><button type="button" class="ibtnDel btn btn-md btn-danger"><i class="dripicons-trash"></i></button></td>' +
                '</tr>';
            $('table.order-list tbody').append(newRow);
        }

        // Product search autocomplete
        var lims_product_code = [
            <?php
    $lims_product_list = \App\Models\Product::where('is_active', true)->get();
    foreach ($lims_product_list as $product) {
        echo "{ value: '" . $product->code . " (" . preg_replace("/'/", "", $product->name) . ")', id: " . $product->id . " },";
    }
            ?>
        ];

        $('#lims_productcodeSearch').autocomplete({
            source: lims_product_code,
            minLength: 1,
            response: function (event, ui) {
                if (ui.content.length == 1) {
                    ui.item = ui.content[0];
                    $(this).data('ui-autocomplete')._trigger('select', 'autocompleteselect', ui);
                    $(this).autocomplete('close');
                }
            },
            select: function (event, ui) {
                $.ajax({
                    type: 'GET',
                    url: '{{route("product.getById", "")}}/' + ui.item.id,
                    dataType: 'json',
                    success: function (data) {
                        var tax = parseFloat(data.net_unit_price * data.tax_rate / 100).toFixed(decimal);
                        var total = parseFloat(data.net_unit_price * (1 + data.tax_rate / 100)).toFixed(decimal);
                        var product = {
                            product_id: data.product_id,
                            product_name: data.product_name,
                            product_code: data.product_code,
                            qty: 1,
                            sale_unit: data.sale_unit,
                            net_unit_price: data.net_unit_price,
                            discount: data.discount,
                            tax_rate: data.tax_rate,
                            tax: tax,
                            total: total,
                            product_batch_id: ''
                        };
                        addProductRow(product);
                        calculateTotal();
                    }
                });
                $(this).val('');
                return false;
            }
        });

        // Calculate totals
        // preserveRowValues: if true, don't recalculate row tax and subtotal (used when loading from PO/Quotation)
        function calculateTotal(preserveRowValues = false) {
            var total_qty = 0;
            var total_discount = 0;
            var total_tax = 0;
            var total = 0;
            var item = 0;

            $('table.order-list tbody tr').each(function () {
                var $row = $(this);
                var qty = parseFloat($row.find('.qty').val()) || 0;

                // Support both .net_unit_price (existing) and .net_unit_price_input (new line items)
                var net_unit_price = parseFloat($row.find('.net_unit_price_input').val()) || parseFloat($row.find('.net_unit_price').val()) || 0;

                // Support both .discount_input (new) and .discount (existing)
                var discount = parseFloat($row.find('.discount_input').val()) || parseFloat($row.find('.discount').val()) || 0;
                var tax_rate = parseFloat($row.find('.tax-rate').val()) || 0;

                var tax, row_total;

                if (preserveRowValues) {
                    // Use original values from the hidden inputs (from PO/Quotation)
                    tax = parseFloat($row.find('input[name="tax[]"]').val()) || 0;
                    row_total = parseFloat($row.find('.subtotal-value').val()) || 0;
                } else {
                    // Recalculate (for manual entry or when qty/price changes)
                    var subtotal = (net_unit_price * qty) - discount;
                    tax = subtotal * tax_rate / 100;
                    row_total = subtotal + tax;

                    $row.find('.tax').html(tax.toFixed(decimal) + '<input type="hidden" class="tax-rate" name="tax_rate[]" value="' + tax_rate + '"/><input type="hidden" name="tax[]" value="' + tax.toFixed(decimal) + '"/>');
                    $row.find('.sub-total').html(row_total.toFixed(decimal) + '<input type="hidden" class="subtotal-value" name="subtotal[]" value="' + row_total.toFixed(decimal) + '"/>');
                }

                total_qty += qty;
                // For new line items, discount is total amount; for existing, check if per-item
                var discountAmount = discount;
                // Check if this is a new line item (has discount_input class)
                if ($row.find('.discount_input').length > 0) {
                    // New line item - discount is already total
                    discountAmount = discount;
                } else {
                    // Existing item - might be per-item, multiply by qty to be safe
                    discountAmount = discount * qty;
                }
                total_discount += discountAmount;
                total_tax += tax;
                total += row_total;
                item++;
            });

            $('#total-qty').text(total_qty);
            $('#total-discount').text(total_discount.toFixed(decimal));
            $('#total-tax').text(total_tax.toFixed(decimal));
            $('#total').text(total.toFixed(decimal));
            $('#item').text(item);
            $('#subtotal').text(total.toFixed(decimal));

            // Calculate grand total with order tax, discount, shipping
            var order_tax_rate = parseFloat($('select[name="order_tax_rate"]').val()) || 0;
            var order_discount = parseFloat($('input[name="order_discount"]').val()) || 0;
            var shipping_cost = parseFloat($('input[name="shipping_cost"]').val()) || 0;

            var order_tax = total * order_tax_rate / 100;
            var grand_total = total + order_tax - order_discount + shipping_cost;

            $('#order_tax').text(order_tax.toFixed(decimal));
            $('#order_discount').text(order_discount.toFixed(decimal));
            $('#shipping_cost').text(shipping_cost.toFixed(decimal));
            $('#grand_total').text(grand_total.toFixed(decimal));

            // Set hidden fields
            $('input[name="total_qty"]').val(total_qty);
            $('input[name="total_discount"]').val(total_discount.toFixed(decimal));
            $('input[name="total_tax"]').val(total_tax.toFixed(decimal));
            $('input[name="total_price"]').val(total.toFixed(decimal));
            $('input[name="item"]').val(item);
            $('input[name="order_tax"]').val(order_tax.toFixed(decimal));
            $('input[name="grand_total"]').val(grand_total.toFixed(decimal));

            // Update pending amount after grand total is set
            updatePendingAmountInternal();
        }

        // Internal function to update pending (called from calculateTotal)
        function updatePendingAmountInternal() {
            var grand_total = parseFloat($('input[name="grand_total"]').val()) || 0;
            var paid_amount = parseFloat($('#paid_amount').val()) || 0;
            var pending = grand_total - paid_amount;

            $('#pending_amount').text(pending.toFixed(decimal));

            // Auto-update payment status
            if (paid_amount <= 0) {
                $('#payment_status').val('unpaid').selectpicker('refresh');
            } else if (paid_amount >= grand_total) {
                $('#payment_status').val('paid').selectpicker('refresh');
            } else {
                $('#payment_status').val('partial').selectpicker('refresh');
            }
        }

        // Recalculate on input change
        $(document).on('change input', '.qty, .net_unit_price, .net_unit_price_input, .discount, .discount_input, select[name="order_tax_rate"], input[name="order_discount"], input[name="shipping_cost"]', function () {
            calculateTotal();
        });

        // Update pending amount and payment status (wrapper function)
        function updatePendingAmount() {
            updatePendingAmountInternal();
        }

        // Update pending amount when paid amount changes
        $('#paid_amount').on('input', function () {
            updatePendingAmountInternal();
        });

        // Show/hide payment details based on payment method
        $('#payment_method').on('change', function () {
            var method = $(this).val();
            if (method == 'card' || method == 'online') {
                $('#payment_details_wrapper').show();
            } else {
                $('#payment_details_wrapper').hide();
                $('#payment_details').val('');
            }
        });

        // Validate completed status - must have payment_status = paid
        $('#status').on('change', function () {
            if ($(this).val() == 'completed') {
                if ($('#payment_status').val() != 'paid') {
                    alert('Invoice can only be marked as completed when payment status is "Paid".');
                    $(this).val('submitted').selectpicker('refresh');
                }
            }
        });

        // Prevent setting completed if payment not paid
        $('#invoice-form').on('submit', function (e) {
            calculateTotal(false);
            if ($('#status').val() == 'completed' && $('#payment_status').val() != 'paid') {
                e.preventDefault();
                alert('Invoice can only be marked as completed when payment status is "Paid".');
                return false;
            }
        });

        // Delete row
        $(document).on('click', '.ibtnDel', function () {
            $(this).closest('tr').remove();
            calculateTotal();
        });

        // Form submit handler - re-enable disabled fields so their values are submitted
        $('#invoice-form').on('submit', function () {
            calculateTotal(false);
            // Temporarily enable disabled selects so their values are submitted
            $('#biller_id, #customer_id, #warehouse_id').prop('disabled', false);
        });

        // ========== Quick Add Scripts for Biller, Customer, Warehouse ==========

        // Quick Add Biller
        $(document).on('submit', '#quickAddBillerForm', function (e) {
            e.preventDefault();
            e.stopPropagation();
            var $form = $(this);
            var $submitBtn = $form.find('button[type="submit"]');
            $submitBtn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Processing...');

            $.ajax({
                url: '{{route("biller.quickAdd")}}',
                method: 'POST',
                data: $form.serialize(),
                timeout: 30000,
                dataType: 'json',
                success: function (response) {
                    if (response && response.success) {
                        var newOption = new Option(response.data.name + ' (' + response.data.company_name + ')', response.data.id, true, true);
                        $('#biller_id').append(newOption).val(response.data.id).selectpicker('refresh');
                        $('#quickAddBillerModal').modal('hide');
                        $form[0].reset();
                    }
                    $submitBtn.prop('disabled', false).html('{{__("db.submit")}}');
                },
                error: function (xhr) {
                    var errorMsg = 'Error';
                    if (xhr && xhr.responseJSON && xhr.responseJSON.errors) {
                        errorMsg = Object.values(xhr.responseJSON.errors).map(function (e) { return e[0]; }).join('<br>');
                    } else if (xhr && xhr.responseJSON && xhr.responseJSON.message) {
                        errorMsg = xhr.responseJSON.message;
                    }
                    alert(errorMsg);
                    $submitBtn.prop('disabled', false).html('{{__("db.submit")}}');
                }
            });
            return false;
        });

        // Quick Add Customer
        $(document).on('submit', '#quickAddCustomerForm', function (e) {
            e.preventDefault();
            e.stopPropagation();
            var $form = $(this);
            var $submitBtn = $form.find('button[type="submit"]');
            $submitBtn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Processing...');

            $.ajax({
                url: '{{route("customer.quickAdd")}}',
                method: 'POST',
                data: $form.serialize(),
                timeout: 30000,
                dataType: 'json',
                success: function (response) {
                    if (response && response.success) {
                        var newOption = new Option(response.data.name + ' (' + response.data.company_name + ')', response.data.id, true, true);
                        $('#customer_id').append(newOption).val(response.data.id).selectpicker('refresh');
                        $('#customer_id').trigger('change');
                        $('#customer_id').trigger('changed.bs.select');
                        $('#quickAddCustomerModal').modal('hide');
                        $form[0].reset();
                    }
                    $submitBtn.prop('disabled', false).html('{{__("db.submit")}}');
                },
                error: function (xhr) {
                    var errorMsg = 'Error';
                    if (xhr && xhr.responseJSON && xhr.responseJSON.errors) {
                        errorMsg = Object.values(xhr.responseJSON.errors).map(function (e) { return e[0]; }).join('<br>');
                    } else if (xhr && xhr.responseJSON && xhr.responseJSON.message) {
                        errorMsg = xhr.responseJSON.message;
                    }
                    alert(errorMsg);
                    $submitBtn.prop('disabled', false).html('{{__("db.submit")}}');
                }
            });
            return false;
        });

        // Quick Add Warehouse is handled globally by expense partials

        // Hide header, navbar, sidebar and footer in product iframe - keep form visible
        $('#quickAddProductModal').on('shown.bs.modal', function () {
            setTimeout(function () {
                try {
                    var iframe = document.getElementById('productIframe');
                    if (iframe && iframe.contentDocument) {
                        var iframeDoc = iframe.contentDocument || iframe.contentWindow.document;

                        // Hide sidebar
                        var sideNavbar = iframeDoc.querySelector('.side-navbar');
                        if (sideNavbar) {
                            sideNavbar.style.display = 'none';
                        }

                        // Hide header/navbar
                        var header = iframeDoc.querySelector('header.container-fluid');
                        if (header) {
                            header.style.display = 'none';
                        }

                        // Hide footer
                        var footer = iframeDoc.querySelector('.main-footer');
                        if (footer) {
                            footer.style.display = 'none';
                        }

                        // Adjust page layout to show form properly
                        var page = iframeDoc.querySelector('.page');
                        if (page) {
                            page.style.marginLeft = '0';
                            page.style.width = '100%';
                        }

                        // Show content (form)
                        var content = iframeDoc.querySelector('#content');
                        if (content) {
                            content.style.display = 'block';
                        }
                    }
                } catch (e) {
                    console.log('Iframe access issue:', e);
                }
            }, 500);
        });

        // Product iframe message listener
        window.addEventListener('message', function (event) {
            if (event.data && event.data.type === 'productAdded') {
                // Hide modal
                $('#quickAddProductModal').modal('hide');

                var warehouse_id = $('#warehouse_id').val();
                var customer_id = $('#customer_id').val();
                var productCode = event.data.product_code || null;

                if (warehouse_id && customer_id && productCode) {
                    // Show loading message
                    alert('Product added! Loading...');

                    // First refresh product list, then auto-search
                    $.get('getproduct/' + warehouse_id, function (data) {
                        lims_product_array = [];
                        product_code = data[0];
                        product_name = data[1];
                        product_qty = data[2];
                        product_type = data[3];
                        product_id = data[4];
                        product_list = data[5];
                        qty_list = data[6];
                        product_warehouse_price = data[7];
                        expired_date = data[10];
                        is_embeded = data[11];
                        imei_number = data[12];
                        $.each(product_code, function (index) {
                            lims_product_array.push(product_code[index] + '|' + product_name[index] + '|' + imei_number[index] + '|' + is_embeded[index]);
                        });

                        // Find the product in the array and call productSearch directly
                        var found = false;
                        $.each(lims_product_array, function (index, item) {
                            if (item.indexOf(productCode) === 0) {
                                // Call productSearch function directly (same as autocomplete select)
                                if (typeof productSearch === 'function') {
                                    productSearch(item);
                                    alert('Product added to order table!');
                                }
                                found = true;
                                return false; // break loop
                            }
                        });

                        if (!found) {
                            alert('Product created! Search for "' + productCode + '" to add it.');
                        }
                    });
                } else if (productCode) {
                    alert('Product created! Code: ' + productCode + '. Select Customer & Warehouse first, then search.');
                } else {
                    alert('Product created successfully!');
                }
            }
        });

        // Remove any duplicate buttons first
        $('#add-line-item').not(':first').remove();

        // Add Line Item button functionality
        $('#add-line-item').off('click').on('click', function () {
            // Check customer and warehouse selection
            var customer_id = $('#customer_id').val();
            var warehouse_id = $('#warehouse_id').val();

            if (!customer_id) {
                alert('Please select Customer first!');
                return false;
            }
            else if (!warehouse_id) {
                alert('Please select Warehouse first!');
                return false;
            }

            // Generate product code
            $.get('{{ route("product.gencode") }}', function (code) {
                var newRow = $("<tr>");
                var cols = '';
                cols += '<td><textarea class="form-control product-name line-item-product-name" name="product_name[]" rows="1" placeholder="Enter product name" required></textarea></td>';
                cols += '<td><input type="text" class="form-control product-code" name="product_code[]" value="' + code + '" style="width:100px;"/></td>';
                cols += '<td><input type="text" class="form-control batch-no" disabled/> <input type="hidden" class="product-batch-id" name="product_batch_id[]"/></td>';
                cols += '<td><input type="number" class="form-control qty" name="qty[]" value="0" step="any" required/></td>';
                cols += '<td><input type="number" class="form-control net_unit_price_input" name="net_unit_price[]" value="0" step="any"/></td>';
                cols += '<td><input type="number" class="form-control discount_input" name="discount[]" value="0" step="any"/></td>';
                cols += '<td class="tax">0<input type="hidden" class="tax-rate" name="tax_rate[]" value="0"/><input type="hidden" class="tax-value" name="tax[]" value="0"/></td>';
                cols += '<td class="sub-total">0<input type="hidden" class="subtotal-value" name="subtotal[]" value="0"/></td>';
                cols += '<td><button type="button" class="ibtnDel btn btn-md btn-danger"><i class="dripicons-trash"></i></button><input type="hidden" class="product-id" name="product_id[]" value="0"/><input type="hidden" class="sale-unit" name="sale_unit[]" value=""/></td>';

                newRow.append(cols);
                $("table.order-list tbody").append(newRow);
                newRow.find('textarea.line-item-product-name').each(function () {
                    if (window.resizeLineItemProductName) window.resizeLineItemProductName(this);
                });

                // Initialize arrays for this row
                var rowindex = newRow.index();
                product_price.splice(rowindex, 0, 0);
                product_discount.splice(rowindex, 0, 0);
                tax_rate.splice(rowindex, 0, 0);
                tax_name.splice(rowindex, 0, '');
                tax_method.splice(rowindex, 0, '');
                unit_name.splice(rowindex, 0, '');
                unit_operator.splice(rowindex, 0, '*');
                unit_operation_value.splice(rowindex, 0, 1);
                is_imei.splice(rowindex, 0, false);

                // Add event handlers for calculation
                newRow.find('.qty').off('input').on('input', function (e) {
                    e.stopPropagation();
                    var row = $(this).closest('tr');
                    var qty = parseFloat($(this).val()) || 0;
                    var price = parseFloat(row.find('.net_unit_price_input').val()) || 0;
                    var discount = parseFloat(row.find('.discount_input').val()) || 0;
                    var subtotal = (qty * price) - discount;
                    var tax = 0;
                    var tax_rate = 0;

                    // Update subtotal display and hidden input
                    row.find('.sub-total').html(subtotal.toFixed({{$general_setting->decimal}}) + '<input type="hidden" class="subtotal-value" name="subtotal[]" value="' + subtotal.toFixed({{$general_setting->decimal}}) + '"/>');
                    row.find('.subtotal-value').val(subtotal.toFixed({{$general_setting->decimal}}));

                    // Update tax display and hidden input
                    row.find('.tax').html(tax.toFixed({{$general_setting->decimal}}) + '<input type="hidden" class="tax-rate" name="tax_rate[]" value="' + tax_rate + '"/><input type="hidden" class="tax-value" name="tax[]" value="' + tax.toFixed({{$general_setting->decimal}}) + '"/>');
                    row.find('.tax-value').val(tax);
                    row.find('.tax-rate').val(tax_rate);

                    calculateTotal();
                });

                newRow.find('.net_unit_price_input').off('input').on('input', function (e) {
                    e.stopPropagation();
                    var row = $(this).closest('tr');
                    var qty = parseFloat(row.find('.qty').val()) || 0;
                    var price = parseFloat($(this).val()) || 0;
                    var discount = parseFloat(row.find('.discount_input').val()) || 0;
                    var subtotal = (qty * price) - discount;
                    var tax = 0;
                    var tax_rate = 0;

                    // Update subtotal display and hidden input
                    row.find('.sub-total').html(subtotal.toFixed({{$general_setting->decimal}}) + '<input type="hidden" class="subtotal-value" name="subtotal[]" value="' + subtotal.toFixed({{$general_setting->decimal}}) + '"/>');
                    row.find('.subtotal-value').val(subtotal.toFixed({{$general_setting->decimal}}));

                    // Update tax display and hidden input
                    row.find('.tax').html(tax.toFixed({{$general_setting->decimal}}) + '<input type="hidden" class="tax-rate" name="tax_rate[]" value="' + tax_rate + '"/><input type="hidden" class="tax-value" name="tax[]" value="' + tax.toFixed({{$general_setting->decimal}}) + '"/>');
                    row.find('.tax-value').val(tax);
                    row.find('.tax-rate').val(tax_rate);

                    calculateTotal();
                });

                newRow.find('.discount_input').off('input').on('input', function (e) {
                    e.stopPropagation();
                    var row = $(this).closest('tr');
                    var qty = parseFloat(row.find('.qty').val()) || 0;
                    var price = parseFloat(row.find('.net_unit_price_input').val()) || 0;
                    var discount = parseFloat($(this).val()) || 0;
                    var subtotal = (qty * price) - discount;
                    var tax = 0;
                    var tax_rate = 0;

                    // Update subtotal display and hidden input
                    row.find('.sub-total').html(subtotal.toFixed({{$general_setting->decimal}}) + '<input type="hidden" class="subtotal-value" name="subtotal[]" value="' + subtotal.toFixed({{$general_setting->decimal}}) + '"/>');
                    row.find('.subtotal-value').val(subtotal.toFixed({{$general_setting->decimal}}));

                    // Update tax display and hidden input
                    row.find('.tax').html(tax.toFixed({{$general_setting->decimal}}) + '<input type="hidden" class="tax-rate" name="tax_rate[]" value="' + tax_rate + '"/><input type="hidden" class="tax-value" name="tax[]" value="' + tax.toFixed({{$general_setting->decimal}}) + '"/>');
                    row.find('.tax-value').val(tax);
                    row.find('.tax-rate').val(tax_rate);

                    calculateTotal();
                });

                // Recalculate totals
                calculateTotal();
            });
        });
    </script>
@endpush