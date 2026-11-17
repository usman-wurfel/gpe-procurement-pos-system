@extends('backend.layout.main') @section('content')

@include('backend.partials.line_item_product_name_autosize')
<x-error-message key="not_permitted" />

<section class="forms">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header d-flex align-items-center">
                        <h4>{{__('db.Update Quotation')}}</h4>
                    </div>
                    <div class="card-body">
                        <p class="italic"><small>{{__('db.The field labels marked with * are required input fields')}}.</small></p>
                        {!! Form::open(['route' => ['quotations.update', $lims_quotation_data->id], 'method' => 'put', 'files' => true, 'id' => 'payment-form']) !!}
                        <div class="row">
                            <div class="col-md-12">
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label>{{__('db.reference')}}</label>
                                            <p><strong>{{ $lims_quotation_data->reference_no }}</strong></p>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>{{__('db.Biller')}} * <small class="text-muted">(Quotation is generated with the reference of this person)</small></label>
                                            <div class="d-flex">
                                                <input type="hidden" name="biller_id_hidden" value="{{$lims_quotation_data->biller_id}}" />
                                                @php
                                                    $default_biller = $lims_biller_list->firstWhere('company_name', 'GLOBAL PRACTISE ENTERPRISE') ?? $lims_biller_list->first();
                                                    $selected_biller_id = $lims_quotation_data->biller_id ?? ($default_biller ? $default_biller->id : null);
                                                @endphp
                                                <select required name="biller_id" id="biller-id" class="selectpicker form-control flex-grow-1" data-live-search="true" title="Select Biller...">
                                                    @foreach($lims_biller_list as $biller)
                                                    <option value="{{$biller->id}}" {{$selected_biller_id == $biller->id ? 'selected' : ''}}>{{$biller->name . ' (' . $biller->company_name . ')'}}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>{{__('db.customer')}} *</label>
                                            <div class="input-group">
                                                <input type="hidden" name="customer_id_hidden" value="{{ $lims_quotation_data->customer_id }}" />
                                                <select required id="customer_id" name="customer_id" class="selectpicker form-control" data-live-search="true" title="Select customer...">
                                                    @foreach($lims_customer_list as $customer)
                                                    <option value="{{$customer->id}}" {{$lims_quotation_data->customer_id == $customer->id ? 'selected' : ''}}>{{$customer->name . ' (' . $customer->company_name . ')'}}</option>
                                                    @endforeach
                                                </select>
                                                <div class="input-group-append">
                                                    <button type="button" class="btn btn-default btn-sm" data-toggle="modal" data-target="#quickAddCustomerModal"><i class="fa fa-plus"></i></button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>{{__('db.Warehouse')}} *</label>
                                            <div class="input-group">
                                                <input type="hidden" name="warehouse_id_hidden" value="{{$lims_quotation_data->warehouse_id}}" />
                                                <select required name="warehouse_id" id="warehouse_id" class="selectpicker form-control" data-live-search="true" title="Select warehouse...">
                                                    @foreach($lims_warehouse_list as $warehouse)
                                                    <option value="{{$warehouse->id}}" {{$lims_quotation_data->warehouse_id == $warehouse->id ? 'selected' : ''}}>{{$warehouse->name}}</option>
                                                    @endforeach
                                                </select>
                                                <div class="input-group-append">
                                                    <button type="button" class="btn btn-default btn-sm" data-toggle="modal" data-target="#quickAddWarehouseModal"><i class="fa fa-plus"></i></button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="row mt-3">
                                    <div class="col-md-12">
                                        <label>{{__('db.Select Product')}}</label>
                                        <div class="search-box input-group">
                                            <button type="button" class="btn btn-secondary btn-lg"><i class="fa fa-barcode"></i></button>
                                            <input type="text" name="product_code_name" id="lims_productcodeSearch" placeholder="{{__('db.Please type product code and select')}}" class="form-control" />
                                            <div class="input-group-append">
                                                <button type="button" class="btn btn-sm btn-success" data-toggle="modal" data-target="#quickAddProductModal"><i class="fa fa-plus"></i></button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="row mt-4">
                                    <div class="col-md-12">
                                        <h5>{{__('db.Order Table')}} *</h5>
                                        <div class="table-responsive mt-3 quotation-order-table-wrap">
                                            <table id="myTable" class="table table-hover order-list">
                                                <thead>
                                                    <tr>
                                                        <th>{{__('db.name')}}</th>
                                                        <th>{{__('db.Code')}}</th>
                                                        <th style="min-width:150px">{{__('db.Unit')}}</th>
                                                        <th>{{__('db.Quantity')}}</th>
                                                        <th>{{__('db.Net Unit Price')}}</th>
                                                        <th>{{__('db.Discount')}}</th>
                                                        <th>{{__('db.Tax')}}</th>
                                                        <th>{{__('db.Subtotal')}}</th>
                                                        <th><i class="dripicons-trash"></i></th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php
                                                    $temp_unit_name = [];
                                                    $temp_unit_operator = [];
                                                    $temp_unit_operation_value = [];
                                                    ?>
                                                    @foreach($lims_product_quotation_data as $product_quotation)
                                                    <?php
                                                        $product_data = DB::table('products')->find($product_quotation->product_id);
                                                    ?>
                                                    @if(!$product_data)
                                                        @continue
                                                    @endif
                                                    <tr>
                                                    <?php
                                                        if($product_quotation->variant_id && $product_data && isset($product_data->id)) {
                                                            $product_variant_data = \App\Models\ProductVariant::select('id', 'item_code')->FindExactProduct($product_data->id, $product_quotation->variant_id)->first();
                                                            $product_variant_id = $product_variant_data ? $product_variant_data->id : null;
                                                            if($product_variant_data) {
                                                                $product_data->code = $product_variant_data->item_code;
                                                            }
                                                        }
                                                        else
                                                            $product_variant_id = null;
                                                        $customer = DB::table('customers')->find($lims_quotation_data->customer_id);

                                                        $customer_group = $customer ? DB::table('customer_groups')->find($customer->customer_group_id) : null;

                                                        // Prevent division by zero
                                                        $qty_val = $product_quotation->qty > 0 ? $product_quotation->qty : 1;
                                                        
                                                        // Check product_data exists before accessing properties
                                                        $tax_method = ($product_data && isset($product_data->tax_method)) ? $product_data->tax_method : 1;
                                                        
                                                        if($tax_method == 1){
                                                            $product_price = $product_quotation->net_unit_price + ($product_quotation->discount / $qty_val);
                                                        }
                                                        elseif ($tax_method == 2) {
                                                            $product_price = ($product_quotation->total / $qty_val) + ($product_quotation->discount / $qty_val);
                                                        }
                                                        else {
                                                            $product_price = $product_quotation->net_unit_price;
                                                        }

                                                        $tax = DB::table('taxes')->where('rate', $product_quotation->tax_rate)->first();

                                                        $unit_name = array();
                                                        $unit_operator = array();
                                                        $unit_operation_value = array();
                                                        if($product_data && isset($product_data->type) && $product_data->type == 'standard' && isset($product_data->unit_id)){
                                                            $units = DB::table('units')->where('base_unit', $product_data->unit_id)->orWhere('id', $product_data->unit_id)->get();

                                                            foreach($units as $unit) {
                                                                if($product_quotation->sale_unit_id == $unit->id) {
                                                                    array_unshift($unit_name, $unit->unit_name);
                                                                    array_unshift($unit_operator, $unit->operator);
                                                                    array_unshift($unit_operation_value, $unit->operation_value);
                                                                }
                                                                else {
                                                                    $unit_name[]  = $unit->unit_name;
                                                                    $unit_operator[] = $unit->operator;
                                                                    $unit_operation_value[] = $unit->operation_value;
                                                                }
                                                            }

                                                            // Only process if units exist
                                                            if(count($unit_operator) > 0 && isset($unit_operator[0]) && isset($unit_operation_value[0])) {
                                                                if($unit_operator[0] == '*' && $unit_operation_value[0] != 0){
                                                                    $product_price = $product_price / $unit_operation_value[0];
                                                                }
                                                                elseif($unit_operator[0] == '/'){
                                                                    $product_price = $product_price * $unit_operation_value[0];
                                                                }
                                                            }
                                                        }
                                                        
                                                        // If no units found, set defaults
                                                        if(empty($unit_name)) {
                                                            $unit_name[] = 'n/a';
                                                            $unit_operator[] = '*';
                                                            $unit_operation_value[] = 1;
                                                        }
                                                        
                                                        if($product_data && isset($product_data->type) && $product_data->type != 'standard') {
                                                            $unit_name = ['n/a'];
                                                            $unit_operator = ['n/a'];
                                                            $unit_operation_value = ['n/a'];
                                                        }
                                                        $temp_unit_name = $unit_name = implode(",",$unit_name) . ',';

                                                        $temp_unit_operator = $unit_operator = implode(",",$unit_operator) .',';

                                                        $temp_unit_operation_value = $unit_operation_value =  implode(",",$unit_operation_value) . ',';
                                                        $product_batch_data = \App\Models\ProductBatch::select('batch_no')->find($product_quotation->product_batch_id);
                                                    ?>
                                                        <td>{{$product_data->name ?? 'N/A'}} <button type="button" class="edit-product btn btn-link" data-toggle="modal" data-target="#editModal"> <i class="dripicons-document-edit"></i></button> </td>
                                                        <td>{{$product_data->code ?? 'N/A'}}</td>
                                                        <td class="quotation-unit-cell">
                                                            @php
                                                                $row_units = \App\Models\Unit::where('is_active', true)->pluck('unit_name')->toArray();
                                                                $selected_unit_model = \App\Models\Unit::find($product_quotation->sale_unit_id);
                                                                $selected_row_unit = $selected_unit_model->unit_name ?? (explode(',', rtrim($unit_name, ','))[0] ?? 'EA');
                                                            @endphp
                                                            <select class="form-control sale-unit-select" name="sale_unit[]" data-live-search="true" data-size="8" title="Select unit">
                                                                @foreach($row_units as $row_unit)
                                                                    <option value="{{$row_unit}}" {{$selected_row_unit == $row_unit ? 'selected' : ''}}>{{$row_unit}}</option>
                                                                @endforeach
                                                                @if($selected_row_unit && !in_array($selected_row_unit, $row_units, true))
                                                                    <option value="{{$selected_row_unit}}" selected>{{$selected_row_unit}}</option>
                                                                @endif
                                                            </select>
                                                        </td>
                                                        <td><input type="number" class="form-control qty" name="qty[]" value="{{$product_quotation->qty}}" step="any" required/></td>
                                                        <td class="net_unit_price">{{ number_format((float)$product_quotation->net_unit_price, $general_setting->decimal, '.', '')}} </td>
                                                        <td class="discount">{{ number_format((float)$product_quotation->discount, $general_setting->decimal, '.', '')}}</td>
                                                        <td class="tax">{{ number_format((float)$product_quotation->tax, $general_setting->decimal, '.', '')}}</td>
                                                        <td class="sub-total">{{ number_format((float)$product_quotation->total, $general_setting->decimal, '.', '')}}</td>
                                                        <td><button type="button" class="ibtnDel btn btn-md btn-danger"><i class="dripicons-trash"></i></button>
                                                        <input type="hidden" class="product-name" name="product_name[]" value="{{$product_data->name ?? ''}}"/>
                                                        <input type="hidden" class="product-id" name="product_id[]" value="{{$product_data->id ?? 0}}"/>
                                                        <input type="hidden" name="product_variant_id[]" value="{{$product_variant_id}}"/>
                                                        <input type="hidden" class="product-code" name="product_code[]" value="{{$product_data->code ?? ''}}"/>
                                                        <input type="hidden" class="product-price" name="product_price[]" value="{{$product_price}}"/>
                                                        <input type="hidden" class="sale-unit-catalog" value="{{$unit_name}}"/>
                                                        <input type="hidden" class="sale-unit-operator" value="{{$unit_operator}}"/>
                                                        <input type="hidden" class="sale-unit-operation-value" value="{{$unit_operation_value}}"/>
                                                        <input type="hidden" class="net_unit_price" name="net_unit_price[]" value="{{$product_quotation->net_unit_price}}" />
                                                        <input type="hidden" class="discount-value" name="discount[]" value="{{$product_quotation->discount}}" />
                                                        <input type="hidden" class="tax-rate" name="tax_rate[]" value="{{$product_quotation->tax_rate}}"/>
                                                        @if($tax)
                                                        <input type="hidden" class="tax-name" value="{{$tax->name}}" />
                                                        @else
                                                        <input type="hidden" class="tax-name" value="No Tax" />
                                                        @endif
                                                        <input type="hidden" class="tax-method" value="{{$product_data->tax_method ?? 1}}"/>
                                                        <input type="hidden" class="tax-value" name="tax[]" value="{{$product_quotation->tax}}" />
                                                        <input type="hidden" class="subtotal-value" name="subtotal[]" value="{{$product_quotation->total}}" />
                                                    </tr>
                                                    @endforeach
                                                </tbody>
                                                <tfoot class="tfoot active">
                                                    <th colspan="2">{{__('db.Total')}}</th>
                                                    <th></th>
                                                    <th id="total-qty">{{$lims_quotation_data->total_qty}}</th>
                                                    <th></th>
                                                    <th id="total-discount">{{ number_format((float)$lims_quotation_data->total_discount, $general_setting->decimal, '.', '')}}</th>
                                                    <th id="total-tax">{{ number_format((float)$lims_quotation_data->total_tax, $general_setting->decimal, '.', '')}}</th>
                                                    <th id="total">{{ number_format((float)$lims_quotation_data->total_price, $general_setting->decimal, '.', '')}}</th>
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
                                        <div class="form-group">
                                            <input type="hidden" name="total_qty" value="{{$lims_quotation_data->total_qty}}" />
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="form-group">
                                            <input type="hidden" name="total_discount" value="{{$lims_quotation_data->total_discount}}" />
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="form-group">
                                            <input type="hidden" name="total_tax" value="{{$lims_quotation_data->total_tax}}" />
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="form-group">
                                            <input type="hidden" name="total_price" value="{{$lims_quotation_data->total_price}}" />
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="form-group">
                                            <input type="hidden" name="item" value="{{$lims_quotation_data->item}}" />
                                            <input type="hidden" name="order_tax" value="{{$lims_quotation_data->order_tax}}"/>
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="form-group">
                                            <input type="hidden" name="grand_total" value="{{$lims_quotation_data->grand_total}}" />
                                        </div>
                                    </div>
                                </div>
                                <div class="row mt-2">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>{{__('db.Order Tax')}}</label>
                                            <input type="hidden" name="order_tax_rate_hidden" value="{{$lims_quotation_data->order_tax_rate}}">
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
                                            <label>
                                                <strong>{{__('db.Order Discount')}}</strong>
                                            </label>
                                            <input type="number" name="order_discount" class="form-control" value="{{$lims_quotation_data->order_discount}}" step="any"/>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>
                                                <strong>{{__('db.Shipping Cost')}}</strong>
                                            </label>
                                            <input type="number" name="shipping_cost" class="form-control" value="{{$lims_quotation_data->shipping_cost}}" step="any"/>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>{{__('db.Attach Document')}}</label>
                                            <i class="dripicons-question" data-toggle="tooltip" title="Only jpg, jpeg, png, gif, pdf, csv, docx, xlsx and txt file is supported"></i>
                                            <input type="file" name="document" class="form-control" />
                                            @if($errors->has('extension'))
                                                <span>
                                                   <strong>{{ $errors->first('extension') }}</strong>
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>{{__('db.status')}} *</label>
                                            <input type="hidden" name="quotation_status_hidden" value="{{$lims_quotation_data->quotation_status}}">
                                            <select name="quotation_status" class="form-control">
                                                <option value="1">{{__('db.Pending')}}</option>
                                                <option value="2">{{__('db.Sent')}}</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>TERMS *</label>
                                            <select name="terms" class="form-control" required>
                                                <option value="CASH" {{ ($lims_quotation_data->terms ?? 'CASH') == 'CASH' ? 'selected' : '' }}>CASH</option>
                                                <option value="NET 30" {{ ($lims_quotation_data->terms ?? '') == 'NET 30' ? 'selected' : '' }}>NET 30</option>
                                                <option value="NET 14" {{ ($lims_quotation_data->terms ?? '') == 'NET 14' ? 'selected' : '' }}>NET 14</option>
                                                <option value="B2B" {{ ($lims_quotation_data->terms ?? '') == 'B2B' ? 'selected' : '' }}>B2B</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                	<div class="col-md-12">
                                		<div class="form-group">
                                			<label>{{__('db.Note')}}</label>
                                			<textarea rows="5" class="form-control" name="note" >{{ $lims_quotation_data->note }}</textarea>
                                		</div>
                                	</div>
                                </div>
                                <div class="row">
                                	<div class="col-md-12">
                                		<div class="form-group">
                                			<label>Terms & Conditions</label>
                                			<textarea rows="5" class="form-control" name="terms_conditions">{{ $lims_quotation_data->terms_conditions ?? "Payment: 30 days. 100% charge on order cancellation penalty once PO issued to GP.\nDelivery: < 4 weeks\nWarranty: Manufacturer's warranty Where Applicable\nValidity: 45 days" }}</textarea>
                                		</div>
                                	</div>
                                </div>
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label>Delivery To</label>
                                            <textarea name="delivery_address" class="form-control" rows="2">{{$lims_quotation_data->delivery_address}}</textarea>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <input type="submit" value="{{__('db.submit')}}" class="btn btn-primary" id="submit-button">
                                </div>
                            </div>
                        </div>
                        {!! Form::close() !!}
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="container-fluid">
        <table class="table table-bordered table-condensed totals">
            <td><strong>{{__('db.Items')}}</strong>
                <span class="pull-right" id="item">{{number_format(0, $general_setting->decimal, '.', '')}}</span>
            </td>
            <td><strong>{{__('db.Total')}}</strong>
                <span class="pull-right" id="subtotal">{{number_format(0, $general_setting->decimal, '.', '')}}</span>
            </td>
            <td><strong>{{__('db.Order Tax')}}</strong>
                <span class="pull-right" id="order_tax">{{number_format(0, $general_setting->decimal, '.', '')}}</span>
            </td>
            <td><strong>{{__('db.Order Discount')}}</strong>
                <span class="pull-right" id="order_discount">{{number_format(0, $general_setting->decimal, '.', '')}}</span>
            </td>
            <td><strong>{{__('db.Shipping Cost')}}</strong>
                <span class="pull-right" id="shipping_cost">{{number_format(0, $general_setting->decimal, '.', '')}}</span>
            </td>
            <td><strong>{{__('db.grand total')}}</strong>
                <span class="pull-right" id="grand_total">{{number_format(0, $general_setting->decimal, '.', '')}}</span>
            </td>
        </table>
    </div>
    <div id="editModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true" class="modal fade text-left">
        <div role="document" class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 id="modal_header" class="modal-title"></h5>
                    <button type="button" data-dismiss="modal" aria-label="Close" class="close"><span aria-hidden="true"><i class="dripicons-cross"></i></span></button>
                </div>
                <div class="modal-body">
                    <form>
                        <div class="form-group">
                            <label>{{__('db.name')}}</label>
                            <input type="text" name="edit_product_name" class="form-control">
                        </div>
                        <div class="form-group">
                            <label>{{__('db.Quantity')}}</label>
                            <input type="number" name="edit_qty" class="form-control" step="any">
                        </div>
                        <div class="form-group">
                            <label>{{__('db.Unit Discount')}}</label>
                            <input type="number" name="edit_discount" class="form-control" step="any">
                        </div>
                        <div class="form-group">
                            <label>{{__('db.Unit Price')}}</label>
                            <input type="number" name="edit_unit_price" class="form-control" step="any">
                        </div>
                        <?php
                            $tax_name_all[] = 'No Tax';
                            $tax_rate_all[] = 0;
                            foreach($lims_tax_list as $tax) {
                                $tax_name_all[] = $tax->name;
                                $tax_rate_all[] = $tax->rate;
                            }
                        ?>
                            <div class="form-group">
                                <label>{{__('db.Tax Rate')}}</label>
                                <select name="edit_tax_rate" class="form-control selectpicker">
                                    @foreach($tax_name_all as $key => $name)
                                    <option value="{{$key}}">{{$name}}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div id="edit_unit" class="form-group">
                                <label>{{__('db.Product Unit')}}</label>
                                <select name="edit_unit" class="form-control sale-unit-select" data-live-search="true" data-size="8" title="Select unit"></select>
                            </div>
                            <button type="button" name="update_btn" class="btn btn-primary">{{__('db.update')}}</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Add Modals - Same as create page -->
    @include('backend.quotation.partials.quick_add_modals')
