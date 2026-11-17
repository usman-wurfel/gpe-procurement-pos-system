<?php

namespace App\Http\Controllers;

use App\Enums\CustomerTypeEnum;
use Illuminate\Http\Request;
use App\Models\Customer;
use App\Models\CustomerGroup;
use App\Models\Supplier;
use App\Models\Warehouse;
use App\Models\Biller;
use App\Models\Product;
use App\Models\Unit;
use App\Models\Tax;
use App\Models\Category;
use App\Models\Quotation;
use App\Models\Delivery;
use App\Models\DeliveryOrder;
use App\Models\Invoice;
use App\Models\PosSetting;
use App\Models\ProductQuotation;
use App\Models\Product_Warehouse;
use App\Models\ProductVariant;
use App\Models\ProductBatch;
use App\Models\Variant;
use DB;
use NumberToWords\NumberToWords;
use Auth;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Mail\QuotationDetails;
use Mail;
use Illuminate\Support\Facades\Validator;
use App\Models\MailSetting;
use App\Models\GeneralSetting;
use App\Traits\MailInfo;
use App\Traits\StaffAccess;
use App\Traits\TenantInfo;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Support\LimsProductSearchPayload;

class QuotationController extends Controller
{
    use TenantInfo, MailInfo, StaffAccess;

    public function index(Request $request)
    {
        $role = Role::find(Auth::user()->role_id);
        if($role->hasPermissionTo('quotes-index')){
            if($request->input('warehouse_id'))
                $warehouse_id = $request->input('warehouse_id');
            else
                $warehouse_id = 0;

            if($request->input('starting_date')) {
                $starting_date = $request->input('starting_date');
                $ending_date = $request->input('ending_date');
            }
            else {
                $starting_date = date("Y-m-d", strtotime(date('Y-m-d', strtotime('-1 year', strtotime(date('Y-m-d') )))));
                $ending_date = date("Y-m-d");
            }

            $permissions = Role::findByName($role->name)->permissions;
            foreach ($permissions as $permission)
                $all_permission[] = $permission->name;
            if(empty($all_permission))
                $all_permission[] = 'dummy text';

            $lims_warehouse_list = Warehouse::where('is_active', true)->get();

            /*if(Auth::user()->role_id > 2 && config('staff_access') == 'own')
                $lims_quotation_all = Quotation::with('biller', 'customer', 'supplier', 'user')->orderBy('id', 'desc')->where('user_id', Auth::id())->get();
            else
                $lims_quotation_all = Quotation::with('biller', 'customer', 'supplier', 'user')->orderBy('id', 'desc')->get();*/
            return view('backend.quotation.index', compact('lims_warehouse_list', 'all_permission', 'warehouse_id', 'starting_date', 'ending_date'));
        }
        else
            return redirect()->back()->with('not_permitted', __('db.Sorry! You are not allowed to access this module'));
    }

