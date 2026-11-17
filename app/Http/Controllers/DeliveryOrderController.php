<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Customer;
use App\Models\CustomerGroup;
use App\Models\Warehouse;
use App\Models\Biller;
use App\Models\Product;
use App\Models\Category;
use App\Models\Unit;
use App\Models\Tax;
use App\Models\Quotation;
use App\Models\ProductQuotation;
use App\Models\Purchase;
use App\Models\ProductPurchase;
use App\Models\DeliveryOrder;
use App\Models\DeliveryOrderProduct;
use App\Models\Product_Warehouse;
use App\Models\ProductVariant;
use App\Models\ProductBatch;
use App\Models\Variant;
use App\Models\GeneralSetting;
use DB;
use Auth;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Validator;
use Barryvdh\DomPDF\Facade\Pdf;

class DeliveryOrderController extends Controller
{
    private function productAutocompleteOptions()
    {
        return Product::where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'code', 'name'])
            ->map(fn ($product) => [
                'value' => $product->code.' ('.$product->name.')',
                'id' => $product->id,
            ])
            ->values();
    }

    public function index(Request $request)
    {
        $role = $this->currentRole();
        if($this->hasAnyRolePermission($role, ['delivery-orders-index'])){
            $warehouse_id = $request->input('warehouse_id', 0);
            $status = $request->input('status', '');
            
            if($request->input('starting_date')) {
                $starting_date = $request->input('starting_date');
                $ending_date = $request->input('ending_date');
            } else {
                $starting_date = date("Y-m-d", strtotime('-1 year'));
                $ending_date = date("Y-m-d");
            }

            $permissions = Role::findByName($role->name)->permissions;
            $all_permission = [];
            foreach ($permissions as $permission)
                $all_permission[] = $permission->name;
            if(empty($all_permission))
                $all_permission[] = 'dummy text';

            $lims_warehouse_list = Warehouse::where('is_active', true)->get();

            return view('backend.delivery_order.index', compact(
                'lims_warehouse_list', 'all_permission', 'warehouse_id', 
                'starting_date', 'ending_date', 'status'
            ));
        }
        return redirect()->back()->with('not_permitted', __('db.Sorry! You are not allowed to access this module'));
    }

    public function deliveryOrderData(Request $request)
    {
        $role = $this->currentRole();
        if(!$this->hasAnyRolePermission($role, ['delivery-orders-index'])) {
            return response()->json([
                "draw" => intval($request->input('draw')),
                "recordsTotal" => 0,
                "recordsFiltered" => 0,
                "data" => []
            ]);
        }

        try {
            $columns = array(
                1 => 'created_at',
                2 => 'delivery_order_no',
                5 => 'grand_total',
            );

            $starting_date = $request->input('starting_date') ?: date("Y-m-d", strtotime('-1 year'));
            $ending_date = $request->input('ending_date') ?: date("Y-m-d");

            $warehouse_id = $request->input('warehouse_id');
            $status = $request->input('status');

            $query = DeliveryOrder::with('biller', 'customer', 'user', 'warehouse')
                ->where('is_active', true)
                ->visibleToStaff()
                ->whereDate('created_at', '>=', $starting_date)
                ->whereDate('created_at', '<=', $ending_date);

            if (Auth::user()->role_id <= 2 && $warehouse_id != 0) {
                $query->where('warehouse_id', $warehouse_id);
            }

            if($status) {
                $query->where('status', $status);
            }

            $totalData = (clone $query)->count();
            $totalFiltered = $totalData;

            $limit = $request->input('length') != -1 ? $request->input('length') : $totalData;
            $start = $request->input('start');
            $order = $columns[$request->input('order.0.column')] ?? 'created_at';
            $dir = strtolower((string) $request->input('order.0.dir'));
            if (!in_array($dir, ['asc', 'desc'], true)) {
                $dir = 'desc';
            }

            if(!empty($request->input('search.value'))) {
                $search = $request->input('search.value');
                $query->where(function($q) use ($search) {
                    $q->where('delivery_order_no', 'LIKE', "%{$search}%")
                      ->orWhere('po_number', 'LIKE', "%{$search}%")
                      ->orWhere('quotation_number', 'LIKE', "%{$search}%")
                      ->orWhere('note', 'LIKE', "%{$search}%")
                      ->orWhere('delivery_address', 'LIKE', "%{$search}%")
                      ->orWhereHas('products.product', function($q) use ($search) {
                          $q->where('name', 'LIKE', "%{$search}%")
                            ->orWhere('product_details', 'LIKE', "%{$search}%");
                      })
                      ->orWhereHas('customer', function($q) use ($search) {
                          $q->where('name', 'LIKE', "%{$search}%");
                      })
                      ->orWhereHas('biller', function($q) use ($search) {
                          $q->where('name', 'LIKE', "%{$search}%");
                      });
                });
                $totalFiltered = (clone $query)->count();
            }

            $delivery_orders = $query->offset($start)
                ->limit($limit)
                ->orderBy($order, $dir)
                ->get();

            $data = [];
            $general_setting = GeneralSetting::latest()->first();

            $canEdit = $this->hasAnyRolePermission($role, ['delivery-orders-edit']);
            $canDelete = Auth::user()->role_id <= 2 && $this->hasAnyRolePermission($role, ['delivery-orders-delete']);

            foreach($delivery_orders as $key => $order) {
                $nestedData['id'] = $order->id;
                $nestedData['key'] = $key;
                $nestedData['date'] = date($general_setting->date_format, strtotime($order->created_at));
                $nestedData['delivery_order_no'] = '<a href="'.route('delivery-orders.show', $order->id).'">'.$order->delivery_order_no.'</a>';
                $nestedData['biller'] = $order->biller->name ?? '';
                $nestedData['customer'] = $order->customer->name ?? '';
                $nestedData['grand_total'] = number_format($order->grand_total, $general_setting->decimal, '.', '');

                $status_class = $order->status == 'completed' ? 'success' : ($order->status == 'submitted' ? 'info' : 'warning');
                $nestedData['status'] = '<span class="badge badge-'.$status_class.'">'.ucfirst($order->status).'</span>';

                $edit_link = ($canEdit && $order->status != 'completed')
                    ? '<li><a href="'.route('delivery-orders.edit', $order->id).'" class="btn btn-link"><i class="dripicons-document-edit"></i> '.__('db.edit').'</a></li>'
                    : '';
                $delete_link = $canDelete
                    ? '<li>
                            <form action="'.route('delivery-orders.destroy', $order->id).'" method="POST" onsubmit="return confirmDelete()">
                                '.csrf_field().'
                                '.method_field('DELETE').'
                                <button type="submit" class="btn btn-link"><i class="dripicons-trash"></i> '.__('db.delete').'</button>
                            </form>
                        </li>'
                    : '';
                $nestedData['options'] = '<div class="btn-group">
                    <button type="button" class="btn btn-default btn-sm dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">'.__('db.action').'
                        <span class="caret"></span>
                    </button>
                    <ul class="dropdown-menu edit-options dropdown-menu-right dropdown-default" user="menu">
                        '.$edit_link.'
                        <li><a href="'.route('delivery-orders.show', $order->id).'" class="btn btn-link"><i class="dripicons-preview"></i> '.__('db.View').'</a></li>
                        '.($delete_link ? '<li class="divider"></li>'.$delete_link : '').'
                    </ul>
                </div>';

                $data[] = $nestedData;
            }

            return response()->json([
                "draw" => intval($request->input('draw')),
                "recordsTotal" => intval($totalData),
                "recordsFiltered" => intval($totalFiltered),
                "data" => $data
            ]);
        } catch (\Throwable $e) {
            \Log::error('deliveryOrderData failed', [
                'message' => $e->getMessage(),
                'user_id' => Auth::id(),
            ]);

            return response()->json([
                "draw" => intval($request->input('draw')),
                "recordsTotal" => 0,
                "recordsFiltered" => 0,
                "data" => [],
                "error" => "Delivery Orders load nahi ho rahe (server error). Logs check kar lo."
            ]);
        }
    }

    public function create(Request $request)
    {
        $role = $this->currentRole();
        if ($this->hasAnyRolePermission($role, ['delivery-orders-add'])) {
            $lims_biller_list = Biller::where('is_active', true)->get();
            $lims_customer_list = Customer::where('is_active', true)->get();
            $lims_warehouse_list = Warehouse::where('is_active', true)->get();
            $lims_tax_list = Tax::where('is_active', true)->get();
            $lims_quotation_list = Quotation::orderBy('id', 'desc')->get();
            $lims_purchase_list = Purchase::orderBy('id', 'desc')->get();
            $lims_customer_group_all = CustomerGroup::where('is_active', true)->get();
            $quotation_id = $request->input('quotation_id');
            $purchase_id = $request->input('purchase_id');

            // Server-side prefill (purchase-style) when coming from a quotation link
            $prefill_quotation = null;
            $prefill_products = [];
            if ($quotation_id) {
                $q = Quotation::with('products.product', 'products.unit', 'biller', 'customer', 'warehouse')->find($quotation_id);
                if ($q) {
                    // Convert to a small JSON-safe payload for the Blade view
                    $prefill_quotation = [
                        'biller_id' => $q->biller_id,
                        'customer_id' => $q->customer_id,
                        'warehouse_id' => $q->warehouse_id,
                        'order_tax_rate' => $q->order_tax_rate ?? 0,
                        'order_discount' => $q->order_discount ?? 0,
                        'shipping_cost' => $q->shipping_cost ?? 0,
                        'note' => $q->note ?? '',
                        'delivery_address' => $q->delivery_address ?? '',
                    ];

                    foreach ($q->products as $product) {
                        $lims_product_data = $product->product;
                        $unit = $product->unit;

                        $prefill_products[] = [
                            'product_id' => $product->product_id,
                            'product_name' => $lims_product_data->name ?? '',
                            'product_code' => $lims_product_data->code ?? '',
                            'qty' => $product->qty,
                            'sale_unit' => $unit->unit_name ?? 'n/a',
                            'net_unit_price' => $product->net_unit_price,
                            'discount' => $product->discount,
                            'tax_rate' => $product->tax_rate,
                            'tax' => $product->tax,
                            'total' => $product->total,
                            'product_batch_id' => $product->product_batch_id,
                            'variant_id' => $product->variant_id,
                        ];
                    }
                }
            }

            $lims_product_autocomplete = $this->productAutocompleteOptions();
            
            return view('backend.delivery_order.create', compact(
                'lims_biller_list', 'lims_customer_list', 'lims_warehouse_list', 
                'lims_tax_list', 'lims_quotation_list', 'lims_purchase_list', 'lims_customer_group_all', 'quotation_id', 'purchase_id',
                'prefill_quotation', 'prefill_products', 'lims_product_autocomplete'
            ));
        }
        return redirect()->back()->with('not_permitted', __('db.Sorry! You are not allowed to access this module'));
    }

    public function store(Request $request)
    {
        $role = $this->currentRole();
        if(!$this->hasAnyRolePermission($role, ['delivery-orders-add'])) {
            return redirect()->back()->with('not_permitted', __('db.Sorry! You are not allowed to access this module'));
        }

        try {
            $data = $request->except('document');
            $data['user_id'] = Auth::id();
            
            // Handle document upload
            if($request->hasFile('document')){
                $document = $request->file('document');
                $v = Validator::make(
                    ['extension' => strtolower($document->getClientOriginalExtension())],
                    ['extension' => 'in:jpg,jpeg,png,gif,pdf,csv,docx,xlsx,txt']
                );
                if ($v->fails())
                    return redirect()->back()->withErrors($v->errors());
                
                $ext = pathinfo($document->getClientOriginalName(), PATHINFO_EXTENSION);
                $documentName = date("Ymdhis") . '.' . $ext;
                $document->move(public_path('documents/delivery_order'), $documentName);
                $data['document'] = $documentName;
            }

            // Generate delivery order number
            $general_setting = GeneralSetting::latest()->first();
            $prefix = rtrim((string) ($general_setting->delivery_order_prefix ?? 'DO'), " -_/");
            $data['delivery_order_no'] = $prefix . '-' . date("his");
            
            // Convert date format from datepicker to database format
            if(isset($data['delivery_date']) && $data['delivery_date']) {
                $date_format = $general_setting->date_format ?? 'd/m/Y';
                try {
                    $data['delivery_date'] = \Carbon\Carbon::createFromFormat($date_format, $data['delivery_date'])->format('Y-m-d');
                } catch (\Exception $e) {
                    // If parsing fails, try alternative format
                    $data['delivery_date'] = date('Y-m-d', strtotime(str_replace("/", "-", $data['delivery_date'])));
                }
            }
            
            if(isset($data['purchase_id']) && $data['purchase_id']) {
                $data['purchase_id'] = $data['purchase_id'];
            }

            // Ensure numeric fields are not null
            $data['order_discount'] = $data['order_discount'] ?? 0;
            $data['order_tax_rate'] = $data['order_tax_rate'] ?? 0;
            $data['shipping_cost'] = $data['shipping_cost'] ?? 0;

            $this->normalizeOrderDocumentTotals($data);
            
            $delivery_order = DeliveryOrder::create($data);

            // Get or create default category by name
            $default_category = Category::where('name', 'Default')
                ->where('is_active', true)
                ->first();
            
            if (!$default_category) {
                $default_category = Category::create([
                    'name' => 'Default',
                    'is_active' => true
                ]);
            }
            
            // Get or create default unit by name
            $default_unit = Unit::where('unit_name', 'Default')
                ->where('is_active', true)
                ->first();
            
            if (!$default_unit) {
                $default_unit = Unit::create([
                    'unit_name' => 'Default',
                    'unit_code' => 'PCS',
                    'base_unit' => null,
                    'operator' => '*',
                    'operation_value' => 1,
                    'is_active' => true
                ]);
            }
            
            // Save products
            $product_id = $data['product_id'] ?? [];
            $product_name = isset($data['product_name']) ? $data['product_name'] : [];
            $qty = $data['qty'] ?? [];
            $sale_unit = $data['sale_unit'] ?? [];
            $net_unit_price = $data['net_unit_price'] ?? [];
            $discount = $data['discount'] ?? [];
            $tax_rate = $data['tax_rate'] ?? [];
            $tax = $data['tax'] ?? [];
            $total = $data['subtotal'] ?? [];
            $product_batch_id = $data['product_batch_id'] ?? [];
            $product_code = $data['product_code'] ?? [];

            foreach ($product_id as $i => $id) {
                try {
                    // If product_id is 0 and product_name exists, check/create product
                    if (($id == 0 || $id === '0' || $id === '') && isset($product_name[$i]) && !empty($product_name[$i])) {
                        $name = trim($product_name[$i]);
                        
                        // Check if product with this name already exists
                        $existing_product = Product::where('name', $name)
                            ->where('is_active', true)
                            ->first();
                        
                        if ($existing_product) {
                            $id = $existing_product->id;
                            $product_id[$i] = $id;
                        } else {
                            // Create new product with quantity as initial stock (line item)
                            $product_qty = $qty[$i] ?? 0;
                            $code = trim($product_code[$i] ?? '');
                            if ($code === '' || Product::where('code', $code)->exists()) {
                                $code = 'LIN-' . $delivery_order->id . '-' . $i . '-' . substr(uniqid(), -6);
                            }
                            try {
                                $new_product = Product::create([
                                    'name' => $name,
                                    'code' => $code,
                                    'type' => 'standard',
                                    'barcode_symbology' => 'C128',
                                    'category_id' => $default_category->id,
                                    'unit_id' => $default_unit->id,
                                    'purchase_unit_id' => $default_unit->id,
                                    'sale_unit_id' => $default_unit->id,
                                    'cost' => 0,
                                    'price' => $net_unit_price[$i] ?? 0,
                                    'qty' => $product_qty,
                                    'tax_method' => 1,
                                    'is_active' => true,
                                    'is_variant' => false,
                                    'is_batch' => false,
                                    'is_imei' => false,
                                    'image' => 'zummXD2dvAtI.png',
                                ]);
                                $id = $new_product->id;
                                $product_id[$i] = $id;

                                if ($product_qty > 0 && isset($data['warehouse_id'])) {
                                    $lims_product_warehouse_data = Product_Warehouse::where([
                                        ['product_id', $id],
                                        ['warehouse_id', $data['warehouse_id']]
                                    ])->first();
                                    if ($lims_product_warehouse_data) {
                                        $lims_product_warehouse_data->qty = $lims_product_warehouse_data->qty + $product_qty;
                                        $lims_product_warehouse_data->save();
                                    } else {
                                        Product_Warehouse::create([
                                            'product_id' => $id,
                                            'warehouse_id' => $data['warehouse_id'],
                                            'qty' => $product_qty
                                        ]);
                                    }
                                }
                            } catch (\Exception $createEx) {
                                \Log::error('Delivery order line-item product create failed: ' . $createEx->getMessage());
                                continue;
                            }
                        }
                    }
                    
                    $lims_product_data = Product::find($id);
                    
                    // Skip if product not found
                    if (!$lims_product_data) {
                        continue;
                    }
                    
                    $current_sale_unit = $sale_unit[$i] ?? '';
                    $sale_unit_id = null;
                    if($current_sale_unit != 'n/a' && !empty($current_sale_unit)){
                        $lims_sale_unit_data = Unit::where('unit_name', $current_sale_unit)->first();
                        if ($lims_sale_unit_data) {
                            $sale_unit_id = $lims_sale_unit_data->id;
                        } else {
                            $sale_unit_id = $default_unit->id;
                        }
                    } else {
                        $sale_unit_id = $default_unit->id;
                    }
                    $product_data = [];
                    $product_data['delivery_order_id'] = $delivery_order->id;
                    $product_data['product_id'] = $id;
                    $product_data['product_batch_id'] = $product_batch_id[$i] ?? null;
                    $product_data['qty'] = $qty[$i] ?? 0;
                    $product_data['sale_unit_id'] = $sale_unit_id;
                    
                    // Check for variant
                    if($lims_product_data->is_variant && isset($product_code[$i])) {
                        $variant = ProductVariant::select('variant_id')->FindExactProductWithCode($id, $product_code[$i])->first();
                        $product_data['variant_id'] = $variant ? $variant->variant_id : null;
                    } else {
                        $product_data['variant_id'] = null;
                    }
                    
                    $product_data['net_unit_price'] = $net_unit_price[$i] ?? 0;
                    $product_data['discount'] = $discount[$i] ?? 0;
                    $product_data['tax_rate'] = $tax_rate[$i] ?? 0;
                    $product_data['tax'] = $tax[$i] ?? 0;
                    $product_data['total'] = $total[$i] ?? 0;
                    
                    DeliveryOrderProduct::create($product_data);
                    
                } catch (\Exception $e) {
                    \Log::error('Error processing delivery order product: ' . $e->getMessage());
                    continue;
                }
            }

            return redirect('delivery-orders')->with('message', 'Delivery Order created successfully');
        } 
        catch (\Exception $e) 
        {
            dd([
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all()
            ]);
        }
    }

    public function show($id)
    {
        $delivery_order = DeliveryOrder::with('products.product', 'products.unit', 'biller', 'customer', 'warehouse', 'quotation')->find($id);
        $general_setting = GeneralSetting::latest()->first();
        
        // Use explicitly linked Purchase Order if available
        $related_purchase = null;
        if($delivery_order->purchase_id) {
            $related_purchase = Purchase::find($delivery_order->purchase_id);
        }
        
        return view('backend.delivery_order.show', compact('delivery_order', 'general_setting', 'related_purchase'));
    }

    public function downloadPdf($id)
    {
        $delivery_order = DeliveryOrder::with('products.product', 'products.unit', 'biller', 'customer', 'warehouse', 'quotation')->findOrFail($id);
        $general_setting = GeneralSetting::latest()->first();
        $related_purchase = null;
        if ($delivery_order->purchase_id) {
            $related_purchase = Purchase::find($delivery_order->purchase_id);
        }
        $pdf = Pdf::loadView('backend.delivery_order.pdf', compact('delivery_order', 'general_setting', 'related_purchase'));
        return $pdf->download(preg_replace('/[^a-zA-Z0-9\-]/', '-', $delivery_order->delivery_order_no) . '.pdf');
    }

    public function edit($id)
    {
        $role = $this->currentRole();
        if($this->hasAnyRolePermission($role, ['delivery-orders-edit'])){
            $delivery_order = DeliveryOrder::with('products', 'quotation')->find($id);
            $lims_biller_list = Biller::where('is_active', true)->get();
            $lims_customer_list = Customer::where('is_active', true)->get();
            $lims_warehouse_list = Warehouse::where('is_active', true)->get();
            $lims_tax_list = Tax::where('is_active', true)->get();
            $lims_quotation_list = Quotation::orderBy('id', 'desc')->get();
            $lims_purchase_list = Purchase::orderBy('id', 'desc')->get();
            $lims_customer_group_all = CustomerGroup::where('is_active', true)->get();

            $lims_product_delivery_data = DeliveryOrderProduct::where('delivery_order_id', $id)->get();
            $lims_product_autocomplete = $this->productAutocompleteOptions();

            return view('backend.delivery_order.edit', compact(
                'delivery_order', 'lims_biller_list', 'lims_customer_list',
                'lims_warehouse_list', 'lims_tax_list', 'lims_quotation_list', 'lims_purchase_list', 'lims_product_delivery_data', 'lims_customer_group_all',
                'lims_product_autocomplete'
            ));
        }
        return redirect()->back()->with('not_permitted', __('db.Sorry! You are not allowed to access this module'));
    }

    public function update(Request $request, $id)
    {
            $role = $this->currentRole();
            if(!$this->hasAnyRolePermission($role, ['delivery-orders-edit'])) {
                return redirect()->back()->with('not_permitted', __('db.Sorry! You are not allowed to access this module'));
            }

            $data = $request->except('document');
            $delivery_order = DeliveryOrder::find($id);
        
        // Handle document upload
        if($request->hasFile('document')){
            $document = $request->file('document');
            $v = Validator::make(
                ['extension' => strtolower($document->getClientOriginalExtension())],
                ['extension' => 'in:jpg,jpeg,png,gif,pdf,csv,docx,xlsx,txt']
            );
            if ($v->fails())
                return redirect()->back()->withErrors($v->errors());
            
            $ext = pathinfo($document->getClientOriginalName(), PATHINFO_EXTENSION);
            $documentName = date("Ymdhis") . '.' . $ext;
            $document->move(public_path('documents/delivery_order'), $documentName);
            $data['document'] = $documentName;
        }

        // Convert date format from datepicker to database format
        if(isset($data['delivery_date']) && $data['delivery_date']) {
            $general_setting = GeneralSetting::latest()->first();
            $date_format = $general_setting->date_format ?? 'd/m/Y';
            try {
                $data['delivery_date'] = \Carbon\Carbon::createFromFormat($date_format, $data['delivery_date'])->format('Y-m-d');
            } catch (\Exception $e) {
                // If parsing fails, try alternative format
                $data['delivery_date'] = date('Y-m-d', strtotime(str_replace("/", "-", $data['delivery_date'])));
            }
        }

        // Ensure numeric fields are not null
        $data['order_discount'] = $data['order_discount'] ?? 0;
        $data['order_tax_rate'] = $data['order_tax_rate'] ?? 0;
        $data['shipping_cost'] = $data['shipping_cost'] ?? 0;

        $this->normalizeOrderDocumentTotals($data);

        $delivery_order->update($data);

        // Delete old products and add new ones
        DeliveryOrderProduct::where('delivery_order_id', $id)->delete();

        // Save products
        $product_id = $data['product_id'] ?? [];
        $product_name = $data['product_name'] ?? [];
        $qty = $data['qty'] ?? [];
        $sale_unit = $data['sale_unit'] ?? [];
        $net_unit_price = $data['net_unit_price'] ?? [];
        $discount = $data['discount'] ?? [];
        $tax_rate = $data['tax_rate'] ?? [];
        $tax = $data['tax'] ?? [];
        $total = $data['subtotal'] ?? [];
        $product_batch_id = $data['product_batch_id'] ?? [];
        $product_code = $data['product_code'] ?? [];

        // Get or create default category
        $default_category = \App\Models\Category::where('name', 'Default')->where('is_active', true)->first();
        if (!$default_category) {
            $default_category = \App\Models\Category::create(['name' => 'Default', 'is_active' => true]);
        }
        
        // Get or create default unit
        $default_unit = \App\Models\Unit::where('unit_name', 'Default')->where('is_active', true)->first();
        if (!$default_unit) {
            $default_unit = \App\Models\Unit::create([
                'unit_code' => 'DEF',
                'unit_name' => 'Default',
                'base_unit' => null,
                'operator' => '*',
                'operation_value' => 1,
                'is_active' => true
            ]);
        }

        // Process new products (product_id = 0)
        foreach ($product_id as $i => $pid) {
            if (($pid == 0 || $pid === '0' || $pid === '') && isset($product_name[$i]) && !empty($product_name[$i])) {
                $name = trim($product_name[$i]);
                
                // Check if product with this name already exists
                $existing_product = Product::where('name', $name)->where('is_active', true)->first();
                
                if ($existing_product) {
                    $product_id[$i] = $existing_product->id;
                } else {
                    // Create new product
                    $new_product = Product::create([
                        'name' => $name,
                        'code' => $product_code[$i] ?? '',
                        'type' => 'standard',
                        'barcode_symbology' => 'C128',
                        'category_id' => $default_category->id,
                        'unit_id' => $default_unit->id,
                        'purchase_unit_id' => $default_unit->id,
                        'sale_unit_id' => $default_unit->id,
                        'cost' => 0,
                        'price' => $net_unit_price[$i] ?? 0,
                        'qty' => 0,
                        'alert_quantity' => 0,
                        'tax_method' => 1,
                        'is_active' => true,
                        'is_variant' => false,
                        'is_batch' => false,
                        'is_imei' => false,
                        'image' => 'zummXD2dvAtI.png',
                    ]);
                    $product_id[$i] = $new_product->id;
                }
            }
        }

        foreach ($product_id as $i => $pid) {
            // Skip if product_id is still 0
            if ($pid == 0 || $pid === '0' || $pid === '') {
                continue;
            }
            $product_data = [];
            $product_data['delivery_order_id'] = $id;
            $product_data['product_id'] = $pid;
            $product_data['product_batch_id'] = $product_batch_id[$i] ?? null;
            $product_data['qty'] = $qty[$i];
            
            if($sale_unit[$i] != 'n/a'){
                $unit = Unit::where('unit_name', $sale_unit[$i])->first();
                $product_data['sale_unit_id'] = $unit->id ?? null;
            } else {
                $product_data['sale_unit_id'] = null;
            }
            
            // Check for variant
            $lims_product_data = Product::find($pid);
            if($lims_product_data && $lims_product_data->is_variant && isset($product_code[$i])) {
                $variant = ProductVariant::select('variant_id')->FindExactProductWithCode($pid, $product_code[$i])->first();
                $product_data['variant_id'] = $variant->variant_id ?? null;
            } else {
                $product_data['variant_id'] = null;
            }
            
            $product_data['net_unit_price'] = $net_unit_price[$i];
            $product_data['discount'] = $discount[$i];
            $product_data['tax_rate'] = $tax_rate[$i];
            $product_data['tax'] = $tax[$i];
            $product_data['total'] = $total[$i];
            
            DeliveryOrderProduct::create($product_data);
        }

        return redirect('delivery-orders')->with('message', 'Delivery Order updated successfully');
    }

    public function destroy($id)
    {
        $role = $this->currentRole();
        if(!(Auth::user()->role_id <= 2 && $this->hasAnyRolePermission($role, ['delivery-orders-delete']))) {
            return redirect()->back()->with('not_permitted', __('db.Sorry! You are not allowed to access this module'));
        }

        $delivery_order = DeliveryOrder::find($id);
        $delivery_order->is_active = false;
        $delivery_order->save();
        
        return redirect('delivery-orders')->with('message', 'Delivery Order deleted successfully');
    }

    public function updateStatus(Request $request, $id)
    {
        $delivery_order = DeliveryOrder::find($id);
        $delivery_order->status = $request->status;
        $delivery_order->save();
        
        return response()->json(['success' => true, 'message' => __('db.Status updated successfully')]);
    }

    public function loadFromQuotation($id)
    {
        $quotation = Quotation::with('products.product', 'products.unit', 'biller', 'customer', 'warehouse')->find($id);
        
        if(!$quotation) {
            return response()->json(['success' => false, 'message' => 'Quotation not found']);
        }

        $products = [];
        foreach($quotation->products as $product) {
            $lims_product_data = $product->product;
            $unit = $product->unit;
            
            $products[] = [
                'product_id' => $product->product_id,
                'product_name' => $lims_product_data->name ?? '',
                'product_code' => $lims_product_data->code ?? '',
                'qty' => $product->qty,
                'sale_unit' => $unit->unit_name ?? 'n/a',
                'net_unit_price' => $product->net_unit_price,
                'discount' => $product->discount,
                'tax_rate' => $product->tax_rate,
                'tax' => $product->tax,
                'total' => $product->total,
                'product_batch_id' => $product->product_batch_id,
                'variant_id' => $product->variant_id,
            ];
        }

        return response()->json([
            'success' => true,
            'quotation' => $quotation,
            'products' => $products
        ]);
    }

    public function loadFromPurchase($id)
    {
        $purchase = Purchase::with(['warehouse', 'supplier'])->find($id);
        
        if(!$purchase) {
            return response()->json(['success' => false, 'message' => 'Purchase Order not found']);
        }

        // Find related quotation from purchase order
        $purchase_product_ids = ProductPurchase::where('purchase_id', $purchase->id)->pluck('product_id')->toArray();
        $quotation = null;
        
        if(!empty($purchase_product_ids)) {
            $quotation = Quotation::where('warehouse_id', $purchase->warehouse_id)
                ->whereHas('products', function($query) use ($purchase_product_ids) {
                    $query->whereIn('product_id', $purchase_product_ids);
                })
                ->orderBy('created_at', 'desc')
                ->with('biller', 'customer')
                ->first();
        }

        $products = [];
        $product_purchases = ProductPurchase::where('purchase_id', $purchase->id)
            ->with(['product'])
            ->get();

        foreach($product_purchases as $product_purchase) {
            // Load product if not loaded via relationship
            $lims_product_data = $product_purchase->product;
            if(!$lims_product_data) {
                $lims_product_data = Product::find($product_purchase->product_id);
            }
            
            if(!$lims_product_data) {
                continue; // Skip if product not found
            }
            
            // Find sale unit - use product's sale_unit_id if available, otherwise find from base unit
            $sale_unit_name = 'n/a';
            if($lims_product_data && $lims_product_data->sale_unit_id) {
                $sale_unit = Unit::find($lims_product_data->sale_unit_id);
                $sale_unit_name = $sale_unit ? $sale_unit->unit_name : 'n/a';
            } elseif($lims_product_data && $lims_product_data->unit_id) {
                // Find first sale unit from base unit
                $sale_units = Unit::where('base_unit', $lims_product_data->unit_id)
                    ->orWhere('id', $lims_product_data->unit_id)
                    ->get();
                if($sale_units->count() > 0) {
                    $sale_unit_name = $sale_units->first()->unit_name;
                }
            }
            
            // Use exact purchase order values - same as entered when creating PO
            // The delivery order should reflect what was agreed in the purchase order
            $products[] = [
                'product_id' => $product_purchase->product_id,
                'product_name' => $lims_product_data->name ?? '',
                'product_code' => $lims_product_data->code ?? '',
                'qty' => $product_purchase->qty,
                'sale_unit' => $sale_unit_name,
                'net_unit_price' => $product_purchase->net_unit_price,
                'discount' => $product_purchase->discount ?? 0,
                'tax_rate' => $product_purchase->tax_rate ?? 0,
                'tax' => $product_purchase->tax ?? 0,
                'total' => $product_purchase->total,
                'product_batch_id' => $product_purchase->product_batch_id,
                'variant_id' => $product_purchase->variant_id,
            ];
        }

        // Return data similar to quotation format
        $data = [
            'success' => true,
            'purchase' => $purchase,
            'quotation' => $quotation ? [
                'biller_id' => $quotation->biller_id,
                'customer_id' => $quotation->customer_id,
                'warehouse_id' => $quotation->warehouse_id,
                'order_tax_rate' => $quotation->order_tax_rate ?? 0,
                'order_discount' => $quotation->order_discount ?? 0,
                'shipping_cost' => $quotation->shipping_cost ?? 0,
                'note' => $quotation->note ?? '',
                'delivery_address' => $quotation->delivery_address ?? '',
            ] : [
                'biller_id' => null,
                'customer_id' => null,
                'warehouse_id' => $purchase->warehouse_id,
                'order_tax_rate' => $purchase->order_tax_rate ?? 0,
                'order_discount' => $purchase->order_discount ?? 0,
                'shipping_cost' => $purchase->shipping_cost ?? 0,
                'note' => $purchase->note ?? '',
                'delivery_address' => $purchase->delivery_address ?? '',
            ],
            'products' => $products
        ];

        return response()->json($data);
    }
}