</section>


@endsection

@include('backend.quotation.partials.unit_select2_assets')

@push('scripts')
@include('backend.partials.lims_product_autocomplete_parse')
<script type="text/javascript">
    $("ul#quotation").siblings('a').attr('aria-expanded','true');
    $("ul#quotation").addClass("show");

// array data depend on warehouse
var lims_product_array = [];
var product_code = [];
var product_name = [];
var product_qty = [];
var product_type = [];
var product_id = [];
var product_list = [];
var qty_list = [];

// array data with selection
var product_price = [];
var product_discount = [];
var tax_rate = [];
var tax_name = [];
var tax_method = [];
var unit_name = [];
var unit_operator = [];
var unit_operation_value = [];
var is_imei = [];

// temporary array
var temp_unit_name = [];
var temp_unit_operator = [];
var temp_unit_operation_value = [];

var exist_code = [];
var exist_qty = [];
var rowindex;
var customer_group_rate;
var row_product_price;
var pos;
var currency = <?php echo json_encode($currency) ?>;
var without_stock = <?php echo json_encode($general_setting->without_stock) ?>;
var allSystemUnits = @json(\App\Models\Unit::where('is_active', true)->pluck('unit_name')->values());
@include('backend.quotation.partials.unit_combobox_scripts')
var rownumber = $('table.order-list tbody tr:last').index();