    public function quotationData(Request $request)
    {
        $columns = array(
            1 => 'created_at',
            2 => 'reference_no',
            5 => 'grand_total',
            6 => 'paid_amount',
        );

        $warehouse_id = $request->input('warehouse_id');
        if(Auth::user()->role_id > 2 && config('staff_access') == 'own')
            $totalData = Quotation::where('user_id', Auth::id())
                        ->whereDate('created_at', '>=' ,$request->input('starting_date'))
                        ->whereDate('created_at', '<=' ,$request->input('ending_date'))
                        ->count();
        //check staff access
        elseif(Auth::user()->role_id > 2 && config('staff_access') == 'warehouse')
            $totalData = Quotation::where('warehouse_id', Auth::user()->warehouse_id)
            ->whereDate('created_at', '>=' ,$request->input('starting_date'))
            ->whereDate('created_at', '<=' ,$request->input('ending_date'))
            ->count();
        elseif($warehouse_id != 0)
            $totalData = Quotation::where('warehouse_id', $warehouse_id)
                        ->whereDate('created_at', '>=' ,$request->input('starting_date'))
                        ->whereDate('created_at', '<=' ,$request->input('ending_date'))
                        ->count();
        elseif($warehouse_id != 0)
            $totalData = Quotation::where('warehouse_id', $warehouse_id)
                        ->whereDate('created_at', '>=' ,$request->input('starting_date'))
                        ->whereDate('created_at', '<=' ,$request->input('ending_date'))
                        ->count();
        else
            $totalData = Quotation::whereDate('created_at', '>=' ,$request->input('starting_date'))
                        ->whereDate('created_at', '<=' ,$request->input('ending_date'))
                        ->count();

        $totalFiltered = $totalData;

        if($request->input('length') != -1)
            $limit = $request->input('length');
        else
            $limit = $totalData;
        $start = $request->input('start');
        $order = $columns[$request->input('order.0.column')];
        $dir = $request->input('order.0.dir');
        if(empty($request->input('search.value'))) {
            if(Auth::user()->role_id > 2 && config('staff_access') == 'own')
                $quotations = Quotation::with('biller', 'customer', 'supplier', 'user')->offset($start)
                            ->where('user_id', Auth::id())
                            ->whereDate('created_at', '>=' ,$request->input('starting_date'))
                            ->whereDate('created_at', '<=' ,$request->input('ending_date'))
                            ->limit($limit)
                            ->orderBy($order, $dir)
                            ->get();
            elseif(Auth::user()->role_id > 2 && config('staff_access') == 'warehouse')
                $quotations = Quotation::with('biller', 'customer', 'supplier', 'user')->offset($start)
                            ->where('warehouse_id', Auth::user()->warehouse_id)
                            ->whereDate('created_at', '>=' ,$request->input('starting_date'))
                            ->whereDate('created_at', '<=' ,$request->input('ending_date'))
                            ->limit($limit)
                            ->orderBy($order, $dir)
                            ->get();
            elseif($warehouse_id != 0)
                $quotations = Quotation::with('biller', 'customer', 'supplier', 'user')->offset($start)
                            ->where('warehouse_id', $warehouse_id)
                            ->whereDate('created_at', '>=' ,$request->input('starting_date'))
                            ->whereDate('created_at', '<=' ,$request->input('ending_date'))
                            ->limit($limit)
                            ->orderBy($order, $dir)
                            ->get();
            else
                $quotations = Quotation::with('biller', 'customer', 'supplier', 'user')->offset($start)
                            ->whereDate('created_at', '>=' ,$request->input('starting_date'))
                            ->whereDate('created_at', '<=' ,$request->input('ending_date'))
                            ->limit($limit)
                            ->orderBy($order, $dir)
                            ->get();
        }
        else
        {
            $search = $request->input('search.value');
            if(Auth::user()->role_id > 2 && config('staff_access') == 'own') {
                $quotations =  Quotation::select('quotations.*')
                            ->with('biller', 'customer', 'supplier', 'user')
                            ->join('billers', 'quotations.biller_id', '=', 'billers.id')
                            ->join('customers', 'quotations.customer_id', '=', 'customers.id')
                            ->leftJoin('suppliers', 'quotations.supplier_id', '=', 'suppliers.id')
                            ->leftJoin('product_quotation as pq', 'quotations.id', '=', 'pq.quotation_id')
                            ->leftJoin('products as p', 'pq.product_id', '=', 'p.id')
                            ->whereDate('quotations.created_at', '=' , date('Y-m-d', strtotime(str_replace('/', '-', $search))))
                            ->where('quotations.user_id', Auth::id())
                            ->orwhere([
                                ['quotations.reference_no', 'LIKE', "%{$search}%"],
                                ['quotations.user_id', Auth::id()]
                            ])
                            ->orwhere([
                                ['billers.name', 'LIKE', "%{$search}%"],
                                ['quotations.user_id', Auth::id()]
                            ])
                            ->orwhere([
                                ['customers.name', 'LIKE', "%{$search}%"],
                                ['quotations.user_id', Auth::id()]
                            ])
                            ->orwhere([
                                ['suppliers.name', 'LIKE', "%{$search}%"],
                                ['quotations.user_id', Auth::id()]
                            ])
                            ->orwhere([
                                ['quotations.note', 'LIKE', "%{$search}%"],
                                ['quotations.user_id', Auth::id()]
                            ])
                            ->orwhere([
                                ['quotations.terms_conditions', 'LIKE', "%{$search}%"],
                                ['quotations.user_id', Auth::id()]
                            ])
                            ->orwhere([
                                ['quotations.delivery_address', 'LIKE', "%{$search}%"],
                                ['quotations.user_id', Auth::id()]
                            ])
                            ->orwhere([
                                ['p.name', 'LIKE', "%{$search}%"],
                                ['quotations.user_id', Auth::id()]
                            ])
                            ->orwhere([
                                ['p.product_details', 'LIKE', "%{$search}%"],
                                ['quotations.user_id', Auth::id()]
                            ])
                            ->offset($start)
                            ->limit($limit)
                            ->groupBy('quotations.id')
                            ->orderBy($order,$dir)->get();

                $totalFiltered = Quotation::join('billers', 'quotations.biller_id', '=', 'billers.id')
                            ->join('customers', 'quotations.customer_id', '=', 'customers.id')
                            ->leftJoin('suppliers', 'quotations.supplier_id', '=', 'suppliers.id')
                            ->leftJoin('product_quotation as pq', 'quotations.id', '=', 'pq.quotation_id')
                            ->leftJoin('products as p', 'pq.product_id', '=', 'p.id')
                            ->whereDate('quotations.created_at', '=' , date('Y-m-d', strtotime(str_replace('/', '-', $search))))
                            ->where('quotations.user_id', Auth::id())
                            ->orwhere([
                                ['quotations.reference_no', 'LIKE', "%{$search}%"],
                                ['quotations.user_id', Auth::id()]
                            ])
                            ->orwhere([
                                ['billers.name', 'LIKE', "%{$search}%"],
                                ['quotations.user_id', Auth::id()]
                            ])
                            ->orwhere([
                                ['customers.name', 'LIKE', "%{$search}%"],
                                ['quotations.user_id', Auth::id()]
                            ])
                            ->orwhere([
                                ['suppliers.name', 'LIKE', "%{$search}%"],
                                ['quotations.user_id', Auth::id()]
                            ])
                            ->orwhere([
                                ['quotations.note', 'LIKE', "%{$search}%"],
                                ['quotations.user_id', Auth::id()]
                            ])
                            ->orwhere([
                                ['quotations.terms_conditions', 'LIKE', "%{$search}%"],
                                ['quotations.user_id', Auth::id()]
                            ])
                            ->orwhere([
                                ['quotations.delivery_address', 'LIKE', "%{$search}%"],
                                ['quotations.user_id', Auth::id()]
                            ])
                            ->orwhere([
                                ['p.name', 'LIKE', "%{$search}%"],
                                ['quotations.user_id', Auth::id()]
                            ])
                            ->orwhere([
                                ['p.product_details', 'LIKE', "%{$search}%"],
                                ['quotations.user_id', Auth::id()]
                            ])
                            ->distinct('quotations.id')->count('quotations.id');
            }
            elseif(Auth::user()->role_id > 2 && config('staff_access') == 'warehouse') {
                $quotations =  Quotation::select('quotations.*')
                            ->with('biller', 'customer', 'supplier', 'user')
                            ->join('billers', 'quotations.biller_id', '=', 'billers.id')
                            ->join('customers', 'quotations.customer_id', '=', 'customers.id')
                            ->leftJoin('suppliers', 'quotations.supplier_id', '=', 'suppliers.id')
                            ->leftJoin('product_quotation as pq', 'quotations.id', '=', 'pq.quotation_id')
                            ->leftJoin('products as p', 'pq.product_id', '=', 'p.id')
                            ->whereDate('quotations.created_at', '=' , date('Y-m-d', strtotime(str_replace('/', '-', $search))))
                            ->where('quotations.user_id', Auth::id())
                            ->orwhere([
                                ['quotations.reference_no', 'LIKE', "%{$search}%"],
                                ['quotations.warehouse_id', Auth::user()->warehouse_id]
                            ])
                            ->orwhere([
                                ['billers.name', 'LIKE', "%{$search}%"],
                                ['quotations.warehouse_id', Auth::user()->warehouse_id]
                            ])
                            ->orwhere([
                                ['customers.name', 'LIKE', "%{$search}%"],
                                ['quotations.warehouse_id', Auth::user()->warehouse_id]
                            ])
                            ->orwhere([
                                ['suppliers.name', 'LIKE', "%{$search}%"],
                                ['quotations.warehouse_id', Auth::user()->warehouse_id]
                            ])
                            ->orwhere([
                                ['quotations.note', 'LIKE', "%{$search}%"],
                                ['quotations.warehouse_id', Auth::user()->warehouse_id]
                            ])
                            ->orwhere([
                                ['quotations.terms_conditions', 'LIKE', "%{$search}%"],
                                ['quotations.warehouse_id', Auth::user()->warehouse_id]
                            ])
                            ->orwhere([
                                ['quotations.delivery_address', 'LIKE', "%{$search}%"],
                                ['quotations.warehouse_id', Auth::user()->warehouse_id]
                            ])
                            ->offset($start)
                            ->limit($limit)
                            ->groupBy('quotations.id')
                            ->orderBy($order,$dir)->get();

                $totalFiltered = Quotation::join('billers', 'quotations.biller_id', '=', 'billers.id')
                            ->join('customers', 'quotations.customer_id', '=', 'customers.id')
                            ->leftJoin('suppliers', 'quotations.supplier_id', '=', 'suppliers.id')
                            ->leftJoin('product_quotation as pq', 'quotations.id', '=', 'pq.quotation_id')
                            ->leftJoin('products as p', 'pq.product_id', '=', 'p.id')
                            ->whereDate('quotations.created_at', '=' , date('Y-m-d', strtotime(str_replace('/', '-', $search))))
                            ->where('quotations.user_id', Auth::id())
                            ->orwhere([
                                ['quotations.reference_no', 'LIKE', "%{$search}%"],
                                ['quotations.warehouse_id', Auth::user()->warehouse_id]
                            ])
                            ->orwhere([
                                ['billers.name', 'LIKE', "%{$search}%"],
                                ['quotations.warehouse_id', Auth::user()->warehouse_id]
                            ])
                            ->orwhere([
                                ['customers.name', 'LIKE', "%{$search}%"],
                                ['quotations.warehouse_id', Auth::user()->warehouse_id]
                            ])
                            ->orwhere([
                                ['suppliers.name', 'LIKE', "%{$search}%"],
                                ['quotations.warehouse_id', Auth::user()->warehouse_id]
                            ])
                            ->orwhere([
                                ['quotations.note', 'LIKE', "%{$search}%"],
                                ['quotations.warehouse_id', Auth::user()->warehouse_id]
                            ])
                            ->orwhere([
                                ['quotations.terms_conditions', 'LIKE', "%{$search}%"],
                                ['quotations.warehouse_id', Auth::user()->warehouse_id]
                            ])
                            ->orwhere([
                                ['quotations.delivery_address', 'LIKE', "%{$search}%"],
                                ['quotations.warehouse_id', Auth::user()->warehouse_id]
                            ])
                            ->orwhere([
                                ['p.name', 'LIKE', "%{$search}%"],
                                ['quotations.warehouse_id', Auth::user()->warehouse_id]
                            ])
                            ->orwhere([
                                ['p.product_details', 'LIKE', "%{$search}%"],
                                ['quotations.warehouse_id', Auth::user()->warehouse_id]
                            ])
                            ->distinct('quotations.id')->count('quotations.id');
            }
            else {
                $quotations =  Quotation::select('quotations.*')
                            ->with('biller', 'customer', 'supplier', 'user')
                            ->join('billers', 'quotations.biller_id', '=', 'billers.id')
                            ->join('customers', 'quotations.customer_id', '=', 'customers.id')
                            ->leftJoin('suppliers', 'quotations.supplier_id', '=', 'suppliers.id')
                            ->leftJoin('product_quotation as pq', 'quotations.id', '=', 'pq.quotation_id')
                            ->leftJoin('products as p', 'pq.product_id', '=', 'p.id')
                            ->whereDate('quotations.created_at', '=' , date('Y-m-d', strtotime(str_replace('/', '-', $search))))
                            ->orwhere('quotations.reference_no', 'LIKE', "%{$search}%")
                            ->orwhere('quotations.note', 'LIKE', "%{$search}%")
                            ->orwhere('quotations.terms_conditions', 'LIKE', "%{$search}%")
                            ->orwhere('quotations.delivery_address', 'LIKE', "%{$search}%")
                            ->orwhere('billers.name', 'LIKE', "%{$search}%")
                            ->orwhere('customers.name', 'LIKE', "%{$search}%")
                            ->orwhere('suppliers.name', 'LIKE', "%{$search}%")
                            ->orwhere('p.name', 'LIKE', "%{$search}%")
                            ->orwhere('p.product_details', 'LIKE', "%{$search}%")
                            ->offset($start)
                            ->limit($limit)
                            ->groupBy('quotations.id')
                            ->orderBy($order,$dir)
                            ->get();

                $totalFiltered = Quotation::join('billers', 'quotations.biller_id', '=', 'billers.id')
                            ->join('customers', 'quotations.customer_id', '=', 'customers.id')
                            ->leftJoin('suppliers', 'quotations.supplier_id', '=', 'suppliers.id')
                            ->leftJoin('product_quotation as pq', 'quotations.id', '=', 'pq.quotation_id')
                            ->leftJoin('products as p', 'pq.product_id', '=', 'p.id')
                            ->whereDate('quotations.created_at', '=' , date('Y-m-d', strtotime(str_replace('/', '-', $search))))
                            ->orwhere('quotations.reference_no', 'LIKE', "%{$search}%")
                            ->orwhere('quotations.note', 'LIKE', "%{$search}%")
                            ->orwhere('quotations.terms_conditions', 'LIKE', "%{$search}%")
                            ->orwhere('quotations.delivery_address', 'LIKE', "%{$search}%")
                            ->orwhere('billers.name', 'LIKE', "%{$search}%")
                            ->orwhere('customers.name', 'LIKE', "%{$search}%")
                            ->orwhere('suppliers.name', 'LIKE', "%{$search}%")
                            ->orwhere('p.name', 'LIKE', "%{$search}%")
                            ->orwhere('p.product_details', 'LIKE', "%{$search}%")
                            ->distinct('quotations.id')->count('quotations.id');
            }
        }
        $data = array();
        if(!empty($quotations))
        {
            foreach ($quotations as $key => $quotation)
            {
                $nestedData['id'] = $quotation->id;
                $nestedData['key'] = $key;
                $nestedData['date'] = date(config('date_format'), strtotime($quotation->created_at->toDateString()));
                $nestedData['reference_no'] = $quotation->reference_no;
                $nestedData['warehouse'] = $quotation->warehouse->name;
                $nestedData['biller'] = $quotation->biller->name;
                $nestedData['customer'] = $quotation->customer->name;

                if($quotation->supplier_id) {
                    $supplier = $quotation->supplier;
                    $nestedData['supplier'] = $supplier->name;
                }
                else {
                    $nestedData['supplier'] = 'N/A';
                }

                if($quotation->quotation_status == 1) {
                    $nestedData['status'] = '<div class="badge badge-danger">'.__('db.Pending').'</div>';
                    $status = __('db.Pending');
                }
                else{
                    $nestedData['status'] = '<div class="badge badge-success">'.__('db.Sent').'</div>';
                    $status = __('db.Sent');
                }

                $nestedData['grand_total'] = number_format($quotation->grand_total, config('decimal'));
                $nestedData['options'] = '<div class="btn-group">
                            <button type="button" class="btn btn-default btn-sm dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">'.__("db.action").'
                              <span class="caret"></span>
                              <span class="sr-only">Toggle Dropdown</span>
                            </button>
                            <ul class="dropdown-menu edit-options dropdown-menu-right dropdown-default" user="menu">
                                <li>
                                    <button type="button" class="btn btn-link view"><i class="fa fa-eye"></i> '.__('db.View').'</button>
                                </li>';
                if(in_array("quotes-edit", $request['all_permission']))
                    $nestedData['options'] .= '<li>
                        <a href="'.route('quotations.edit', $quotation->id).'" class="btn btn-link"><i class="dripicons-document-edit"></i> '.__('db.edit').'</a>
                        </li>';
                $nestedData['options'] .= '<li>
                        <a href="'.route('quotation.create_sale', $quotation->id).'" class="btn btn-link"><i class="fa fa-shopping-cart"></i> '.__('db.Create Sale').'</a>
                        </li>';
                $nestedData['options'] .= '<li>
                        <a href="'.route('quotation.create_purchase', $quotation->id).'" class="btn btn-link"><i class="fa fa-shopping-basket"></i> Create Purchase Order</a>
                        </li>';
                
                // Check if Delivery Order exists
                $delivery_order = DeliveryOrder::where('quotation_id', $quotation->id)->where('is_active', true)->first();
                if($delivery_order) {
                    $nestedData['options'] .= '<li>
                        <a href="'.route('delivery-orders.show', $delivery_order->id).'" class="btn btn-link"><i class="fa fa-truck"></i> View Delivery Order</a>
                        </li>';
                } else {
                    $nestedData['options'] .= '<li>
                        <a href="'.route('delivery-orders.create', ['quotation_id' => $quotation->id]).'" class="btn btn-link"><i class="fa fa-truck"></i> Create Delivery Order</a>
                        </li>';
                }
                
                // Check if Invoice exists
                $invoice = Invoice::where('quotation_id', $quotation->id)->where('is_active', true)->first();
                if($invoice) {
                    $nestedData['options'] .= '<li>
                        <a href="'.route('invoices.show', $invoice->id).'" class="btn btn-link"><i class="fa fa-file-text"></i> View Invoice</a>
                        </li>';
                } else {
                    $nestedData['options'] .= '<li>
                        <a href="'.route('invoices.create', ['quotation_id' => $quotation->id]).'" class="btn btn-link"><i class="fa fa-file-text"></i> Create Invoice</a>
                        </li>';
                }
                if(in_array("quotes-delete", $request['all_permission']))
                    $nestedData['options'] .= \Form::open(["route" => ["quotations.destroy", $quotation->id], "method" => "DELETE"] ).'
                            <li>
                              <button type="submit" class="btn btn-link" onclick="return confirmDelete()"><i class="dripicons-trash"></i> '.__("db.delete").'</button>
                            </li>'.\Form::close().'
                        </ul>
                    </div>';

                // data for quotation details by one click

                $warehouse = $quotation->warehouse;
                $warehouse_name = $warehouse ? $warehouse->name : '';
                $warehouse_address = $warehouse ? ($warehouse->address ?? '') : '';
                $warehouse_phone = $warehouse ? ($warehouse->phone ?? '') : '';
                $nestedData['quotation'] = [
                    date(config('date_format'), strtotime($quotation->created_at->toDateString())),
                    (string) $quotation->reference_no,
                    (string) $status,
                    (string) ($quotation->biller->name ?? ''),
                    (string) ($quotation->biller->company_name ?? ''),
                    (string) ($quotation->biller->email ?? ''),
                    (string) ($quotation->biller->phone_number ?? ''),
                    (string) ($quotation->biller->address ?? ''),
                    (string) ($quotation->biller->city ?? ''),
                    (string) ($quotation->customer->name ?? ''),
                    (string) ($quotation->customer->phone_number ?? ''),
                    (string) ($quotation->customer->address ?? ''),
                    (string) ($quotation->customer->city ?? ''),
                    (string) $quotation->id,
                    (string) $quotation->total_tax,
                    (string) $quotation->total_discount,
                    (string) $quotation->total_price,
                    (string) $quotation->order_tax,
                    (string) $quotation->order_tax_rate,
                    (string) $quotation->order_discount,
                    (string) $quotation->shipping_cost,
                    (string) $quotation->grand_total,
                    preg_replace('/\s+/S', ' ', (string) ($quotation->note ?? '')),
                    (string) ($quotation->user->name ?? ''),
                    (string) ($quotation->user->email ?? ''),
                    (string) ($quotation->document ?? ''),
                    preg_replace('/\s+/S', ' ', (string) ($quotation->delivery_address ?? '')),
                    (string) $warehouse_name,
                    (string) $warehouse_address,
                    (string) $warehouse_phone,
                    (string) ($quotation->terms ?? ''),
                    preg_replace('/\s+/S', ' ', (string) ($quotation->terms_conditions ?? '')),
                ];
                $data[] = $nestedData;
            }
        }
        $json_data = array(
            "draw"            => intval($request->input('draw')),
            "recordsTotal"    => intval($totalData),
            "recordsFiltered" => intval($totalFiltered),
            "data"            => $data
        );

        echo json_encode($json_data);
    }

