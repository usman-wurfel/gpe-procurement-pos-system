@extends('backend.layout.main') @section('content')

    @include('backend.partials.line_item_product_name_autosize')
    <x-error-message key="not_permitted" />

    <section class="forms">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header d-flex align-items-center">
                            <h4>Add Delivery Order</h4>
                        </div>
                        <div class="card-body">
                            <p class="italic">
                                <small>{{__('db.The field labels marked with * are required input fields')}}.</small>
                            </p>
                            {!! Form::open(['route' => 'delivery-orders.store', 'method' => 'post', 'files' => true, 'id' => 'delivery-order-form']) !!}
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
                                                            {{$quotation->reference_no}}
                                                        </option>
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
                                                            {{$purchase->reference_no}}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <!-- PO No and Quotation No Fields (shown when Manual Entry selected) -->
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

                                    <!-- Due Date, Customer, Status and Warehouse Row -->
                                    <div class="row mt-3">
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label>Delivery Date</label>
                                                <input type="text" name="delivery_date" class="form-control date"
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
                                                            <option value="{{$customer->id}}" {{ (isset($prefill_quotation) && $prefill_quotation && ($prefill_quotation['customer_id'] ?? null) == $customer->id) ? 'selected' : '' }}>
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
                                                <select name="status" class="form-control selectpicker">
                                                    <option value="pending">Pending</option>
                                                    <option value="submitted">Submitted</option>
                                                    <option value="completed">Completed</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label>{{__('db.Warehouse')}} *</label>
                                                <div class="input-group">
                                                    <select id="warehouse_id" name="warehouse_id" required
                                                        class="selectpicker form-control" data-live-search="true"
                                                        title="Select warehouse...">
                                                        @foreach($lims_warehouse_list as $warehouse)
                                                            <option value="{{$warehouse->id}}" {{ (isset($prefill_quotation) && $prefill_quotation && ($prefill_quotation['warehouse_id'] ?? null) == $warehouse->id) ? 'selected' : '' }}>{{$warehouse->name}}</option>
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
                                    </div>

                                    <div class="row" id="biller-customer-warehouse-row" style="display: none;">
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label>{{__('db.Biller')}} *</label>
                                                <div class="d-flex">
                                                    @php
                                                        $default_biller = $lims_biller_list->firstWhere('company_name', 'GLOBAL PRACTISE ENTERPRISE') ?? $lims_biller_list->first();
                                                    @endphp
                                                    <select required name="biller_id" id="biller_id"
                                                        class="selectpicker form-control flex-grow-1"
                                                        data-live-search="true" title="Select Biller...">
                                                        @foreach($lims_biller_list as $biller)
                                                            <option value="{{$biller->id}}" {{($default_biller && $biller->id == $default_biller->id) ? 'selected' : ''}}>
                                                                {{$biller->name . ' (' . $biller->company_name . ')'}}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-12 mt-2">
                                        <label>{{__('db.Select Product')}}</label>
                                        <div class="search-box input-group">
                                            <button class="btn btn-secondary"><i class="fa fa-barcode"></i></button>
                                            <input type="text" name="product_code_name" id="lims_productcodeSearch"
                                                placeholder="{{__('db.Please type product code and select')}}"
                                                class="form-control" />
                                            <div class="input-group-append">
                                                <button type="button" class="btn btn-sm btn-success" data-toggle="modal"
                                                    data-target="#quickAddProductModal"><i class="fa fa-plus"></i></button>
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
                                                    {{number_format(0, $general_setting->decimal, '.', '')}}
                                                </th>
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
                                        <input type="number" name="order_discount" class="form-control" step="any"
                                            value="{{ (isset($prefill_quotation) && $prefill_quotation) ? ($prefill_quotation['order_discount'] ?? '') : '' }}">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>{{__('db.Shipping Cost')}}</label>
                                        <input type="number" name="shipping_cost" class="form-control" step="any"
                                            value="{{ (isset($prefill_quotation) && $prefill_quotation) ? ($prefill_quotation['shipping_cost'] ?? '') : '' }}">
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
                                        <textarea rows="5" name="note"
                                            class="form-control">{{ (isset($prefill_quotation) && $prefill_quotation) ? ($prefill_quotation['note'] ?? '') : '' }}</textarea>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label>Delivery To</label>
                                        <textarea name="delivery_address" class="form-control"
                                            rows="2">{{ (isset($prefill_quotation) && $prefill_quotation) ? ($prefill_quotation['delivery_address'] ?? '') : '' }}</textarea>
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
            $footer_order_tax_rate = ($prefill_quotation['order_tax_rate'] ?? 0);
            $footer_order_tax = $footer_total * $footer_order_tax_rate / 100;
            $footer_order_discount = ($prefill_quotation['order_discount'] ?? 0);
            $footer_shipping = ($prefill_quotation['shipping_cost'] ?? 0);
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

        <div id="editModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true"
            class="modal fade text-left">
            <div role="document" class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 id="modal_header" class="modal-title"></h5>
                        <button type="button" data-dismiss="modal" aria-label="Close" class="close"><span
                                aria-hidden="true"><i class="dripicons-cross"></i></span></button>
                    </div>
                    <div class="modal-body">
                        <form>
                            <div class="row modal-element">
                                <div class="col-md-4 form-group">
                                    <label>{{__('db.Quantity')}}</label>
                                    <input type="number" name="edit_qty" class="form-control" step="any">
                                </div>
                                <div class="col-md-4 form-group">
                                    <label>{{__('db.Unit Discount')}}</label>
                                    <input type="number" name="edit_discount" class="form-control" step="any">
                                </div>
                                <div class="col-md-4 form-group">
                                    <label>{{__('db.Unit Price')}}</label>
                                    <input type="number" name="edit_unit_price" class="form-control" step="any">
                                </div>
                                <?php
    $tax_name_all[] = 'No Tax';
    $tax_rate_all[] = 0;
    foreach ($lims_tax_list as $tax) {
        $tax_name_all[] = $tax->name;
        $tax_rate_all[] = $tax->rate;
    }
                                ?>           
                                <div class="col-md-4 form-group">
                                    <label>{{__('db.Tax Rate')}}</label>
                                    <select name="edit_tax_rate" class="form-control selectpicker">
                                        @foreach($tax_name_all as $key => $name)
                                            <option value="{{$key}}">{{$name}}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div id="edit_unit" class="col-md-4 form-group">
                                    <label>{{__('db.Product Unit')}}</label>
                                    <select name="edit_unit" class="form-control selectpicker">
                                    </select>
                                </div>
                            </div>
                            <button type="button" name="update_btn" class="btn btn-primary">{{__('db.update')}}</button>
                        </form>
                    </div>
                </div>
            </div>
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
        $(function () {
            $("ul#delivery-order").siblings('a').attr('aria-expanded', 'true').addClass('active');
            $("ul#delivery-order").addClass("show");
            $("ul#delivery-order #delivery-order-create-menu").addClass("active");
        });

        var lims_product_array = [];
        var product_code = [];
        var product_name = [];
        var product_qty = [];
        var product_type = [];
        var product_id = [];
        var product_list = [];
        var qty_list = [];
        var rowindex;
        var customer_group_rate;
        var row_product_price;
        var pos;
        var product_price = [];
        var product_discount = [];
        var tax_rate_arr = [];
        var tax_name = [];
        var tax_method = [];
        var unit_name = [];
        var unit_operator = [];
        var unit_operation_value = [];
        var is_imei = [];
        var product_warehouse_price = [];
        var expired_date = [];
        var is_embeded = [];
        var imei_number = [];
        var decimal = <?php echo json_encode($general_setting->decimal); ?>;
        var prefillQuotation = @json($prefill_quotation ?? null);
        var prefillProducts = @json($prefill_products ?? []);

        // Auto-load quotation if quotation_id is provided
        @if(isset($quotation_id) && $quotation_id)
            $(document).ready(function () {
                $('#quotation_id').val({{$quotation_id}}).selectpicker('refresh');
                // If server already prefilled rows, just refresh selectpickers and calculate totals
                if ($('table.order-list tbody tr').length > 0) {
                    $('#customer_id').selectpicker('refresh');
                    $('#warehouse_id').selectpicker('refresh');
                    $('#biller_id').selectpicker('refresh');
                    calculateTotal(false);
                } else {
                    // No server-side rows, load via AJAX
                    setTimeout(function () {
                        $('#quotation_id').trigger('change');
                    }, 200);
                }
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
            } else if ($(this).attr('id') === 'purchase_id' && purchase_id) {
                $('#quotation_id').val('').selectpicker('refresh');
            }

            // Update reference fields
            updateReferenceFields();
        });

        // Load from Quotation
        $('#quotation_id').on('change', function () {
            var quotation_id = $(this).val();
            if (quotation_id) {
                $.ajax({
                    url: "{{ url('delivery-orders/load-quotation') }}/" + quotation_id,
                    type: "GET",
                    dataType: "json",
                    success: function (response) {
                        if (response.success) {
                            applyQuotationPrefill(response.quotation, response.products || []);
                        }
                    },
                    error: function (xhr) {
                        console.error("Error loading quotation:", xhr.responseText);
                    }
                });
            } else {
                // Make fields editable and show + buttons (Manual Entry)
                $('#biller_id, #customer_id, #warehouse_id').prop('disabled', false).selectpicker('refresh');
                $('.quick-add-btn').show();

                // Clear products table for manual entry
                $('table.order-list tbody').html('');
                calculateTotal();
            }
        });

        function applyQuotationPrefill(quotation, products) {
            if (!quotation) return;

            // Set form fields
            $('#biller_id').val(quotation.biller_id).selectpicker('refresh');
            $('#customer_id').val(quotation.customer_id).selectpicker('refresh');
            $('#warehouse_id').val(quotation.warehouse_id).selectpicker('refresh');

            // Set order tax, discount, shipping, note from quotation
            $('select[name="order_tax_rate"]').val(quotation.order_tax_rate || 0);
            $('input[name="order_discount"]').val(quotation.order_discount || 0);
            $('input[name="shipping_cost"]').val(quotation.shipping_cost || 0);
            $('textarea[name="note"]').val(quotation.note || '');
            $('textarea[name="delivery_address"]').val(quotation.delivery_address || '');

            // Make fields readonly and hide + buttons
            $('#biller_id, #customer_id, #warehouse_id').prop('disabled', true).selectpicker('refresh');
            $('.quick-add-btn').hide();

            // Clear existing products
            $('table.order-list tbody').html('');

            // Add products to table
            if (products && products.length) {
                $.each(products, function (index, product) {
                    addProductRow(product);
                });
            }

            calculateTotal(false);
        }

        // Purchase-style server prefill (no AJAX) when coming from ?quotation_id=
        $(document).ready(function () {
            // If rows were already rendered server-side, just compute totals
            if ($('table.order-list tbody tr').length > 0) {
                calculateTotal(false);
            } else if (prefillQuotation && prefillProducts && prefillProducts.length) {
                applyQuotationPrefill(prefillQuotation, prefillProducts);
            }
        });

        // Load from Purchase Order
        $('#purchase_id').on('change', function () {
            var purchase_id = $(this).val();
            if (purchase_id) {
                $.ajax({
                    url: "{{ url('delivery-orders/load-purchase') }}/" + purchase_id,
                    type: "GET",
                    dataType: "json",
                    success: function (response) {
                        if (response.success) {
                            var quotation = response.quotation;
                            var products = response.products;

                            console.log("Purchase Order Data:", response);
                            console.log("Quotation:", quotation);
                            console.log("Products:", products);

                            // Set form fields
                            if (quotation.biller_id) {
                                $('#biller_id').val(quotation.biller_id).selectpicker('refresh');
                                $('#biller_id').prop('disabled', true).selectpicker('refresh');
                            }
                            if (quotation.customer_id) {
                                $('#customer_id').val(quotation.customer_id).selectpicker('refresh');
                            }
                            // Keep customer dropdown enabled for Purchase Order case
                            $('#customer_id').prop('disabled', false).selectpicker('refresh');

                            $('#warehouse_id').val(quotation.warehouse_id).selectpicker('refresh');
                            $('#warehouse_id').prop('disabled', true).selectpicker('refresh');

                            // Set order tax, discount, shipping, note
                            if (quotation.order_tax_rate !== undefined) {
                                $('select[name="order_tax_rate"]').val(quotation.order_tax_rate || 0).selectpicker('refresh');
                            }
                            if (quotation.order_discount !== undefined) {
                                $('input[name="order_discount"]').val(quotation.order_discount || 0);
                            }
                            if (quotation.shipping_cost !== undefined) {
                                $('input[name="shipping_cost"]').val(quotation.shipping_cost || 0);
                            }
                            if (quotation.note !== undefined) {
                                $('textarea[name="note"]').val(quotation.note || '');
                            }
                            if (quotation.delivery_address !== undefined) {
                                $('textarea[name="delivery_address"]').val(quotation.delivery_address || '');
                            }

                            // Hide + buttons
                            $('.quick-add-btn').hide();

                            // Clear existing products
                            $('table.order-list tbody').html('');

                            // Add products to table
                            if (products && products.length > 0) {
                                $.each(products, function (index, product) {
                                    console.log("Adding product:", product);
                                    addProductRow(product);
                                });
                            } else {
                                console.warn("No products found in purchase order");
                            }

                            calculateTotal(true);
                        } else {
                            console.error("Response not successful:", response);
                        }
                    },
                    error: function (xhr) {
                        console.error("Error loading purchase order:", xhr.responseText);
                    }
                });
            } else {
                // Make fields editable and show + buttons (Manual Entry)
                $('#biller_id, #customer_id, #warehouse_id').prop('disabled', false).selectpicker('refresh');
                $('.quick-add-btn').show();

                // Clear products table for manual entry
                $('table.order-list tbody').html('');
                calculateTotal();
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

        // Product search autocomplete (JSON-safe — special chars in product names break inline JS)
        var lims_product_code = @json($lims_product_autocomplete ?? []);

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
        }

        // Recalculate on input change
        $(document).on('change input', '.qty, .net_unit_price, .net_unit_price_input, .discount, .discount_input, select[name="order_tax_rate"], input[name="order_discount"], input[name="shipping_cost"]', function () {
            calculateTotal();
        });

        // Delete row
        $(document).on('click', '.ibtnDel', function () {
            $(this).closest('tr').remove();
            calculateTotal();
        });

        // Form submit handler - re-enable disabled fields so their values are submitted
        $('#delivery-order-form').on('submit', function () {
            calculateTotal(false);
            // Temporarily enable disabled selects so their values are submitted
            $('#biller_id, #customer_id, #warehouse_id').prop('disabled', false);
            // Remove hidden duplicates if they exist
            $('input[name="biller_id"][type="hidden"]').remove();
            $('input[name="customer_id"][type="hidden"]').remove();
            $('input[name="warehouse_id"][type="hidden"]').remove();
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

        // Add Line Item button functionality
        $(function () {
        $('#delivery-order-form #add-line-item').off('click').on('click', function () {
            // Check customer and warehouse selection (bootstrap-select compatible)
            var customer_id = $('#customer_id').val();
            var warehouse_id = $('#warehouse_id').val();

            if (!customer_id || customer_id === '0') {
                alert('Please select Customer first!');
                return false;
            }
            else if (!warehouse_id || warehouse_id === '0') {
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
                tax_rate_arr.splice(rowindex, 0, 0);
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
            }).fail(function () {
                alert('Could not generate product code. Please try again.');
            });
        });
        });
    </script>
    <script type="text/javascript">
        // Guaranteed recalculation after everything loads
        $(window).on('load', function() {
            if ($('table.order-list tbody tr').length > 0) {
                calculateTotal(false);
            }
        });
    </script>
@endpush