for(rowindex  =0; rowindex <= rownumber; rowindex++){

    product_price.push(parseFloat($('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ')').find('.product-price').val()));
    exist_code.push($('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ')').find('td:nth-child(2)').text());
    var total_discount = parseFloat($('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ')').find('.discount').text());
    var quantity = parseFloat($('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ')').find('.qty').val());
    exist_qty.push(quantity);
    product_discount.push((total_discount / quantity).toFixed({{$general_setting->decimal}}));
    tax_rate.push(parseFloat($('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ')').find('.tax-rate').val()));
    tax_name.push($('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ')').find('.tax-name').val());
    tax_method.push($('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ')').find('.tax-method').val());
    temp_unit_name = $('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ')').find('.sale-unit-catalog').val().split(',');
    unit_name.push($('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ')').find('.sale-unit-catalog').val());
    unit_operator.push($('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ')').find('.sale-unit-operator').val());
    unit_operation_value.push($('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ')').find('.sale-unit-operation-value').val());
    var rowSelectedUnit = quotationGetRowUnit($('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ')')) || temp_unit_name[0];
}

$('.selectpicker').selectpicker({
    style: 'btn-link',
});

$('[data-toggle="tooltip"]').tooltip();

//assigning value
$('select[name="customer_id"]').val($('input[name="customer_id_hidden"]').val());
$('select[name="warehouse_id"]').val($('input[name="warehouse_id_hidden"]').val());
$('select[name="biller_id"]').val($('input[name="biller_id_hidden"]').val());
$('select[name="order_tax_rate"]').val($('input[name="order_tax_rate_hidden"]').val());
$('select[name="quotation_status"]').val($('input[name="quotation_status_hidden"]').val());
$('.selectpicker').selectpicker('refresh');

$('#item').text($('input[name="item"]').val() + '(' + $('input[name="total_qty"]').val() + ')');
$('#subtotal').text(parseFloat($('input[name="total_price"]').val()).toFixed({{$general_setting->decimal}}));
$('#order_tax').text(parseFloat($('input[name="order_tax"]').val()).toFixed({{$general_setting->decimal}}));
if(!$('input[name="order_discount"]').val())
    $('input[name="order_discount"]').val('{{number_format(0, $general_setting->decimal, '.', '')}}');
$('#order_discount').text(parseFloat($('input[name="order_discount"]').val()).toFixed({{$general_setting->decimal}}));
if(!$('input[name="shipping_cost"]').val())
    $('input[name="shipping_cost"]').val('{{number_format(0, $general_setting->decimal, '.', '')}}');
$('#shipping_cost').text(parseFloat($('input[name="shipping_cost"]').val()).toFixed({{$general_setting->decimal}}));
$('#grand_total').text(parseFloat($('input[name="grand_total"]').val()).toFixed({{$general_setting->decimal}}));

var id = $('select[name="customer_id"]').val();
$.get('../getcustomergroup/' + id, function(data) {
    customer_group_rate = (data / 100);
});

var id = $('select[name="warehouse_id"]').val();
$.get('../getproduct/' + id, function(data) {
    lims_product_array = [];
    product_code = data[0];
    product_name = data[1];
    product_qty = data[2];
    product_type = data[3];
    product_id = data[4];
    product_list = data[5];
    qty_list = data[6];
    product_warehouse_price = data[7];

    is_embeded = data[11];
    imei_number = data[12];
    $.each(product_code, function(index) {
        lims_product_array.push(product_code[index]+'|'+product_name[index]+'|'+imei_number[index]+'|'+is_embeded[index]);
    });
});

$('select[name="customer_id"]').on('change', function() {
    var id = $(this).val();
    $.get('../getcustomergroup/' + id, function(data) {
        customer_group_rate = (data / 100);
    });
});

$('select[name="warehouse_id"]').on('change', function() {
    var id = $(this).val();
    $.get('../getproduct/' + id, function(data) {
        lims_product_array = [];
        product_code = data[0];
        product_name = data[1];
        product_qty = data[2];
        product_type = data[3];
        product_id = data[4];
        product_list = data[5];
        qty_list = data[6];
        product_warehouse_price = data[7];
        is_embeded = data[11];
        imei_number = data[12];
        $.each(product_code, function(index) {
            lims_product_array.push(product_code[index]+'|'+product_name[index]+'|'+imei_number[index]+'|'+is_embeded[index]);
        });
    });
});

$('#lims_productcodeSearch').on('input', function(){
    var customer_id = $('#customer_id').val();
    var warehouse_id = $('select[name="warehouse_id"]').val();
    temp_data = $('#lims_productcodeSearch').val();
    if(!customer_id){
        $('#lims_productcodeSearch').val(temp_data.substring(0, temp_data.length - 1));
        alert('Please select Customer!');
    }
    else if(!warehouse_id){
        $('#lims_productcodeSearch').val(temp_data.substring(0, temp_data.length - 1));
        alert('Please select Warehouse!');
    }

});

var lims_productcodeSearch = $('#lims_productcodeSearch');

lims_productcodeSearch.autocomplete({
    source: function(request, response) {
        var matcher = new RegExp(".?" + $.ui.autocomplete.escapeRegex(request.term), "i");
        response($.grep(lims_product_array, function(item) {
            return matcher.test(item);
        }));
    },
    response: function(event, ui) {
        if (ui.content.length == 1) {
            var data = ui.content[0].value;
            $(this).autocomplete( "close" );
            productSearch(data);
        };
    },
    select: function(event, ui) {
        var data = ui.item.value;
        productSearch(data);
    }
});

//Change quantity
$("#myTable").on('input', '.qty', function() {
    rowindex = $(this).closest('tr').index();
    if($(this).val() < 1 && $(this).val() != '') {
      $('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ') .qty').val(1);
      alert("Quantity can't be less than 1");
    }
    checkQuantity($(this).val(), true);
});

$("#myTable").on("change", ".batch-no", function () {
    rowindex = $(this).closest('tr').index();
    var product_id = $('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ')').find('.product-id').val();
    var warehouse_id = $('#warehouse_id').val();
    $.get('../../check-batch-availability/' + product_id + '/' + $(this).val() + '/' + warehouse_id, function(data) {
        if(data['message'] != 'ok') {
            alert(data['message']);
            $('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ')').find('.batch-no').val('');
            $('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ')').find('.product-batch-id').val('');
        }
        else {
            $('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ')').find('.product-batch-id').val(data['product_batch_id']);
            code = $('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ')').find('.product-code').val();
            pos = product_code.indexOf(code);
            product_qty[pos] = data['qty'];
        }
    });
});

//Delete product
$("table.order-list tbody").on("click", ".ibtnDel", function(event) {
    rowindex = $(this).closest('tr').index();
    product_price.splice(rowindex, 1);
    product_discount.splice(rowindex, 1);
    tax_rate.splice(rowindex, 1);
    tax_name.splice(rowindex, 1);
    tax_method.splice(rowindex, 1);
    unit_name.splice(rowindex, 1);
    unit_operator.splice(rowindex, 1);
    unit_operation_value.splice(rowindex, 1);
    $(this).closest("tr").remove();
    calculateTotal();
});

//Edit product
$("table.order-list").on("click", ".edit-product", function() {
    rowindex = $(this).closest('tr').index();
    edit();
});
  //update product
$('button[name="update_btn"]').on("click", function() {
    if(is_imei[rowindex]) {
        var imeiNumbers = '';
        $("#editModal .imei-numbers").each(function(i) {
            if (i)
                imeiNumbers += ','+ $(this).val();
            else
                imeiNumbers = $(this).val();
        });
        $('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ')').find('.imei-number').val(imeiNumbers);
    }
    
    var edit_discount = $('input[name="edit_discount"]').val();
    var edit_qty = $('input[name="edit_qty"]').val();
    var edit_unit_price = $('input[name="edit_unit_price"]').val();

    if (parseFloat(edit_discount) > parseFloat(edit_unit_price)) {
        alert('Invalid Discount Input!');
        return;
    }

    if(edit_qty < 1) {
        $('input[name="edit_qty"]').val(1);
        edit_qty = 1;
        alert("Quantity can't be less than 1");
    }

    var updatedProductName = $.trim($('input[name="edit_product_name"]').val() || '');
    if(!updatedProductName) {
        alert('Product name is required!');
        return;
    }

    var tax_rate_all = <?php echo json_encode($tax_rate_all) ?>;
    tax_rate[rowindex] = parseFloat(tax_rate_all[$('select[name="edit_tax_rate"]').val()]);
    tax_name[rowindex] = $('select[name="edit_tax_rate"] option:selected').text();
    if(product_type[pos] == 'standard'){
        var row_unit_operator = unit_operator[rowindex].slice(0, unit_operator[rowindex].indexOf(","));
        var row_unit_operation_value = unit_operation_value[rowindex].slice(0, unit_operation_value[rowindex].indexOf(","));


        if (row_unit_operator == '*') {
            product_price[rowindex] = $('input[name="edit_unit_price"]').val() / row_unit_operation_value;
        } else {
            product_price[rowindex] = $('input[name="edit_unit_price"]').val() * row_unit_operation_value;
        }


        var selectedUnitText = $.trim($('select[name="edit_unit"]').val() || '');
        var $row = $('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ')');
        quotationSetUnitValue($row.find('.sale-unit-select'), selectedUnitText);
        unit_name[rowindex] = selectedUnitText + ',';
    }
    else {
        product_price[rowindex] = $('input[name="edit_unit_price"]').val();
    }
    product_discount[rowindex] = $('input[name="edit_discount"]').val();
    var productCellHtml = updatedProductName + ' <button type="button" class="edit-product btn btn-link" data-toggle="modal" data-target="#editModal"> <i class="dripicons-document-edit"></i></button>';
    $('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ')').find('td:nth-child(1)').html(productCellHtml);
    $('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ')').find('.product-name').val(updatedProductName);
    checkQuantity(edit_qty, false);
});

$(window).keydown(function(e){
    if (e.which == 13) {
        var $targ = $(e.target);
        if (!$targ.is("textarea") && !$targ.is(":button,:submit")) {
            var focusNext = false;
            $(this).find(":input:visible:not([disabled],[readonly]), a").each(function(){
                if (this === e.target) {
                    focusNext = true;
                }
                else if (focusNext){
                    $(this).focus();
                    return false;
                }
            });
            return false;
        }
    }
});

$('#quotation-form').on('submit',function(e){
    var rownumber = $('table.order-list tbody tr:last').index();
    if (rownumber < 0) {
        alert("Please insert product to order table!")
        e.preventDefault();
    }
    else {
        $("#submit-button").prop('disabled', true);
    }
});

function productSearch(data){
    var parsed = typeof parseLimsProductAutocompleteValue === 'function' ? parseLimsProductAutocompleteValue(data) : null;
    if (!parsed) {
        alert('Invalid product data. Please select the product again.');
        return;
    }
    var code = parsed.code;
    var pre_qty = 0;
    var flag = true;

    $(".product-code").each(function(i) {
        if ($(this).val() == code) {
            rowindex = i;
            if(parsed.imei_number != 'null') {
                imeiNumbers = $('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ') .imei-number').val();
                imeiNumbersArray = imeiNumbers.split(",");
                // console.log('arra '+ rowindex);

                if(imeiNumbersArray.includes(parsed.imei_number)) {
                    alert('Same imei or serial number is not allowed!');
                    flag = false;
                    $('#lims_productcodeSearch').val('');
                }
            }
            pre_qty = $('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ') .qty').val();
        }
    });

    if(flag)
    {
        data += '?'+$('#customer_id').val()+'?'+(parseFloat(pre_qty) + 1);

        $.ajax({
            type: 'GET',
            url: '../lims_product_search',
            data: {
                data: data
            },
            success: function(data) {
                if (!data || !data.length || data[6] === undefined) {
                    alert('Product could not be loaded. Try again or check product code.');
                    return;
                }
                var flag = 1;
                if (pre_qty > 0) {
                    var qty = data[15];
                    $('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ') .qty').val(qty);
                    pos = product_code.indexOf(data[1]);
                    if(!data[11] && product_warehouse_price[pos]) {
                        product_price[rowindex] = parseFloat(product_warehouse_price[pos] * currency['exchange_rate']) + parseFloat(product_warehouse_price[pos] * currency['exchange_rate'] * customer_group_rate);
                    }
                    else{
                        product_price[rowindex] = parseFloat(data[2] * currency['exchange_rate']) + parseFloat(data[2] * currency['exchange_rate'] * customer_group_rate);
                    }
                    flag = 0;
                    checkQuantity(String(qty), true);
                    flag = 0;
                }
                $("input[name='product_code_name']").val('');
                if(flag){
                    var newRow = $("<tr>");
                    var cols = '';
                    temp_unit_name = (data[6]).split(',');
                    temp_unit_name.pop();
                    cols += '<td>' + data[0] + '<button type="button" class="edit-product btn btn-link" data-toggle="modal" data-target="#editModal"> <i class="dripicons-document-edit"></i></button></td>';
                    cols += '<td>' + data[1] + '</td>';
                    var hiddenUnit = (temp_unit_name[0] && temp_unit_name[0].trim() !== '') ? temp_unit_name[0] : quotationDefaultUnit();
                    cols += '<td class="quotation-unit-cell">' + quotationBuildUnitFieldHtml(hiddenUnit, temp_unit_name) + '</td>';
                    cols += '<td><input type="number" class="form-control qty" name="qty[]" value="1" step="any" required/></td>';
                    cols += '<td class="net_unit_price"></td>';
                    cols += '<td class="discount">{{number_format(0, $general_setting->decimal, '.', '')}}</td>';
                    cols += '<td class="tax"></td>';
                    cols += '<td class="sub-total"></td>';
                    cols += '<td><button type="button" class="ibtnDel btn btn-md btn-danger"><i class="dripicons-trash"></i></button></td>';
                    cols += '<input type="hidden" class="product-code" name="product_code[]" value="' + data[1] + '"/>';
                    cols += '<input type="hidden" class="product-id" name="product_id[]" value="' + data[9] + '"/>';
                    cols += '<input type="hidden" class="net_unit_price" name="net_unit_price[]" />';
                    cols += '<input type="hidden" class="discount-value" name="discount[]" />';
                    cols += '<input type="hidden" class="tax-rate" name="tax_rate[]" value="' + data[3] + '"/>';
                    cols += '<input type="hidden" class="tax-value" name="tax[]" />';
                    cols += '<input type="hidden" class="subtotal-value" name="subtotal[]" />';
                    cols += '<input type="hidden" class="imei-number" name="imei_number[]" value="'+data[18]+'" />';

                    newRow.append(cols);
                    $("table.order-list tbody").append(newRow);
                    quotationInitRowUnitSelect(newRow, hiddenUnit, temp_unit_name);
                    rowindex = newRow.index();
                    pos = product_code.indexOf(data[1]);
                    if(!data[11] && product_warehouse_price[pos]) {
                        product_price.splice(rowindex, 0, parseFloat(product_warehouse_price[pos] * currency['exchange_rate']) + parseFloat(product_warehouse_price[pos] * currency['exchange_rate'] * customer_group_rate));
                    }
                    else {
                        product_price.splice(rowindex, 0, parseFloat(data[2] * currency['exchange_rate']) + parseFloat(data[2] * currency['exchange_rate'] * customer_group_rate));
                    }
                    product_discount.splice(rowindex, 0, '{{number_format(0, $general_setting->decimal, '.', '')}}');
                    tax_rate.splice(rowindex, 0, parseFloat(data[3]));
                    tax_name.splice(rowindex, 0, data[4]);
                    tax_method.splice(rowindex, 0, data[5]);
                    unit_name.splice(rowindex, 0, data[6]);
                    unit_operator.splice(rowindex, 0, data[7]);
                    unit_operation_value.splice(rowindex, 0, data[8]);
                    is_imei.splice(rowindex, 0, data[13]);
                    checkQuantity(1, true);
                }
                else if(data[18]) {
                    var imeiNumbers = $('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ')').find('.imei-number').val();
                    if(imeiNumbers)
                        imeiNumbers += ','+data[18];
                    else
                        imeiNumbers = data[18];
                    $('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ')').find('.imei-number').val(imeiNumbers);
                }
            }
        });
    }
}

function edit(){
    $(".imei-section").remove();
    if(is_imei[rowindex]) {
        var imeiNumbers = $('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ')').find('.imei-number').val();
        if(imeiNumbers.length) {
            imeiArrays = imeiNumbers.split(",");
            htmlText = `<div class="col-md-8 form-group imei-section">
                        <label>IMEI or Serial Numbers</label>
                        <div class="table-responsive ml-2">
                            <table id="imei-table" class="table table-hover">
                                <tbody>`;
            for (var i = 0; i < imeiArrays.length; i++) {
                htmlText += `<tr>
                                <td>
                                    <input type="text" class="form-control imei-numbers" name="imei_numbers[]" value="`+imeiArrays[i]+`" />
                                </td>
                                <td>
                                    <button type="button" class="imei-del btn btn-sm btn-danger">X</button>
                                </td>
                            </tr>`;
            }
            htmlText += `</tbody>
                            </table>
                        </div>
                    </div>`;
            $("#editModal .modal-element").append(htmlText);
        }
    }

    var row_product_name = $('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ')').find('.product-name').val()
        || $('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ')').find('td:nth-child(1)').text();
    var row_product_code = $('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ')').find('td:nth-child(2)').text();
    $('#modal_header').text(row_product_name + '(' + row_product_code + ')');
    $('input[name="edit_product_name"]').val(row_product_name);

    var qty = $('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ')').find('.qty').val();
    $('input[name="edit_qty"]').val(qty);

    $('input[name="edit_discount"]').val(parseFloat(product_discount[rowindex]).toFixed({{$general_setting->decimal}}));

    var tax_name_all = <?php echo json_encode($tax_name_all) ?>;
    pos = tax_name_all.indexOf(tax_name[rowindex]);
    $('select[name="edit_tax_rate"]').val(pos);

    pos = product_code.indexOf(row_product_code);
    if(product_type[pos] == 'standard'){
        unitConversion();
        temp_unit_name = (unit_name[rowindex] || '').split(',');
        temp_unit_name.pop();
        temp_unit_operator = (unit_operator[rowindex]).split(',');
        temp_unit_operator.pop();
        temp_unit_operation_value = (unit_operation_value[rowindex]).split(',');
        temp_unit_operation_value.pop();

        var currentRowSelectedUnit = quotationGetRowUnit($('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ')'));
        quotationPopulateEditUnitFields(temp_unit_name, currentRowSelectedUnit);
        $("#edit_unit").show();
    }
    else{
        row_product_price = product_price[rowindex];
        $("#edit_unit").hide();
    }
    $('input[name="edit_unit_price"]').val(row_product_price.toFixed({{$general_setting->decimal}}));
    $('.selectpicker').selectpicker('refresh');
}

function checkQuantity(sale_qty, flag) {
    var row_product_code = $('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ')').find('td:nth-child(2)').text();
    pos = product_code.indexOf(row_product_code);
    if(without_stock == 'no') {
        if(product_type[pos] == 'standard'){
            var operator = unit_operator[rowindex].split(',');
            var operation_value = unit_operation_value[rowindex].split(',');
            if(operator[0] == '*')
                total_qty = sale_qty * operation_value[0];
            else if(operator[0] == '/')
                total_qty = sale_qty / operation_value[0];
            if (total_qty > parseFloat(product_qty[pos])) {
                alert('Quantity exceeds stock quantity!');
                if (flag) {
                    sale_qty = sale_qty.substring(0, sale_qty.length - 1);
                    $('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ')').find('.qty').val(sale_qty);
                }
                else {
                    edit();
                    return;
                }
            }
        }
        else if(product_type[pos] == 'combo'){
            child_id = product_list[pos].split(',');
            child_qty = qty_list[pos].split(',');
            $(child_id).each(function(index) {
                var position = product_id.indexOf(parseInt(child_id[index]));
                if( parseFloat(sale_qty * child_qty[index]) > product_qty[position] ) {
                    alert('Quantity exceeds stock quantity!');
                    if (flag) {
                        sale_qty = sale_qty.substring(0, sale_qty.length - 1);
                        $('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ')').find('.qty').val(sale_qty);
                    }
                    else {
                        edit();
                        flag = true;
                        return false;
                    }
                }
            });
        }
    }

    if(!flag){
        $('#editModal').modal('hide');
        $('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ')').find('.qty').val(sale_qty);
    }
    calculateRowProductData(sale_qty);
}

function calculateRowProductData(quantity) {
    if(product_type[pos] == 'standard')
        unitConversion();
    else
        row_product_price = product_price[rowindex];

    $('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ')').find('.discount').text((product_discount[rowindex] * quantity).toFixed({{$general_setting->decimal}}));
    $('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ')').find('.discount-value').val((product_discount[rowindex] * quantity).toFixed({{$general_setting->decimal}}));
    $('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ')').find('.tax-rate').val(tax_rate[rowindex].toFixed({{$general_setting->decimal}}));

    if (tax_method[rowindex] == 1) {
        var net_unit_price = row_product_price - product_discount[rowindex];
        var tax = net_unit_price * quantity * (tax_rate[rowindex] / 100);
        var sub_total = (net_unit_price * quantity) + tax;

        $('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ')').find('.net_unit_price').text(net_unit_price.toFixed({{$general_setting->decimal}}));
        $('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ')').find('.net_unit_price').val(net_unit_price.toFixed({{$general_setting->decimal}}));
        $('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ')').find('.tax').text(tax.toFixed({{$general_setting->decimal}}));
        $('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ')').find('.tax-value').val(tax.toFixed({{$general_setting->decimal}}));
        $('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ')').find('.sub-total').text(sub_total.toFixed({{$general_setting->decimal}}));
        $('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ')').find('.subtotal-value').val(sub_total.toFixed({{$general_setting->decimal}}));
    } else {
        var sub_total_unit = row_product_price - product_discount[rowindex];
        var net_unit_price = (100 / (100 + tax_rate[rowindex])) * sub_total_unit;
        var tax = (sub_total_unit - net_unit_price) * quantity;
        var sub_total = sub_total_unit * quantity;

        $('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ')').find('.net_unit_price').text(net_unit_price.toFixed({{$general_setting->decimal}}));
        $('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ')').find('.net_unit_price').val(net_unit_price.toFixed({{$general_setting->decimal}}));
        $('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ')').find('.tax').text(tax.toFixed({{$general_setting->decimal}}));
        $('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ')').find('.tax-value').val(tax.toFixed({{$general_setting->decimal}}));
        $('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ')').find('.sub-total').text(sub_total.toFixed({{$general_setting->decimal}}));
        $('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ')').find('.subtotal-value').val(sub_total.toFixed({{$general_setting->decimal}}));
    }
    calculateTotal();
}

function unitConversion() {
    var row_unit_operator = unit_operator[rowindex].slice(0, unit_operator[rowindex].indexOf(","));
    var row_unit_operation_value = unit_operation_value[rowindex].slice(0, unit_operation_value[rowindex].indexOf(","));

    if (row_unit_operator == '*') {
        row_product_price = product_price[rowindex] * row_unit_operation_value;
    } else {
        row_product_price = product_price[rowindex] / row_unit_operation_value;
    }
}

function calculateTotal() {
    //Sum of quantity
    var total_qty = 0;
    $(".qty").each(function() {

        if ($(this).val() == '') {
            total_qty += 0;
        } else {
            total_qty += parseFloat($(this).val());
        }
    });
    $("#total-qty").text(total_qty);
    $('input[name="total_qty"]').val(total_qty);

    //Sum of discount - use hidden input or text (remove commas)
    var total_discount = 0;
    $('table.order-list tbody tr').each(function() {
        var $row = $(this);
        var discountValue = parseFloat($row.find('.discount-value').val()) || 0;
        if(discountValue === 0) {
            var discountText = $row.find('.discount').text().trim();
            discountValue = parseFloat(discountText.replace(/,/g, '')) || 0;
        }
        total_discount += discountValue;
    });
    $("#total-discount").text(total_discount.toFixed({{$general_setting->decimal}}));
    $('input[name="total_discount"]').val(total_discount.toFixed({{$general_setting->decimal}}));

    //Sum of tax - use hidden input or text (remove commas)
    var total_tax = 0;
    $('table.order-list tbody tr').each(function() {
        var $row = $(this);
        var taxValue = parseFloat($row.find('.tax-value').val()) || 0;
        if(taxValue === 0) {
            var taxText = $row.find('.tax').text().trim();
            taxValue = parseFloat(taxText.replace(/,/g, '')) || 0;
        }
        total_tax += taxValue;
    });
    $("#total-tax").text(total_tax.toFixed({{$general_setting->decimal}}));
    $('input[name="total_tax"]').val(total_tax.toFixed({{$general_setting->decimal}}));

    //Sum of subtotal - use hidden input or text (remove commas)
    var total = 0;
    $('table.order-list tbody tr').each(function() {
        var $row = $(this);
        var subtotalValue = parseFloat($row.find('.subtotal-value').val()) || 0;
        if(subtotalValue === 0) {
            var subtotalText = $row.find('.sub-total').text().trim();
            subtotalValue = parseFloat(subtotalText.replace(/,/g, '')) || 0;
        }
        total += subtotalValue;
    });
    $("#total").text(total.toFixed({{$general_setting->decimal}}));
    $('input[name="total_price"]').val(total.toFixed({{$general_setting->decimal}}));

    calculateGrandTotal();
}

function calculateGrandTotal() {

    var item = $('table.order-list tbody tr:last').index();

    var total_qty = parseFloat($('#total-qty').text()) || 0;
    
    // Get subtotal from input (more reliable) or text (remove commas)
    var subtotal = parseFloat($('input[name="total_price"]').val()) || 0;
    if(subtotal === 0) {
        var subtotalText = $('#total').text().trim();
        subtotal = parseFloat(subtotalText.replace(/,/g, '')) || 0;
    }
    
    var order_tax_rate = parseFloat($('select[name="order_tax_rate"]').val()) || 0;
    var order_discount = parseFloat($('input[name="order_discount"]').val()) || 0;
    var shipping_cost = parseFloat($('input[name="shipping_cost"]').val()) || 0;

    if (!order_discount)
        order_discount = 0;
    if (!shipping_cost)
        shipping_cost = 0;

    item = ++item + '(' + total_qty + ')';
    
    // Calculate order tax: (subtotal - order_discount) * (order_tax_rate / 100)
    var order_tax = (subtotal - order_discount) * (order_tax_rate / 100);
    
    // Calculate grand total: subtotal + order_tax + shipping_cost - order_discount
    var grand_total = (subtotal + order_tax + shipping_cost) - order_discount;

    $('#item').text(item);
    $('input[name="item"]').val($('table.order-list tbody tr:last').index() + 1);
    $('#subtotal').text(subtotal.toFixed({{$general_setting->decimal}}));
    $('#order_tax').text(order_tax.toFixed({{$general_setting->decimal}}));
    $('input[name="order_tax"]').val(order_tax.toFixed({{$general_setting->decimal}}));
    $('#order_discount').text(order_discount.toFixed({{$general_setting->decimal}}));
    $('#shipping_cost').text(shipping_cost.toFixed({{$general_setting->decimal}}));
    $('#grand_total').text(grand_total.toFixed({{$general_setting->decimal}}));
    $('input[name="grand_total"]').val(grand_total.toFixed({{$general_setting->decimal}}));
}

$('input[name="order_discount"]').on("input", function() {
    calculateGrandTotal();
});

$('input[name="shipping_cost"]').on("input", function() {
    calculateGrandTotal();
});

$('select[name="order_tax_rate"]').on("change", function() {
    calculateGrandTotal();
});

// Initialize existing rows' subtotal values on page load
$(document).ready(function() {
    setTimeout(function() {
        $('table.order-list tbody tr').each(function() {
            var $row = $(this);
            // First, try to get value from hidden input (most reliable)
            var subtotalValue = parseFloat($row.find('.subtotal-value').val()) || 0;
            
            // If hidden input doesn't have value, try to parse from display text
            if(subtotalValue === 0) {
                var subtotalText = $row.find('.sub-total').text().trim();
                if(subtotalText) {
                    subtotalValue = parseFloat(subtotalText.replace(/,/g, '').replace(/[^\d.-]/g, '')) || 0;
                }
            }
            
            // If still 0, calculate from components
            if(subtotalValue === 0) {
                var netPriceText = $row.find('.net_unit_price').text().trim() || $row.find('.net_unit_price').val() || '0';
                var netPrice = parseFloat(netPriceText.replace(/,/g, '')) || 0;
                var qty = parseFloat($row.find('.qty').val()) || 0;
                var discountText = $row.find('.discount').text().trim() || '0';
                var discount = parseFloat(discountText.replace(/,/g, '')) || 0;
                var taxText = $row.find('.tax').text().trim() || '0';
                var tax = parseFloat(taxText.replace(/,/g, '')) || 0;
                
                // Calculate: (net_unit_price * qty) - discount + tax
                subtotalValue = (netPrice * qty) - discount + tax;
            }
            
            // Update both display and hidden input if we have a valid value
            if(subtotalValue > 0 || subtotalValue < 0) {
                var formattedValue = subtotalValue.toFixed({{$general_setting->decimal}});
                $row.find('.sub-total').text(parseFloat(formattedValue).toLocaleString('en-US', {minimumFractionDigits: {{$general_setting->decimal}}, maximumFractionDigits: {{$general_setting->decimal}}}));
                $row.find('.subtotal-value').val(formattedValue);
            }
        });
        calculateTotal();
    }, 100);
});

// Quick Add JavaScript - Same as create page
@include('backend.quotation.partials.quick_add_scripts')

// Remove any duplicate buttons first
$('#add-line-item').not(':first').remove();

// Add Line Item button functionality
$('#add-line-item').off('click').on('click', function() {
    // Check customer and warehouse selection
    var customer_id = $('#customer_id').val();
    var warehouse_id = $('#warehouse_id').val();
    
    if(!customer_id){
        alert('Please select Customer first!');
        return false;
    }
    else if(!warehouse_id){
        alert('Please select Warehouse first!');
        return false;
    }
    
    // Generate product code
    $.get('{{ route("product.gencode") }}', function(code) {
        var newRow = $("<tr>");
        var cols = '';
        cols += '<td><textarea class="form-control product-name line-item-product-name" name="product_name[]" rows="1" placeholder="Enter product name" required></textarea></td>';
        cols += '<td><input type="text" class="form-control product-code" name="product_code[]" value="' + code + '" style="width:100px;"/></td>';
        cols += '<td class="quotation-unit-cell">' + quotationBuildUnitFieldHtml(quotationDefaultUnit()) + '</td>';
        cols += '<td><input type="number" class="form-control qty" name="qty[]" value="0" step="any" required/></td>';
        cols += '<td><input type="number" class="form-control net_unit_price_input" name="net_unit_price[]" value="0" step="any"/></td>';
        cols += '<td><input type="number" class="form-control discount_input" name="discount[]" value="0" step="any"/></td>';
        cols += '<td class="tax">0<input type="hidden" class="tax-rate" name="tax_rate[]" value="0"/><input type="hidden" class="tax-value" name="tax[]" value="0"/></td>';
        cols += '<td class="sub-total">0<input type="hidden" class="subtotal-value" name="subtotal[]" value="0"/></td>';
        cols += '<td><button type="button" class="ibtnDel btn btn-md btn-danger"><i class="dripicons-trash"></i></button><input type="hidden" class="product-id" name="product_id[]" value="0"/></td>';
        
        newRow.append(cols);
        $("table.order-list tbody").append(newRow);
        quotationInitRowUnitSelect(newRow, quotationDefaultUnit(), []);
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
        
        // Add event handlers for calculation (with event.stopPropagation to prevent conflict)
        newRow.find('.qty').off('input').on('input', function(e) {
            e.stopPropagation();
            var row = $(this).closest('tr');
            var qty = parseFloat($(this).val()) || 0;
            var price = parseFloat(row.find('.net_unit_price_input').val()) || 0;
            var discount = parseFloat(row.find('.discount_input').val()) || 0;
            var subtotal = (qty * price) - discount;
            var tax = 0; // No tax for new items
            
            row.find('.sub-total').text(subtotal.toFixed({{$general_setting->decimal}}));
            row.find('.subtotal-value').val(subtotal.toFixed({{$general_setting->decimal}}));
            row.find('.tax').text(tax.toFixed({{$general_setting->decimal}}));
            row.find('.tax-value').val(tax);
            
            calculateTotal();
        });
        
        newRow.find('.net_unit_price_input').off('input').on('input', function(e) {
            e.stopPropagation();
            var row = $(this).closest('tr');
            var qty = parseFloat(row.find('.qty').val()) || 0;
            var price = parseFloat($(this).val()) || 0;
            var discount = parseFloat(row.find('.discount_input').val()) || 0;
            var subtotal = (qty * price) - discount;
            var tax = 0;
            
            row.find('.sub-total').text(subtotal.toFixed({{$general_setting->decimal}}));
            row.find('.subtotal-value').val(subtotal.toFixed({{$general_setting->decimal}}));
            row.find('.tax').text(tax.toFixed({{$general_setting->decimal}}));
            row.find('.tax-value').val(tax);
            
            calculateTotal();
        });
        
        newRow.find('.discount_input').off('input').on('input', function(e) {
            e.stopPropagation();
            var row = $(this).closest('tr');
            var qty = parseFloat(row.find('.qty').val()) || 0;
            var price = parseFloat(row.find('.net_unit_price_input').val()) || 0;
            var discount = parseFloat($(this).val()) || 0;
            var subtotal = (qty * price) - discount;
            var tax = 0;
            
            row.find('.sub-total').text(subtotal.toFixed({{$general_setting->decimal}}));
            row.find('.subtotal-value').val(subtotal.toFixed({{$general_setting->decimal}}));
            row.find('.tax').text(tax.toFixed({{$general_setting->decimal}}));
            row.find('.tax-value').val(tax);
            
            calculateTotal();
        });
        
        // Recalculate totals
        calculateTotal();
    });
});

</script>
@endpush