    public function create()
    {
        $role = Role::find(Auth::user()->role_id);
        if($role->hasPermissionTo('quotes-add')){
            $lims_biller_list = Biller::where('is_active', true)->get();
            $lims_warehouse_list = Warehouse::where('is_active', true)->get();
            $lims_customer_list = Customer::where([
                'is_active' => true,
                'type' => CustomerTypeEnum::REGULAR->value,
            ])->get();
            $lims_supplier_list = Supplier::where('is_active', true)->get();
            $lims_tax_list = Tax::where('is_active', true)->get();
            $lims_customer_group_all = CustomerGroup::where('is_active', true)->get();

            return view('backend.quotation.create', compact('lims_biller_list', 'lims_warehouse_list', 'lims_customer_list', 'lims_supplier_list', 'lims_tax_list', 'lims_customer_group_all'));
        }
        else
            return redirect()->back()->with('not_permitted', __('db.Sorry! You are not allowed to access this module'));
    }

    public function store(Request $request)
    {
        $data = $request->except('document');
        //return dd($data);
        $data['user_id'] = Auth::id();
        $document = $request->document;
        if($document){
            $v = Validator::make(
                [
                    'extension' => strtolower($request->document->getClientOriginalExtension()),
                ],
                [
                    'extension' => 'in:jpg,jpeg,png,gif,pdf,csv,docx,xlsx,txt',
                ]
            );
            if ($v->fails())
                return redirect()->back()->withErrors($v->errors());
            $ext = pathinfo($document->getClientOriginalName(), PATHINFO_EXTENSION);
            $documentName = date("Ymdhis");
            if(!config('database.connections.GPEsaas_landlord')) {
                $documentName = $documentName . '.' . $ext;
                $document->move(public_path('documents/quotation'), $documentName);
            }
            else {
                $documentName = $this->getTenantId() . '_' . $documentName . '.' . $ext;
                $document->move(public_path('documents/quotation'), $documentName);
            }
            $data['document'] = $documentName;
        }
        // Generate quotation reference number with prefix from settings
        $general_setting = GeneralSetting::latest()->first();
        $prefix = rtrim((string) ($general_setting->quotation_prefix ?? 'GPE QTN'), " -_/");
        $data['reference_no'] = $prefix . '-' . date("his");
        $lims_quotation_data = Quotation::create($data);
        if($lims_quotation_data->quotation_status == 2){
            //collecting mail data
            $lims_customer_data = Customer::find($data['customer_id']);
            $mail_data['email'] = $lims_customer_data->email;
            $mail_data['reference_no'] = $lims_quotation_data->reference_no;
            $mail_data['total_qty'] = $lims_quotation_data->total_qty;
            $mail_data['total_price'] = $lims_quotation_data->total_price;
            $mail_data['order_tax'] = $lims_quotation_data->order_tax;
            $mail_data['order_tax_rate'] = $lims_quotation_data->order_tax_rate;
            $mail_data['order_discount'] = $lims_quotation_data->order_discount;
            $mail_data['shipping_cost'] = $lims_quotation_data->shipping_cost;
            $mail_data['grand_total'] = $lims_quotation_data->grand_total;
        }
        $product_id = $data['product_id'] ?? [];
        $product_batch_id = $data['product_batch_id'] ?? [];
        $product_code = $data['product_code'] ?? [];
        $product_name = isset($data['product_name']) ? $data['product_name'] : [];
        $qty = $data['qty'] ?? [];
        $sale_unit = $data['sale_unit'] ?? [];
        $net_unit_price = $data['net_unit_price'] ?? [];
        $discount = $data['discount'] ?? [];
        $tax_rate = $data['tax_rate'] ?? [];
        $tax = $data['tax'] ?? [];
        $total = $data['subtotal'] ?? [];
        $product_quotation = [];
        $log_data['item_description'] = '';

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

        // Debug logging
        \Log::info('Quotation Store - Product IDs: ' . json_encode($product_id));
        \Log::info('Quotation Store - Product Names: ' . json_encode($product_name));
        \Log::info('Quotation Store - Product Codes: ' . json_encode($product_code));
        
        foreach ($product_id as $i => $id) {
            try {
                \Log::info("Processing index $i: product_id=$id, product_name=" . ($product_name[$i] ?? 'NOT SET'));
                
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
                        $product_qty = $qty[$i] ?? 0;
                        $code = trim($product_code[$i] ?? '');
                        if ($code === '' || Product::where('code', $code)->exists()) {
                            $code = 'QTN-' . $lims_quotation_data->id . '-' . $i . '-' . substr(uniqid(), -6);
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
                            \Log::info("Created new product: ID=$id, Name=$name, Code=" . $code);
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
                            \Log::error('Quotation store line-item product create failed: ' . $createEx->getMessage());
                            continue;
                        }
                    }
                }
                
                $lims_product_data = Product::find($id);
                
                // Skip if product not found
                if (!$lims_product_data) {
                    continue;
                }

                // If user edited product name in quotation line-item, update master product name as well.
                if (isset($product_name[$i]) && trim((string) $product_name[$i]) !== '') {
                    $editedName = trim((string) $product_name[$i]);
                    if ($lims_product_data->name !== $editedName) {
                        $lims_product_data->name = $editedName;
                        $lims_product_data->save();
                    }
                }

                $current_sale_unit = $sale_unit[$i] ?? '';
                $resolvedUnit = $this->resolveQuotationSaleUnit($current_sale_unit, $default_unit);
                $sale_unit_id = $resolvedUnit['id'];
                $lims_sale_unit_data = $resolvedUnit['model'];

                $current_qty = $qty[$i] ?? 0;
                if($sale_unit_id && $lims_sale_unit_data) {
                    $log_data['item_description'] .= $lims_product_data->name. '-'. $current_qty.' '.$lims_sale_unit_data->unit_code.'<br>';
                    $mail_data['unit'][$i] = $lims_sale_unit_data->unit_code;
                }
                else {
                    $log_data['item_description'] .= $lims_product_data->name. '-'. $current_qty.'<br>';
                    $mail_data['unit'][$i] = '';
                }

                if($lims_product_data->is_variant) {
                    $lims_product_variant_data = ProductVariant::select('variant_id')->FindExactProductWithCode($id, $product_code[$i] ?? '')->first();
                    $product_quotation['variant_id'] = $lims_product_variant_data ? $lims_product_variant_data->variant_id : null;
                }
                else
                    $product_quotation['variant_id'] = null;
                    
                if($product_quotation['variant_id']){
                    $variant_data = Variant::find($product_quotation['variant_id']);
                    $mail_data['products'][$i] = $lims_product_data->name . ' [' . ($variant_data ? $variant_data->name : '') .']';
                }
                else
                    $mail_data['products'][$i] = $lims_product_data->name;
                    
                $product_quotation['quotation_id'] = $lims_quotation_data->id;
                $product_quotation['product_id'] = $id;
                $product_quotation['product_batch_id'] = $product_batch_id[$i] ?? null;
                $product_quotation['qty'] = $mail_data['qty'][$i] = $current_qty;
                $product_quotation['sale_unit_id'] = $sale_unit_id;
                $product_quotation['net_unit_price'] = $net_unit_price[$i] ?? 0;
                $product_quotation['discount'] = $discount[$i] ?? 0;
                $product_quotation['tax_rate'] = $tax_rate[$i] ?? 0;
                $product_quotation['tax'] = $tax[$i] ?? 0;
                $product_quotation['total'] = $mail_data['total'][$i] = $total[$i] ?? 0;
                ProductQuotation::create($product_quotation);
                
            } catch (\Exception $e) {
                // Log error but continue processing other products
                \Log::error('Error processing quotation product at index ' . $i . ': ' . $e->getMessage());
                \Log::error('Stack trace: ' . $e->getTraceAsString());
                continue;
            }
        }
        $message = 'Quotation created successfully';

        //creating log
        $log_data['action'] = 'Quotation Created';
        $log_data['user_id'] = Auth::id();
        $log_data['reference_no'] = $lims_quotation_data->reference_no;
        $log_data['date'] = $lims_quotation_data->created_at->toDateString();            
        // $log_data['admin_email'] = config('admin_email');
        $log_data['admin_message'] = Auth::user()->name . ' has created a quotation. Reference No: ' .$lims_quotation_data->reference_no;
        $log_data['user_email'] = Auth::user()->email;
        $log_data['user_name'] = Auth::user()->name;
        $log_data['user_message'] = 'You just created a quotation. Reference No: ' .$lims_quotation_data->reference_no;
        // $log_data['mail_setting'] = $mail_setting = MailSetting::latest()->first();
        $this->createActivityLog($log_data);

        $mail_setting = MailSetting::latest()->first();
        if($lims_quotation_data->quotation_status == 2 && $mail_data['email'] && $mail_setting) {
            $this->setMailInfo($mail_setting);
            try{
                Mail::to($mail_data['email'])->send(new QuotationDetails($mail_data));
            }
            catch(\Exception $e){
                $message = 'Quotation created successfully. Please setup your <a href="setting/mail_setting">mail setting</a> to send mail.';
            }
        }
        return redirect('quotations')->with('message', $message);
    }

    public function sendMail(Request $request)
    {
        $data = $request->all();
        $lims_quotation_data = Quotation::find($data['quotation_id']);
        $lims_product_quotation_data = ProductQuotation::where('quotation_id', $data['quotation_id'])->get();
        $lims_customer_data = Customer::find($lims_quotation_data->customer_id);
        $mail_setting = MailSetting::latest()->first();

        if(!$mail_setting) {
            $message = 'Please setup your <a href="setting/mail_setting">mail setting</a> to send mail.';
        }else if(!$request->email && !$lims_customer_data->email) {
            $message = 'Customer doesnt have email and no manual email provided!';
        }
        else if(($request->email || $lims_customer_data->email) && $mail_setting) {
            $general_setting = GeneralSetting::latest()->first();
            $lims_biller_data = Biller::find($lims_quotation_data->biller_id);
            
            //collecting male data
            $mail_data['email'] = $request->email ?? $lims_customer_data->email;
            $mail_data['reference_no'] = $lims_quotation_data->reference_no;
            $mail_data['created_at'] = date(config('date_format'), strtotime($lims_quotation_data->created_at->toDateString()));
            $mail_data['total_qty'] = $lims_quotation_data->total_qty;
            $mail_data['total_price'] = $lims_quotation_data->total_price;
            $mail_data['order_tax'] = $lims_quotation_data->order_tax;
            $mail_data['order_tax_rate'] = $lims_quotation_data->order_tax_rate;
            $mail_data['order_discount'] = $lims_quotation_data->order_discount;
            $mail_data['shipping_cost'] = $lims_quotation_data->shipping_cost;
            $mail_data['grand_total'] = $lims_quotation_data->grand_total;

            // Biller Info
            $mail_data['biller_name'] = $lims_biller_data->name;
            $mail_data['biller_company'] = $lims_biller_data->company_name;
            $mail_data['biller_phone'] = $lims_biller_data->phone_number;
            $mail_data['biller_address'] = $lims_biller_data->address;
            $mail_data['biller_city'] = $lims_biller_data->city;
            $mail_data['biller_email'] = $lims_biller_data->email;

            // Customer Info
            $mail_data['customer_name'] = $lims_customer_data->name;
            $mail_data['customer_phone'] = $lims_customer_data->phone_number;
            $mail_data['customer_address'] = $lims_customer_data->address;
            $mail_data['customer_city'] = $lims_customer_data->city;

            foreach ($lims_product_quotation_data as $key => $product_quotation_data) {
                $lims_product_data = Product::find($product_quotation_data->product_id);
                if($product_quotation_data->variant_id) {
                    $variant_data = Variant::find($product_quotation_data->variant_id);
                    $mail_data['products'][$key] = $lims_product_data->name . ' [' . $variant_data->name . ']';
                    $mail_data['product_codes'][$key] = $variant_data->item_code ?? $lims_product_data->code;
                }
                else {
                    $mail_data['products'][$key] = $lims_product_data->name;
                    $mail_data['product_codes'][$key] = $lims_product_data->code;
                }

                if($product_quotation_data->sale_unit_id){
                    $lims_unit_data = Unit::find($product_quotation_data->sale_unit_id);
                    $mail_data['unit'][$key] = $lims_unit_data->unit_code;
                }
                else
                    $mail_data['unit'][$key] = '';

                $mail_data['qty'][$key] = $product_quotation_data->qty;
                $mail_data['total'][$key] = $product_quotation_data->total;
                $mail_data['discount'][$key] = $product_quotation_data->discount;
            }

            // Total in Words
            try {
                $numberToWords = new NumberToWords();
                $transformer = $numberToWords->getNumberTransformer('en');
                $mail_data['total_in_words'] = $transformer->toWords($lims_quotation_data->grand_total);
            } catch (\Exception $e) {
                $mail_data['total_in_words'] = '';
            }

            $this->setMailInfo($mail_setting);
            try{
                $pdf = Pdf::loadView('backend.mail.quotation_details', ['quotation_data' => $mail_data, 'general_setting' => $general_setting, 'pdf_mode' => true]);
                Mail::to($mail_data['email'])->send(new QuotationDetails($mail_data, $pdf->output()));
                $message = 'Mail sent successfully with PDF attachment';
            }
            catch(\Exception $e){
                $message = 'Please setup your <a href="setting/mail_setting">mail setting</a> to send mail.';
            }
        }

        return redirect()->back()->with('message', $message);
    }

    public function getCustomerGroup($id)
    {
         $lims_customer_data = Customer::find($id);
         $lims_customer_group_data = CustomerGroup::find($lims_customer_data->customer_group_id);
         return $lims_customer_group_data->percentage;
    }

    public function getProduct($id)
    {
        $product_code = [];
        $product_name = [];
        $product_qty = [];
        $product_price = [];
        $product_data = [];
        $batch_no = [];
        $product_batch_id = [];
        $expired_date = [];
        $is_embeded = [];
        $imei_number = [];

        //retrieve data of product without variant
        $lims_product_warehouse_data = Product::join('product_warehouse', 'products.id', '=', 'product_warehouse.product_id')
        ->where([
            ['products.is_active', true],
            ['product_warehouse.warehouse_id', $id],
        ])
        ->whereNull('product_warehouse.variant_id')
        ->whereNull('product_warehouse.product_batch_id')
        ->select('product_warehouse.*')
        ->get();

        foreach ($lims_product_warehouse_data as $product_warehouse)
        {
            $product_qty[] = $product_warehouse->qty;
            $product_price[] = $product_warehouse->price;
            $lims_product_data = Product::find($product_warehouse->product_id);
            $product_code[] =  $lims_product_data->code;
            $product_name[] = $lims_product_data->name;
            $product_type[] = $lims_product_data->type;
            $product_id[] = $lims_product_data->id;
            $product_list[] = null;
            $qty_list[] = null;
            $batch_no[] = null;
            $product_batch_id[] = null;
            $expired_date[] = null;
            if($product_warehouse->is_embeded)
                $is_embeded[] = $product_warehouse->is_embeded;
            else
                $is_embeded[] = 0;
            $imei_number[] = null;
        }

        $lims_product_with_imei_warehouse_data = Product::join('product_warehouse', 'products.id', '=', 'product_warehouse.product_id')
        ->where([
            ['products.is_active', true],
            ['products.is_imei', true],
            ['product_warehouse.warehouse_id', $id],
            ['product_warehouse.qty', '>', 0]
        ])
        ->whereNull('product_warehouse.variant_id')
        ->whereNotNull('product_warehouse.imei_number')
        ->select('product_warehouse.*', 'products.is_embeded')
        ->groupBy('product_warehouse.product_id')
        ->get();

        config()->set('database.connections.mysql.strict', false);
        \DB::reconnect(); //important as the existing connection if any would be in strict mode

        $lims_product_with_batch_warehouse_data = Product::join('product_warehouse', 'products.id', '=', 'product_warehouse.product_id')
        ->where([
            ['products.is_active', true],
            ['product_warehouse.warehouse_id', $id],
        ])
        ->whereNull('product_warehouse.variant_id')
        ->whereNotNull('product_warehouse.product_batch_id')
        ->select('product_warehouse.*')
        ->groupBy('product_warehouse.product_id')
        ->get();

        //now changing back the strict ON
        config()->set('database.connections.mysql.strict', true);
        \DB::reconnect();

        foreach ($lims_product_with_batch_warehouse_data as $product_warehouse)
        {
            $product_qty[] = $product_warehouse->qty;
            $product_price[] = $product_warehouse->price;
            $lims_product_data = Product::find($product_warehouse->product_id);
            $product_code[] =  $lims_product_data->code;
            $product_name[] = $lims_product_data->name;
            $product_type[] = $lims_product_data->type;
            $product_id[] = $lims_product_data->id;
            $product_list[] = null;
            $qty_list[] = null;
            $product_batch_data = ProductBatch::select('id', 'batch_no')->find($product_warehouse->product_batch_id);
            $batch_no[] = $product_batch_data->batch_no;
            $product_batch_id[] = $product_batch_data->id;
            $expired_date[] = null;
            if($product_warehouse->is_embeded)
                $is_embeded[] = $product_warehouse->is_embeded;
            else
                $is_embeded[] = 0;
            $imei_number[] = null;
        }

          //product with imei
          foreach ($lims_product_with_imei_warehouse_data as $product_warehouse)
          {
              $imei_numbers = explode(",", $product_warehouse->imei_number);
              foreach ($imei_numbers as $key => $number) {
                  $product_qty[] = $product_warehouse->qty;
                  $product_price[] = $product_warehouse->price;
                  $lims_product_data = Product::find($product_warehouse->product_id);
                  $product_code[] =  $lims_product_data->code;
                  $product_name[] = htmlspecialchars($lims_product_data->name);
                  $product_type[] = $lims_product_data->type;
                  $product_id[] = $lims_product_data->id;
                  $product_list[] = $lims_product_data->product_list;
                  $qty_list[] = $lims_product_data->qty_list;
                  $batch_no[] = null;
                  $product_batch_id[] = null;
                  $expired_date[] = null;
                  $is_embeded[] = 0;
                  $imei_number[] = $number;
              }
          }

        //retrieve data of product with variant
        $lims_product_warehouse_data = Product::join('product_warehouse', 'products.id', '=', 'product_warehouse.product_id')
        ->where([
            ['products.is_active', true],
            ['product_warehouse.warehouse_id', $id],
        ])->whereNotNull('product_warehouse.variant_id')->select('product_warehouse.*')->get();
        foreach ($lims_product_warehouse_data as $product_warehouse)
        {
            $lims_product_data = Product::find($product_warehouse->product_id);
            $lims_product_variant_data = ProductVariant::select('item_code')->FindExactProduct($product_warehouse->product_id, $product_warehouse->variant_id)->first();
            if($lims_product_variant_data) {
                $product_qty[] = $product_warehouse->qty;
                $product_code[] =  $lims_product_variant_data->item_code;
                $product_name[] = $lims_product_data->name;
                $product_type[] = $lims_product_data->type;
                $product_id[] = $lims_product_data->id;
                $product_list[] = null;
                $qty_list[] = null;
                $batch_no[] = null;
                $product_batch_id[] = null;
            }
            $expired_date[] = null;
            if($product_warehouse->is_embeded)
                $is_embeded[] = $product_warehouse->is_embeded;
            else
                $is_embeded[] = 0;
            $imei_number[] = null;
        }
        //retrieve product data of digital and combo
        $lims_product_data = Product::whereNotIn('type', ['standard'])->where('is_active', true)->get();
        foreach ($lims_product_data as $product)
        {
            $product_qty[] = $product->qty;
            $product_code[] =  $product->code;
            $product_name[] = $product->name;
            $product_type[] = $product->type;
            $product_id[] = $product->id;
            $product_list[] = $product->product_list;
            $lims_product_data = $product->id;
            $qty_list[] = $product->qty_list;
            $expired_date[] = null;
            $is_embeded[] = 0;
            $imei_number[] = null;
        }
        $product_data = [$product_code, $product_name, $product_qty, $product_type, $product_id, $product_list, $qty_list, $product_price, $batch_no, $product_batch_id, $expired_date, $is_embeded, $imei_number];
        return $product_data;
    }

    public function limsProductSearch(Request $request)
    {
        //return $request['data'];
        $todayDate = date('Y-m-d');
        $rawData = $request['data'] ?? '';
        $product_info = explode('?', $rawData, 3);
        $customer_id = $product_info[1] ?? null;

        $parsed = LimsProductSearchPayload::parseFourFieldPipePayload($product_info[0] ?? '');
        if ($parsed === null) {
            return [];
        }
        $product_data = $parsed;

        if (! empty($product_data[3]) && $product_data[3][0]) {
            $embeded_code = $product_data[0];
            $product_data[0] = substr($embeded_code, 0, 7);
            $qty = substr($embeded_code, 7, 5) / 1000;
        } else {
            $qty = $product_info[2] ?? 1;
        }
        $product_variant_id = null;
        $all_discount = DB::table('discount_plan_customers')
                        ->join('discount_plans', 'discount_plans.id', '=', 'discount_plan_customers.discount_plan_id')
                        ->join('discount_plan_discounts', 'discount_plans.id', '=', 'discount_plan_discounts.discount_plan_id')
                        ->join('discounts', 'discounts.id', '=', 'discount_plan_discounts.discount_id')
                        ->where([
                            ['discount_plans.is_active', true],
                            ['discounts.is_active', true],
                            ['discount_plan_customers.customer_id', $customer_id]
                        ])
                        ->select('discounts.*')
                        ->get();
        // return $product_data[0];
        $lims_product_data = Product::where([
            ['code', $product_data[0]],
            ['is_active', true]
        ])->first();

        if (! $lims_product_data) {
            $lims_product_data = Product::join('product_variants', 'products.id', 'product_variants.product_id')
                ->select('products.*', 'product_variants.id as product_variant_id', 'product_variants.item_code', 'product_variants.additional_price')
                ->where([
                    ['product_variants.item_code', $product_data[0]],
                    ['products.is_active', true],
                ])->first();

            if (! $lims_product_data) {
                return [];
            }
            $product_variant_id = $lims_product_data->product_variant_id;
            $lims_product_data->price += $lims_product_data->additional_price;
        }

        $product[] = $lims_product_data->name;
        if($lims_product_data->is_variant){
            $product[] = $lims_product_data->item_code;
            $lims_product_data->price += $lims_product_data->additional_price;
        }
        else
            $product[] = $lims_product_data->code;

        $no_discount = 1;
        foreach ($all_discount as $key => $discount) {
            $product_list = explode(",", $discount->product_list);
            $days = explode(",", $discount->days);

            if( ( $discount->applicable_for == 'All' || in_array($lims_product_data->id, $product_list) ) && ( $todayDate >= $discount->valid_from && $todayDate <= $discount->valid_till && in_array(date('D'), $days) && $qty >= $discount->minimum_qty && $qty <= $discount->maximum_qty ) ) {
                if($discount->type == 'flat') {
                    $product[] = $lims_product_data->price - $discount->value;
                }
                elseif($discount->type == 'percentage') {
                    $product[] = $lims_product_data->price - ($lims_product_data->price * ($discount->value/100));
                }
                $no_discount = 0;
                break;
            }
            else {
                continue;
            }
        }

        if($lims_product_data->promotion && $todayDate <= $lims_product_data->last_date && $no_discount) {
            $product[] = $lims_product_data->promotion_price;
        }
        elseif($no_discount)
            $product[] = $lims_product_data->price;

        if($lims_product_data->tax_id) {
            $lims_tax_data = Tax::find($lims_product_data->tax_id);
            if($lims_tax_data) {
                $product[] = $lims_tax_data->rate;
                $product[] = $lims_tax_data->name;
            } else {
                $product[] = 0;
                $product[] = 'No Tax';
            }
        }
        else{
            $product[] = 0;
            $product[] = 'No Tax';
        }
        $product[] = $lims_product_data->tax_method;
        if($lims_product_data->type == 'standard'){
            $units = Unit::where("base_unit", $lims_product_data->unit_id)
                    ->orWhere('id', $lims_product_data->unit_id)
                    ->get();
            $unit_name = array();
            $unit_operator = array();
            $unit_operation_value = array();
            foreach ($units as $unit) {
                if($lims_product_data->sale_unit_id == $unit->id) {
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
            $product[] = implode(",",$unit_name) . ',';
            $product[] = implode(",",$unit_operator) . ',';
            $product[] = implode(",",$unit_operation_value) . ',';
        }
        else{
            $product[] = 'n/a'. ',';
            $product[] = 'n/a'. ',';
            $product[] = 'n/a'. ',';
        }
        $product[] = $lims_product_data->id;
        $product[] = $product_variant_id;
        $product[] = $lims_product_data->promotion;
        $product[] = $lims_product_data->is_batch;
        $product[] = $lims_product_data->is_imei;
        $product[] = $lims_product_data->is_variant;
        $product[] = $qty;
        $product[] = $lims_product_data->wholesale_price;
        $product[] = $lims_product_data->cost;
        $product[] = $product_data[2];

        return $product;
    }

    public function productQuotationData($id)
    {
        $lims_product_quotation_data = ProductQuotation::where('quotation_id', $id)->get();
        $product_quotation = [];
        $counter = 0;
        
        foreach ($lims_product_quotation_data as $key => $product_quotation_data) {
            $product = Product::find($product_quotation_data->product_id);
            
            // Skip if product doesn't exist
            if (!$product) {
                continue;
            }
            
            if($product_quotation_data->variant_id) {
                $lims_product_variant_data = ProductVariant::select('item_code')->FindExactProduct($product_quotation_data->product_id, $product_quotation_data->variant_id)->first();
                if($lims_product_variant_data) {
                    $product->code = $lims_product_variant_data->item_code;
                }
            }
            if($product_quotation_data->sale_unit_id){
                $unit_data = Unit::find($product_quotation_data->sale_unit_id);
                $unit = $unit_data ? $unit_data->unit_code : '';
            }
            else
                $unit = '';

            $product_quotation[0][$counter] = $product->name . ' [' . $product->code . ']';
            $product_quotation[1][$counter] = $product_quotation_data->qty;
            $product_quotation[2][$counter] = $unit;
            $product_quotation[3][$counter] = $product_quotation_data->tax;
            $product_quotation[4][$counter] = $product_quotation_data->tax_rate;
            $product_quotation[5][$counter] = $product_quotation_data->discount;
            $product_quotation[6][$counter] = $product_quotation_data->total;
            $product_quotation[8][$counter] = $product_quotation_data->net_unit_price; // Add net unit price
            if($product_quotation_data->product_batch_id) {
                $product_batch_data = ProductBatch::select('batch_no')->find($product_quotation_data->product_batch_id);
                $product_quotation[7][$counter] = $product_batch_data ? $product_batch_data->batch_no : 'N/A';
            }
            else
                $product_quotation[7][$counter] = 'N/A';
            
            $counter++;
        }
        return $product_quotation;
    }

    public function downloadPdf($id)
    {
        $quotation = Quotation::with(['products.product', 'customer', 'biller', 'warehouse'])->findOrFail($id);
        foreach ($quotation->products as $p) {
            $p->unit = Unit::find($p->sale_unit_id);
        }
        $general_setting = GeneralSetting::latest()->first();
        $pdf = Pdf::loadView('backend.quotation.pdf', compact('quotation', 'general_setting'));
        return $pdf->download(preg_replace('/[^a-zA-Z0-9\-]/', '-', $quotation->reference_no) . '.pdf');
    }

    public function edit($id)
    {
        $role = Role::find(Auth::user()->role_id);
        if($role->hasPermissionTo('quotes-edit')){
            $lims_customer_list = Customer::where('is_active', true)->get();
            $lims_warehouse_list = Warehouse::where('is_active', true)->get();
            $lims_biller_list = Biller::where('is_active', true)->get();
            $lims_supplier_list = Supplier::where('is_active', true)->get();
            $lims_tax_list = Tax::where('is_active', true)->get();
            $lims_customer_group_all = CustomerGroup::where('is_active', true)->get();
            $lims_quotation_data = Quotation::find($id);
            $lims_product_quotation_data = ProductQuotation::where('quotation_id', $id)->get();
            return view('backend.quotation.edit',compact('lims_customer_list', 'lims_warehouse_list', 'lims_biller_list', 'lims_tax_list', 'lims_quotation_data','lims_product_quotation_data', 'lims_supplier_list', 'lims_customer_group_all'));
        }
        else
            return redirect()->back()->with('not_permitted', __('db.Sorry! You are not allowed to access this module'));
    }

    public function update(Request $request, $id)
    {
        $data = $request->except('document');
        //return dd($data);
        $document = $request->document;
        $lims_quotation_data = Quotation::find($id);

        // Ensure reference_no has the prefix from settings
        $general_setting = GeneralSetting::latest()->first();
        $prefix = rtrim((string) ($general_setting->quotation_prefix ?? 'GPE QTN'), " -_/");
        
        // Check if existing reference_no has the prefix, if not update it
        if($lims_quotation_data->reference_no && strpos($lims_quotation_data->reference_no, $prefix) !== 0) {
            // Extract the number part from existing reference_no
            $number_part = preg_replace('/^[^-]+-/', '', $lims_quotation_data->reference_no);
            if(empty($number_part) || $number_part == $lims_quotation_data->reference_no) {
                // If no number part found, use the existing reference_no as number part
                $number_part = $lims_quotation_data->reference_no;
            }
            $data['reference_no'] = $prefix . '-' . $number_part;
        } elseif(isset($data['reference_no']) && $data['reference_no']) {
            // If reference_no is being updated from form, ensure it has prefix
            if(strpos($data['reference_no'], $prefix) !== 0) {
                $number_part = preg_replace('/^[^-]+-/', '', $data['reference_no']);
                if(empty($number_part) || $number_part == $data['reference_no']) {
                    $number_part = date("his");
                }
                $data['reference_no'] = $prefix . '-' . $number_part;
            }
        }

        if($document) {
            $v = Validator::make(
                [
                    'extension' => strtolower($request->document->getClientOriginalExtension()),
                ],
                [
                    'extension' => 'in:jpg,jpeg,png,gif,pdf,csv,docx,xlsx,txt',
                ]
            );
            if ($v->fails())
                return redirect()->back()->withErrors($v->errors());

            $this->fileDelete(public_path('documents/quotation/'), $lims_quotation_data->document);

            $ext = pathinfo($document->getClientOriginalName(), PATHINFO_EXTENSION);
            $documentName = date("Ymdhis");
            if(!config('database.connections.GPEsaas_landlord')) {
                $documentName = $documentName . '.' . $ext;
                $document->move(public_path('documents/quotation'), $documentName);
            }
            else {
                $documentName = $this->getTenantId() . '_' . $documentName . '.' . $ext;
                $document->move(public_path('documents/quotation'), $documentName);
            }
            $data['document'] = $documentName;
        }
        $lims_product_quotation_data = ProductQuotation::where('quotation_id', $id)->get();
        //update quotation table
        $lims_quotation_data->update($data);
        if($lims_quotation_data->quotation_status == 2){
            //collecting mail data
            $lims_customer_data = Customer::find($data['customer_id']);
            $mail_data['email'] = $lims_customer_data->email;
            $mail_data['reference_no'] = $lims_quotation_data->reference_no;
            $mail_data['total_qty'] = $data['total_qty'];
            $mail_data['total_price'] = $data['total_price'];
            $mail_data['order_tax'] = $data['order_tax'];
            $mail_data['order_tax_rate'] = $data['order_tax_rate'];
            $mail_data['order_discount'] = $data['order_discount'];
            $mail_data['shipping_cost'] = $data['shipping_cost'];
            $mail_data['grand_total'] = $data['grand_total'];
        }
        $product_id = $data['product_id'] ?? [];
        $product_batch_id = $data['product_batch_id'] ?? [];
        $product_variant_id = $data['product_variant_id'] ?? [];
        $product_code = $data['product_code'] ?? [];
        $product_name = isset($data['product_name']) ? $data['product_name'] : [];
        $qty = $data['qty'] ?? [];
        $sale_unit = $data['sale_unit'] ?? [];
        $net_unit_price = $data['net_unit_price'] ?? [];
        $discount = $data['discount'] ?? [];
        $tax_rate = $data['tax_rate'] ?? [];
        $tax = $data['tax'] ?? [];
        $total = $data['subtotal'] ?? [];
        
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

        foreach ($lims_product_quotation_data as $key => $product_quotation_data) {
            $old_product_id[] = $product_quotation_data->product_id;
            $lims_product_data = Product::select('id')->find($product_quotation_data->product_id);
            if($product_quotation_data->variant_id) {
                $lims_product_variant_data = ProductVariant::select('id')->FindExactProduct($product_quotation_data->product_id, $product_quotation_data->variant_id)->first();
                $old_product_variant_id[] = $lims_product_variant_data->id;
                if(!in_array($lims_product_variant_data->id, $product_variant_id))
                    $product_quotation_data->delete();
            }
            else {
                $old_product_variant_id[] = null;
                if(!in_array($product_quotation_data->product_id, $product_id))
                    $product_quotation_data->delete();
            }
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
        
        $log_data['item_description'] = '';
        foreach ($product_id as $i => $pro_id) {
            try {
                // If product_id is 0 and product_name exists, check/create product
                if (($pro_id == 0 || $pro_id === '0' || $pro_id === '') && isset($product_name[$i]) && !empty($product_name[$i])) {
                    $name = trim($product_name[$i]);
                    
                    // Check if product with this name already exists
                    $existing_product = Product::where('name', $name)
                        ->where('is_active', true)
                        ->first();
                    
                    if ($existing_product) {
                        $pro_id = $existing_product->id;
                        $product_id[$i] = $pro_id;
                    } else {
                        $product_qty = $qty[$i] ?? 0;
                        $code = trim($product_code[$i] ?? '');
                        if ($code === '' || Product::where('code', $code)->exists()) {
                            $code = 'QTN-' . $id . '-' . $i . '-' . substr(uniqid(), -6);
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
                            $pro_id = $new_product->id;
                            $product_id[$i] = $pro_id;
                            if ($product_qty > 0 && isset($data['warehouse_id'])) {
                                $lims_product_warehouse_data = Product_Warehouse::where([
                                    ['product_id', $pro_id],
                                    ['warehouse_id', $data['warehouse_id']]
                                ])->first();
                                if ($lims_product_warehouse_data) {
                                    $lims_product_warehouse_data->qty = $lims_product_warehouse_data->qty + $product_qty;
                                    $lims_product_warehouse_data->save();
                                } else {
                                    Product_Warehouse::create([
                                        'product_id' => $pro_id,
                                        'warehouse_id' => $data['warehouse_id'],
                                        'qty' => $product_qty
                                    ]);
                                }
                            }
                        } catch (\Exception $createEx) {
                            \Log::error('Quotation update line-item product create failed: ' . $createEx->getMessage());
                            continue;
                        }
                    }
                }
                
                $lims_sale_unit_data = null;
                $sale_unit_id = 0;
                
                $current_sale_unit = $sale_unit[$i] ?? '';
                $resolvedUnit = $this->resolveQuotationSaleUnit($current_sale_unit, $default_unit);
                $sale_unit_id = $resolvedUnit['id'];
                $lims_sale_unit_data = $resolvedUnit['model'];
                
                $lims_product_data = Product::select('id', 'name', 'is_variant')->find($pro_id);
                
                // Skip if product not found
                if (!$lims_product_data) {
                    continue;
                }

                // If user edited product name in quotation line-item, update master product name as well.
                if (isset($product_name[$i]) && trim((string) $product_name[$i]) !== '') {
                    $editedName = trim((string) $product_name[$i]);
                    if ($lims_product_data->name !== $editedName) {
                        // Use full model for safe save.
                        $productToUpdate = Product::find($pro_id);
                        if ($productToUpdate) {
                            $productToUpdate->name = $editedName;
                            $productToUpdate->save();
                        }
                    }
                }
            
            if($sale_unit_id && $lims_sale_unit_data) {
                $log_data['item_description'] .= $lims_product_data->name. '-'. $qty[$i].' '.$lims_sale_unit_data->unit_code.'<br>';
                $mail_data['unit'][$i] = $lims_sale_unit_data->unit_code;
            }
            else {
                $log_data['item_description'] .= $lims_product_data->name. '-'. $qty[$i].'<br>';
                $mail_data['unit'][$i] = '';
            }
            
                $input['quotation_id'] = $id;
                $input['product_id'] = $pro_id;
                $input['product_batch_id'] = $product_batch_id[$i] ?? null;
                $input['qty'] = $mail_data['qty'][$i] = $qty[$i] ?? 0;
                $input['sale_unit_id'] = $sale_unit_id;
                $input['net_unit_price'] = $net_unit_price[$i] ?? 0;
                $input['discount'] = $discount[$i] ?? 0;
                $input['tax_rate'] = $tax_rate[$i] ?? 0;
                $input['tax'] = $tax[$i] ?? 0;
                $input['total'] = $mail_data['total'][$i] = $total[$i] ?? 0;
                $flag = 1;
                
                if($lims_product_data->is_variant) {
                    $lims_product_variant_data = ProductVariant::select('variant_id')->FindExactProductWithCode($pro_id, $product_code[$i] ?? '')->first();
                    $input['variant_id'] = $lims_product_variant_data ? $lims_product_variant_data->variant_id : null;
                    
                    if($input['variant_id'] && isset($product_variant_id[$i]) && in_array($product_variant_id[$i], $old_product_variant_id)) {
                        ProductQuotation::where([
                            ['product_id', $pro_id],
                            ['variant_id', $input['variant_id']],
                            ['quotation_id', $id]
                        ])->update($input);
                    }
                    else {
                        ProductQuotation::create($input);
                    }
                    
                    if($input['variant_id']) {
                        $variant_data = Variant::find($input['variant_id']);
                        $mail_data['products'][$i] = $lims_product_data->name . ' [' . ($variant_data ? $variant_data->name : '') . ']';
                    } else {
                        $mail_data['products'][$i] = $lims_product_data->name;
                    }
                }
                else {
                    $input['variant_id'] = null;
                    if(in_array($pro_id, $old_product_id)) {
                        ProductQuotation::where([
                            ['product_id', $pro_id],
                            ['quotation_id', $id]
                        ])->update($input);
                    }
                    else {
                        ProductQuotation::create($input);
                    }
                    $mail_data['products'][$i] = $lims_product_data->name;
                }
                
            } catch (\Exception $e) {
                // Log error but continue processing other products
                \Log::error('Error processing quotation product at index ' . $i . ': ' . $e->getMessage());
                \Log::error('Stack trace: ' . $e->getTraceAsString());
                continue;
            }
        }

        $message = 'Quotation updated successfully';

        //creating log
        $log_data['action'] = 'Quotation Updated';
        $log_data['user_id'] = Auth::id();
        $log_data['reference_no'] = $lims_quotation_data->reference_no;
        $log_data['date'] = $lims_quotation_data->created_at->toDateString();            
        // $log_data['admin_email'] = config('admin_email');
        $log_data['admin_message'] = Auth::user()->name . ' has updated a quotation. Reference No: ' .$lims_quotation_data->reference_no;
        $log_data['user_email'] = Auth::user()->email;
        $log_data['user_name'] = Auth::user()->name;
        $log_data['user_message'] = 'You just updated a quotation. Reference No: ' .$lims_quotation_data->reference_no;
        // $log_data['mail_setting'] = $mail_setting = MailSetting::latest()->first();
        $this->createActivityLog($log_data);

        $mail_setting = MailSetting::latest()->first();
        if($lims_quotation_data->quotation_status == 2 && $mail_data['email'] && $mail_setting) {
            $this->setMailInfo($mail_setting);
            try{
                Mail::to($mail_data['email'])->send(new QuotationDetails($mail_data));
            }
            catch(\Exception $e){
                $message = 'Quotation updated successfully. Please setup your <a href="setting/mail_setting">mail setting</a> to send mail.';
            }
        }
        return redirect('quotations')->with('message', $message);
    }

    public function createSale($id)
    {
        $lims_customer_list = Customer::where('is_active', true)->get();
        $lims_warehouse_list = Warehouse::where('is_active', true)->get();
        $lims_biller_list = Biller::where('is_active', true)->get();
        $lims_tax_list = Tax::where('is_active', true)->get();
        $lims_quotation_data = Quotation::find($id);
        $lims_product_quotation_data = ProductQuotation::where('quotation_id', $id)->get();
        $lims_pos_setting_data = PosSetting::latest()->first();
        return view('backend.quotation.create_sale',compact('lims_customer_list', 'lims_warehouse_list', 'lims_biller_list', 'lims_tax_list', 'lims_quotation_data','lims_product_quotation_data', 'lims_pos_setting_data'));
    }

    public function createPurchase($id)
    {
        $lims_supplier_list = Supplier::where('is_active', true)->get();
        $lims_warehouse_list = Warehouse::where('is_active', true)->get();
        $lims_tax_list = Tax::where('is_active', true)->get();
        $lims_quotation_data = Quotation::find($id);
        $lims_product_quotation_data = ProductQuotation::where('quotation_id', $id)->get();
        $lims_product_list_without_variant = $this->productWithoutVariant();
        $lims_product_list_with_variant = $this->productWithVariant();

        return view('backend.quotation.create_purchase',compact('lims_product_list_without_variant', 'lims_product_list_with_variant', 'lims_supplier_list', 'lims_warehouse_list', 'lims_tax_list', 'lims_quotation_data','lims_product_quotation_data'));
    }

    public function productWithoutVariant()
    {
        return Product::ActiveStandard()->select('id', 'name', 'code')
                ->whereNull('is_variant')->get();
    }

    public function productWithVariant()
    {
        return Product::join('product_variants', 'products.id', 'product_variants.product_id')
                ->ActiveStandard()
                ->whereNotNull('is_variant')
                ->select('products.id', 'products.name', 'product_variants.item_code')
                ->orderBy('position')->get();
    }

    public function deleteBySelection(Request $request)
    {
        $quotation_id = $request['quotationIdArray'];
        foreach ($quotation_id as $id) {
            $lims_quotation_data = Quotation::find($id);
            $lims_product_quotation_data = ProductQuotation::where('quotation_id', $id)->get();
            foreach ($lims_product_quotation_data as $product_quotation_data) {
                $product_quotation_data->delete();
            }
            $lims_quotation_data->delete();
            $this->fileDelete(public_path('documents/quotation/'), $lims_quotation_data->document);
        }
        return 'Quotation deleted successfully!';
    }

    public function destroy($id)
    {
        $lims_quotation_data = Quotation::find($id);
        $lims_product_quotation_data = ProductQuotation::where('quotation_id', $id)->get();
        $log_data['item_description'] = '';
        foreach ($lims_product_quotation_data as $product_quotation_data) {
            $lims_product_data = Product::select('id', 'name', 'is_variant')->find($product_quotation_data->product_id);

            if($product_quotation_data->sale_unit_id) {
                $lims_sale_unit_data = Unit::find($product_quotation_data->sale_unit_id);
                $log_data['item_description'] .= $lims_product_data->name. '-'. $product_quotation_data->qty.' '.$lims_sale_unit_data->unit_code.'<br>';
            }
            else {
                $log_data['item_description'] .= $lims_product_data->name. '-'. $product_quotation_data->qty.'<br>';
            }
            $product_quotation_data->delete();
        }

        //creating log
        $log_data['action'] = 'Quotation Deleted';
        $log_data['user_id'] = Auth::id();
        $log_data['reference_no'] = $lims_quotation_data->reference_no;
        $log_data['date'] = $lims_quotation_data->created_at->toDateString();            
        // $log_data['admin_email'] = config('admin_email');
        $log_data['admin_message'] = Auth::user()->name . ' has deleted a quotation. Reference No: ' .$lims_quotation_data->reference_no;
        $log_data['user_email'] = Auth::user()->email;
        $log_data['user_name'] = Auth::user()->name;
        $log_data['user_message'] = 'You just deleted a quotation. Reference No: ' .$lims_quotation_data->reference_no;
        // $log_data['mail_setting'] = MailSetting::latest()->first();
        $this->createActivityLog($log_data);

        $lims_quotation_data->delete();
        $this->fileDelete(public_path('documents/quotation/'), $lims_quotation_data->document);
        return redirect('quotations')->with('not_permitted', __('db.Quotation deleted successfully'));
    }

    public function quickStoreUnit(Request $request)
    {
        $unitName = trim((string) $request->input('unit_name', ''));
        if ($unitName === '') {
            return response()->json(['success' => false, 'message' => 'Unit name is required.'], 422);
        }

        $existing = Unit::where('is_active', true)
            ->where('unit_name', $unitName)
            ->first();

        if ($existing) {
            return response()->json([
                'success' => true,
                'created' => false,
                'unit' => $existing,
            ]);
        }

        $unit = $this->createQuotationUnit($unitName);

        return response()->json([
            'success' => true,
            'created' => true,
            'unit' => $unit,
        ]);
    }

    protected function resolveQuotationSaleUnit(?string $unitName, Unit $defaultUnit): array
    {
        $unitName = trim((string) $unitName);
        if ($unitName === '' || strtolower($unitName) === 'n/a') {
            return ['id' => $defaultUnit->id, 'model' => $defaultUnit];
        }

        $lims_sale_unit_data = Unit::where('is_active', true)
            ->where('unit_name', $unitName)
            ->first();

        if (!$lims_sale_unit_data) {
            $lims_sale_unit_data = $this->createQuotationUnit($unitName);
        }

        return ['id' => $lims_sale_unit_data->id, 'model' => $lims_sale_unit_data];
    }

    protected function createQuotationUnit(string $unitName): Unit
    {
        $existing = Unit::where('is_active', true)
            ->where('unit_name', $unitName)
            ->first();

        if ($existing) {
            return $existing;
        }

        $code = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $unitName));
        if ($code === '') {
            $code = 'UNIT';
        }
        $code = substr($code, 0, 10);
        $baseCode = $code;
        $suffix = 1;

        while (Unit::where('unit_code', $code)->where('is_active', true)->exists()) {
            $code = substr($baseCode, 0, 7) . $suffix;
            $suffix++;
            if ($suffix > 999) {
                $code = substr($baseCode, 0, 5) . substr(uniqid(), -4);
                break;
            }
        }

        return Unit::create([
            'unit_name' => $unitName,
            'unit_code' => $code,
            'base_unit' => null,
            'operator' => '*',
            'operation_value' => 1,
            'is_active' => true,
        ]);
    }
}
