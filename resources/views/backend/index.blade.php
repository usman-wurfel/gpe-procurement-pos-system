@extends('backend.layout.main')
@section('content')

@push('css')
<style>
.bootstrap-select:not([class*="col-"]):not([class*="form-control"]):not(.input-group-btn) {width: auto;}
.count-title {margin-top: 15px}
.dashboard-counts strong {font-size: .9rem;margin-top: 5px}
.date-btn:hover {background-color: #099aad !important; color: #fff !important; border-color: #099aad !important;}
.date-btn.active {background-color: #099aad !important; color: #fff !important; border-color: #099aad !important;}
.nav-tabs .nav-link:hover {color: #099aad !important;}
</style>
@endpush

    <x-success-message key="message" />
    <x-error-message key="not_permitted" />

    @php
        if ($general_setting->theme == 'default.css') {
            $color = '#099aad';
            $color_rgba = 'rgba(9, 154, 173, 0.8)';
        } elseif ($general_setting->theme == 'green.css') {
            $color = '#2ecc71';
            $color_rgba = 'rgba(46, 204, 113, 0.8)';
        } elseif ($general_setting->theme == 'blue.css') {
            $color = '#3498db';
            $color_rgba = 'rgba(52, 152, 219, 0.8)';
        } elseif ($general_setting->theme == 'dark.css') {
            $color = '#34495e';
            $color_rgba = 'rgba(52, 73, 94, 0.8)';
        }
    @endphp
    <div class="row">

        <div class="container-fluid">
            @php
                $lims_warehouse_list = App\Models\Warehouse::where('is_active', true)->get();
            @endphp

            <div class="col-md-12 mt-2">
                <div class="brand-text float-left mt-4">
                    <h3 style="font-size:1em">Welcome <span>{{ Auth::user()->name }}</span></h3>
                </div>
                @if (in_array('restaurant', explode(',', cache()->get('general_setting')->modules)))
                    @if (Auth::user()->role_id > 2 && isset(Auth::user()->service_staff))
                        @php
                            $cooked = DB::table('sales')
                                ->where('waiter_id', Auth::user()->id)
                                ->where('sale_status', 5)
                                ->orWhere('sale_status', 6)
                                ->where('sales.created_at', '>=', now()->subDay())
                                ->count();
                        @endphp
                    @elseif(Auth::user()->role_id <= 2)
                        @php
                            $cooked = DB::table('sales')
                                ->where('sale_status', 6)
                                ->where('sales.created_at', '>=', now()->subDay())
                                ->count();
                        @endphp
                    @endif
                @endif
                @if (in_array('restaurant', explode(',', cache()->get('general_setting')->modules)))
                    <a href="{{ route('kitchen.dashboard') }}">
                        <div class="alert alert-warning alert-dismissible text-center mb-2">
                            <strong>{{ $cooked }} {{ __('db.Orders to serve') }}</strong>
                        </div>
                    </a>
                @endif

                @php
                    $revenue_profit_summary = $role_has_permissions_list
                        ->where('name', 'revenue_profit_summary')
                        ->first();
                @endphp
                @if ($revenue_profit_summary)
                    <div class="filter-toggle btn-group d-inline-block">
                        <div class="btn-group" role="group" style="max-width:180px">
                            <div class="d-flex align-items-center">
                                <i class="dripicons-location text-primary"></i>
                                @if (\Auth::user()->role_id <= 2)
                                <select name="warehouse_id" class="selectpicker" id="warehouse_btn" data-live-search="true"
                                    data-live-search-style="begins">
                                    <option value="0">All Warehouse
                                    </option>
                                    @foreach ($lims_warehouse_list as $warehouse)
                                        <option value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>
                                    @endforeach
                                </select>
                                @endif
                            </div>
                        </div>

                        <div class="dropdown d-inline">
                            <button type="button" class="btn btn-defualt dropdown-toggle" data-toggle="dropdown">
                                <i class="dripicons-calendar"></i> Date Range
                            </button>
                            <div class="dropdown-menu">
                                <button class="btn btn-default date-btn w-100" style="border:none"
                                    data-start_date="{{ date('Y-m-d') }}"
                                    data-end_date="{{ date('Y-m-d') }}">Today</button>
                                <button class="btn btn-default date-btn w-100" style="border:none"
                                    data-start_date="{{ date('Y-m-d', strtotime(' -7 day')) }}"
                                    data-end_date="{{ date('Y-m-d') }}">Last 7 Days</button>
                                <button class="btn btn-default date-btn w-100 active" style="border:none"
                                    data-start_date="{{ date('Y') . '-' . date('m') . '-' . '01' }}"
                                    data-end_date="{{ date('Y-m-d') }}">This Month</button>
                                <button class="btn btn-default date-btn w-100" style="border:none"
                                    data-start_date="{{ date('Y') . '-01' . '-01' }}"
                                    data-end_date="{{ date('Y') . '-12' . '-31' }}">This Year</button>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
    <!-- Counts Section -->
    <section class="dashboard-counts pt-0">
        <div class="container-fluid">
            <div class="row">
                @if ($revenue_profit_summary)
                    <div class="col-md-12 form-group">
                        <div class="row">
                            <!-- Count item widget-->
                            <!-- <div class="col-sm-3">
                                <div class="wrapper count-title">
                                    <div class="icon">
                                         <i class="dripicons-cart" style="color: #863636"></i>
                                    </div>
                                    <div>
                                        <div class="count-number total_sale-data">
                                            {{ number_format((float) $total_sale, $general_setting->decimal, '.', '') }}</div>
                                        <div class="name">
                                            <strong style="color: #863636">{{ __('db.Total Sale') }}
                                                <x-info title="Grand Total - Shipping Cost = Total Sale" type="info" />
                                            </strong>
                                        </div>
                                    </div>
                                </div>
                            </div> -->
                            <!-- Count item widget-->
                            <div class="col-sm-3">
                                <div class="wrapper count-title">
                                    <div class="icon"><i class="dripicons-graph-bar" style="color: #099aad"></i></div>
                                    <div>
                                        <div class="count-number revenue-data">
                                            {{ number_format((float) $revenue, $general_setting->decimal, '.', '') }}</div>
                                        <div class="name"><strong style="color: #099aad">Revenue
                                            <x-info title="(grand_total - shipping_cost) - Return +income  =  Revenue" type="info" />
                                            </strong></div>
                                    </div>
                                </div>
                            </div>
                            <!-- Count item widget invoice due-->
                            <!-- <div class="col-sm-3">
                                <div class="wrapper count-title">
                                    <div class="icon">
                                        <i class="dripicons-document" style="color: #0584a0"></i>
                                    </div>
                                    <div>
                                        <div class="count-number invoice-due-data">
                                            {{ number_format((float) $invoice_due, $general_setting->decimal, '.', '') }}</div>
                                        <div class="name"><strong style="color: #0584a0">{{ __('db.Invoice Due') }}
                                                <x-info title="Graned Total - Paid Amount = Invoice Due" type="info" />
                                            </strong></div>
                                    </div>
                                </div>
                            </div> -->
                            <!-- Count item widget-->
                            <div class="col-sm-3">
                                <div class="wrapper count-title">
                                    <div class="icon"><i class="dripicons-document" style="color: #ff8952"></i></div>
                                    <div>
                                        <div class="count-number invoice-count-data">
                                            {{ $total_invoices ?? 0 }}</div>
                                        <div class="name"><strong style="color: #ff8952">Total Invoices
                                                <x-info title="Total Number of Invoices" type="info" /></strong></div>
                                    </div>
                                </div>
                            </div>

                            <!-- Count item widget-->
                            <!-- <div class="col-sm-3">
                                <div class="wrapper count-title">
                                    <div class="icon"><i class="dripicons-download" aria-hidden="true" style="color:#c60031; "></i></div>
                                    <div>
                                        <div class="count-number total_purchase-data">
                                            {{ number_format((float) $purchase - $purchase_return, $general_setting->decimal, '.', '') }}
                                        </div>
                                        <div class="name"><strong
                                                style="color: #c60031">{{ __('db.Total Purchase') }}</strong></div>
                                    </div>
                                </div>
                            </div> -->


                            <!-- Count item widget-->
                            <!-- <div class="col-sm-3">
                                <div class="wrapper count-title">
                                    <div class="icon"><i class="dripicons-warning" style="color: #bdbb39"></i></div>
                                    <div>
                                        <div class="count-number purchase_due-data">
                                            {{ number_format((float) $purchase_due, $general_setting->decimal, '.', '') }}
                                        </div>
                                        <div class="name"><strong
                                                style="color: #bdbb39">{{ __('db.Purchase Due') }}</strong></div>
                                    </div>
                                </div>
                            </div> -->

                            <!-- Count item widget-->
                            <div class="col-sm-3">
                                <div class="wrapper count-title">
                                    <div class="icon"><i class="dripicons-truck" style="color: #00c689"></i></div>
                                    <div>
                                        <div class="count-number delivery-order-count-data">
                                            {{ $total_delivery_orders ?? 0 }}
                                        </div>
                                        <div class="name"><strong
                                                style="color: #00c689">Total Delivery Orders</strong></div>
                                    </div>
                                </div>
                            </div>
                            <!-- Count item widget-->
                            <div class="col-sm-3">
                                <div class="wrapper count-title">
                                    <div class="icon"><i class="dripicons-trophy" style="color: #297ff9"></i></div>
                                    <div>
                                        <div class="count-number profit-data">
                                            {{ number_format((float) $profit, $general_setting->decimal, '.', '') }}</div>
                                        <div class="name"><strong style="color: #297ff9">Profit <x-info title="Revenue + Purchase Return - Product Cost - Expense" type="info" /></strong>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
                @php
                    $cash_flow = $role_has_permissions_list->where('name', 'cash_flow')->first();
                @endphp
                @if ($cash_flow)
                    <div class="col-md-7 mt-4">
                        <div class="card line-chart-example">
                            <div class="card-header d-flex align-items-center">
                                <h4>Cash Flow</h4>
                            </div>
                            <div class="card-body">
                                <canvas id="cashFlow" data-color = "{{ $color }}"
                                    data-color_rgba = "{{ $color_rgba }}"
                                    data-recieved = "{{ json_encode($payment_recieved) }}"
                                    data-sent = "{{ json_encode($payment_sent) }}"
                                    data-month = "{{ json_encode($month) }}"
                                    data-label1="Payment Received"
                                    data-label2="Payment Sent"></canvas>
                            </div>
                        </div>
                    </div>
                @endif
                @php
                    $monthly_summary = $role_has_permissions_list->where('name', 'monthly_summary')->first();
                @endphp
                @if ($monthly_summary)
                    <div class="col-md-5 mt-4">
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h4>{{ date('F') }} {{ date('Y') }}</h4>
                            </div>
                            <div class="pie-chart mb-2">
                                <canvas id="transactionChart" data-color = "{{ $color }}"
                                    data-color_rgba = "{{ $color_rgba }}" data-revenue={{ $revenue }}
                                    data-purchase={{ $purchase }} data-expense={{ $expense }}
                                    data-label1="Purchase" data-label2="Revenue"
                                    data-label3="Expense" width="100" height="95"> </canvas>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <div class="container-fluid">
            <div class="row">
                @php
                    $yearly_report = $role_has_permissions_list->where('name', 'yearly_report')->first();
                @endphp
                @if ($yearly_report)
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-header d-flex align-items-center">
                                <h4>Yearly Report</h4>
                            </div>
                            <div class="card-body">
                                <canvas id="saleChart" data-sale_chart_value = "{{ json_encode($yearly_sale_amount) }}"
                                    data-purchase_chart_value = "{{ json_encode($yearly_purchase_amount) }}"
                                    data-label1="Purchased Amount"
                                    data-label2="Sold Amount"></canvas>
                            </div>
                        </div>
                    </div>
                @endif
                @if ($dashRecentTxAny)
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h4>Recent Transaction</h4>
                            <div class="right-column">
                                <div class="badge badge-primary">Latest 5</div>
                            </div>
                        </div>
                        <ul class="nav nav-tabs" role="tablist">
                            @if ($dashRecentTx['sale'])
                            <li class="nav-item">
                                <a class="nav-link {{ $dashRecentTxFirst === 'sale' ? 'active' : '' }}" href="#sale-latest" role="tab"
                                    data-toggle="tab">Sale</a>
                            </li>
                            @endif
                            @if ($dashRecentTx['purchase'])
                            <li class="nav-item">
                                <a class="nav-link {{ $dashRecentTxFirst === 'purchase' ? 'active' : '' }}" href="#purchase-latest" role="tab"
                                    data-toggle="tab">Purchase</a>
                            </li>
                            @endif
                            @if ($dashRecentTx['quotation'])
                            <li class="nav-item">
                                <a class="nav-link {{ $dashRecentTxFirst === 'quotation' ? 'active' : '' }}" href="#quotation-latest" role="tab"
                                    data-toggle="tab">Quotation</a>
                            </li>
                            @endif
                            <li class="nav-item" style="display: none;">
                                <a class="nav-link" href="#payment-latest" role="tab"
                                    data-toggle="tab">Payment</a>
                            </li>
                            @if ($dashRecentTx['invoice'])
                            <li class="nav-item">
                                <a class="nav-link {{ $dashRecentTxFirst === 'invoice' ? 'active' : '' }}" href="#invoice-latest" role="tab"
                                    data-toggle="tab">Invoice</a>
                            </li>
                            @endif
                            @if ($dashRecentTx['delivery_order'])
                            <li class="nav-item">
                                <a class="nav-link {{ $dashRecentTxFirst === 'delivery_order' ? 'active' : '' }}" href="#delivery-order-latest" role="tab"
                                    data-toggle="tab">Delivery Order</a>
                            </li>
                            @endif
                        </ul>

                        <div class="tab-content">
                            @if ($dashRecentTx['sale'])
                            <div role="tabpanel" class="tab-pane fade {{ $dashRecentTxFirst === 'sale' ? 'show active' : '' }}" id="sale-latest">
                                <div class="table-responsive">
                                    <table id="recent-sale" class="table">
                                        <thead>
                                            <tr>
                                                <th>Date</th>
                                                <th>Reference</th>
                                                <th>Customer</th>
                                                <th>Status</th>
                                                <th>Grand Total</th>
                                            </tr>
                                        </thead>
                                        <tbody>

                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            @endif
                            @if ($dashRecentTx['purchase'])
                            <div role="tabpanel" class="tab-pane fade {{ $dashRecentTxFirst === 'purchase' ? 'show active' : '' }}" id="purchase-latest">
                                <div class="table-responsive">
                                    <table id="recent-purchase" class="table">
                                        <thead>
                                            <tr>
                                                <th>Date</th>
                                                <th>Reference</th>
                                                <th>Supplier</th>
                                                <th>Status</th>
                                                <th>Grand Total</th>
                                            </tr>
                                        </thead>
                                        <tbody>

                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            @endif
                            @if ($dashRecentTx['quotation'])
                            <div role="tabpanel" class="tab-pane fade {{ $dashRecentTxFirst === 'quotation' ? 'show active' : '' }}" id="quotation-latest">
                                <div class="table-responsive">
                                    <table id="recent-quotation" class="table">
                                        <thead>
                                            <tr>
                                                <th>Date</th>
                                                <th>Reference</th>
                                                <th>Customer</th>
                                                <th>Status</th>
                                                <th>Grand Total</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            @endif
                            <div role="tabpanel" class="tab-pane fade" id="payment-latest">
                                <div class="table-responsive">
                                    <table id="recent-payment" class="table">
                                        <thead>
                                            <tr>
                                                <th>Date</th>
                                                <th>Reference</th>
                                                <th>Amount</th>
                                                <th>Paid By</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            @if ($dashRecentTx['invoice'])
                            <div role="tabpanel" class="tab-pane fade {{ $dashRecentTxFirst === 'invoice' ? 'show active' : '' }}" id="invoice-latest">
                                <div class="table-responsive">
                                    <table id="recent-invoice" class="table">
                                        <thead>
                                            <tr>
                                                <th>Date</th>
                                                <th>Reference</th>
                                                <th>Customer</th>
                                                <th>Status</th>
                                                <th>Grand Total</th>
                                            </tr>
                                        </thead>
                                        <tbody>

                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            @endif
                            @if ($dashRecentTx['delivery_order'])
                            <div role="tabpanel" class="tab-pane fade {{ $dashRecentTxFirst === 'delivery_order' ? 'show active' : '' }}" id="delivery-order-latest">
                                <div class="table-responsive">
                                    <table id="recent-delivery-order" class="table">
                                        <thead>
                                            <tr>
                                                <th>Date</th>
                                                <th>Reference</th>
                                                <th>Customer</th>
                                                <th>Status</th>
                                                <th>Grand Total</th>
                                            </tr>
                                        </thead>
                                        <tbody>

                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </section>


@endsection

@push('scripts')
    <script type="text/javascript">
        function dateFormat(inputDate, format) {
            const date = new Date(inputDate);
            //extract the parts of the date
            const day = date.getDate();
            const month = date.getMonth() + 1;
            const year = date.getFullYear();
            //replace the month
            format = format.replace("m", month.toString().padStart(2, "0"));
            //replace the year
            format = format.replace("Y", year.toString());
            //replace the day
            format = format.replace("d", day.toString().padStart(2, "0"));
            return format;
        }

        @if (!empty($dashRecentTx['sale']))
        $(document).ready(function() {
            $.ajax({
                url: "{{ url('/recent-sale') }}",
                type: 'GET',
                dataType: 'json',
                success: function(data) {
                    if(data && data.length > 0) {
                        data.forEach(function(item) {
                            var sale_date = item.created_at ? dateFormat(item.created_at.split('T')[0],
                                '{{ $general_setting->date_format }}') : '';
                            if (item.sale_status == 1) {
                                var status = '<div class="badge badge-success">Completed</div>';
                            } else if (item.sale_status == 2) {
                                var status = '<div class="badge badge-danger">Pending</div>';
                            } else {
                                var status = '<div class="badge badge-warning">Draft</div>';
                            }
                            var exchange_rate = (item.exchange_rate && item.exchange_rate > 0) ? item.exchange_rate : 1;
                            var grand_total = (item.grand_total && item.grand_total > 0) ? (item.grand_total / exchange_rate).toFixed({{ $general_setting->decimal }}) : '0.00';
                            $('#recent-sale').find('tbody').append('<tr><td>' + sale_date +
                                '</td><td>' + item.reference_no + '</td><td>' + item.name +
                                '</td><td>' + status + '</td><td>' + grand_total.toString()
                                .replace(/\B(?=(\d{3})+(?!\d))/g, ",") + '</td></tr>');
                        });
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error loading recent sales:', error);
                }
            });
        });
        @endif

        @if (!empty($dashRecentTx['purchase']))
        $(document).ready(function() {
            $.ajax({
                url: '{{ url('/recent-purchase') }}',
                type: 'GET',
                dataType: 'json',
                success: function(data) {
                    if(data && data.length > 0) {
                        data.forEach(function(item) {
                            var payment_date = item.created_at ? dateFormat(item.created_at.split('T')[0],
                                '{{ $general_setting->date_format }}') : '';
                            if (item.status == 1) {
                                var status = '<div class="badge badge-success">Received</div>';
                            } else if (item.status == 2) {
                                var status = '<div class="badge badge-danger">Partial</div>';
                            } else if (item.status == 3) {
                                var status = '<div class="badge badge-danger">Pending</div>';
                            } else {
                                var status = '<div class="badge badge-warning">Ordered</div>';
                            }
                            var supplier_name = (item.name && item.name !== 'null' && item.name !== null) ? item.name : '';
                            var exchange_rate = (item.exchange_rate && item.exchange_rate > 0) ? item.exchange_rate : 1;
                            var grand_total = (item.grand_total && item.grand_total > 0) ? (item.grand_total / exchange_rate).toFixed({{ $general_setting->decimal }}) : '0.00';
                            $('#recent-purchase').find('tbody').append('<tr><td>' + payment_date +
                                '</td><td>' + item.reference_no + '</td><td>' + supplier_name +
                                '</td><td>' + status + '</td><td>' + grand_total.toString()
                                .replace(/\B(?=(\d{3})+(?!\d))/g, ",") + '</td></tr>');
                        });
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error loading recent purchases:', error);
                }
            });
        });
        @endif

        @if (!empty($dashRecentTx['quotation']))
        $(document).ready(function() {
            $.ajax({
                url: '{{ url('/recent-quotation') }}',
                type: 'GET',
                dataType: 'json',
                success: function(data) {
                    if(data && data.length > 0) {
                        data.forEach(function(item) {
                            var quotation_date = item.created_at ? dateFormat(item.created_at.split('T')[0],
                                '{{ $general_setting->date_format }}') : '';
                            if (item.quotation_status == 1) {
                                var status = '<div class="badge badge-success">Pending</div>';
                            } else if (item.quotation_status == 2) {
                                var status = '<div class="badge badge-danger">Sent</div>';
                            }
                            $('#recent-quotation').find('tbody').append('<tr><td>' +
                                quotation_date + '</td><td>' + item.reference_no + '</td><td>' +
                                item.name + '</td><td>' + status + '</td><td>' + item
                                .grand_total.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",") +
                                '</td></tr>');
                        });
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error loading recent quotations:', error);
                }
            });
        });
        @endif

        $(document).ready(function() {
            $.ajax({
                url: '{{ url('/recent-payment') }}',
                type: 'GET',
                dataType: 'json',
                success: function(data) {
                    if(data && data.length > 0) {
                        data.forEach(function(item) {
                            var payment_date = item.created_at ? dateFormat(item.created_at.split('T')[0],
                                '{{ $general_setting->date_format }}') : '';
                            $('#recent-payment').find('tbody').append('<tr><td>' + payment_date +
                                '</td><td>' + item.payment_reference + '</td><td>' + (item.amount/item.exchange_rate)
                                .toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",") +
                                '</td><td>' + item.paying_method + '</td></tr>');
                        });
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error loading recent payments:', error);
                }
            });
        });

        @if (!empty($dashRecentTx['invoice']))
        $(document).ready(function() {
            $.ajax({
                url: "{{ url('/recent-invoice') }}",
                type: 'GET',
                dataType: 'json',
                success: function(data) {
                    if(data && data.length > 0) {
                        data.forEach(function(item) {
                            var invoice_date = item.created_at ? dateFormat(item.created_at.split('T')[0],
                                '{{ $general_setting->date_format }}') : '';
                            if (item.status == 1) {
                                var status = '<div class="badge badge-success">Completed</div>';
                            } else if (item.status == 2) {
                                var status = '<div class="badge badge-danger">Pending</div>';
                            } else {
                                var status = '<div class="badge badge-warning">Draft</div>';
                            }
                            var exchange_rate = (item.exchange_rate && item.exchange_rate > 0) ? item.exchange_rate : 1;
                            var grand_total = (item.grand_total && item.grand_total > 0) ? (item.grand_total / exchange_rate).toFixed({{ $general_setting->decimal }}) : '0.00';
                            $('#recent-invoice').find('tbody').append('<tr><td>' + invoice_date +
                                '</td><td>' + item.invoice_no + '</td><td>' + item.name +
                                '</td><td>' + status + '</td><td>' + grand_total.toString()
                                .replace(/\B(?=(\d{3})+(?!\d))/g, ",") + '</td></tr>');
                        });
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error loading recent invoices:', error);
                }
            });
        });
        @endif

        @if (!empty($dashRecentTx['delivery_order']))
        $(document).ready(function() {
            $.ajax({
                url: '{{ url('/recent-delivery-order') }}',
                type: 'GET',
                dataType: 'json',
                success: function(data) {
                    if(data && data.length > 0) {
                        data.forEach(function(item) {
                            var do_date = item.created_at ? dateFormat(item.created_at.split('T')[0],
                                '{{ $general_setting->date_format }}') : '';
                            if (item.status == 1) {
                                var status = '<div class="badge badge-success">Completed</div>';
                            } else if (item.status == 2) {
                                var status = '<div class="badge badge-danger">Pending</div>';
                            } else {
                                var status = '<div class="badge badge-warning">Draft</div>';
                            }
                            var exchange_rate = (item.exchange_rate && item.exchange_rate > 0) ? item.exchange_rate : 1;
                            var grand_total = (item.grand_total && item.grand_total > 0) ? (item.grand_total / exchange_rate).toFixed({{ $general_setting->decimal }}) : '0.00';
                            $('#recent-delivery-order').find('tbody').append('<tr><td>' + do_date +
                                '</td><td>' + item.delivery_order_no + '</td><td>' + item.name +
                                '</td><td>' + status + '</td><td>' + grand_total.toString()
                                .replace(/\B(?=(\d{3})+(?!\d))/g, ",") + '</td></tr>');
                        });
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error loading recent delivery orders:', error);
                }
            });
        });
        @endif


        $(document).ready(function() {
            $.ajax({
                url: '{{ url('/') }}',
                type: 'GET',
                dataType: 'json',
                success: function(data) {
                    $('#userShowModal').modal('show');
                    $('#user-id').text(data.id);
                    $('#user-name').text(data.name);
                    $('#user-email').text(data.email);
                }
            });
        })
        // Show and hide color-switcher
        $(".color-switcher .switcher-button").on('click', function() {
            $(".color-switcher").toggleClass("show-color-switcher", "hide-color-switcher", 300);
        });

        // Color Skins
        $('a.color').on('click', function() {
            /*var title = $(this).attr('title');
            $('#style-colors').attr('href', 'css/skin-' + title + '.css');
            return false;*/
            $.get('setting/general_setting/change-theme/' + $(this).data('color'), function(data) {});
            var style_link = $('#custom-style').attr('href').replace(/([^-]*)$/, $(this).data('color'));
            $('#custom-style').attr('href', style_link);
        });

        $(".date-btn").on("click", function() {
            $(".date-btn").removeClass("active");
            $(this).addClass("active");
            var start_date = $(this).data('start_date');
            var end_date = $(this).data('end_date');
            var warehouse_id = $("#warehouse_btn").val();
            $.get('dashboard-filter/' + start_date + '/' + end_date + '/' + warehouse_id, function(data) {
                dashboardFilter(data);
            });
        });

        $("#warehouse_btn").on("change", function() {
            var warehouse_id = $(this).val();
            var start_date = $('.date-btn.active').data('start_date');
            var end_date = $('.date-btn.active').data('end_date');
            //console.log(start_date);
            //console.log(end_date);
            $.get('dashboard-filter/' + start_date + '/' + end_date + '/' + warehouse_id, function(data) {
                dashboardFilter(data);
            });
        });

        function dashboardFilter(data) {
            // data is an array:
            // [revenue, invoice_count, profit, delivery_order_count, total_sale, invoice_due, total_purchase, purchase_due]

            $('.total_sale-data').hide();
            $('.total_sale-data').html(parseFloat(data[4] ?? 0).toFixed({{ $general_setting->decimal }}));
            $('.total_sale-data').show(500);

            $('.revenue-data').hide();
            $('.revenue-data').html(parseFloat(data[0] ?? 0).toFixed({{ $general_setting->decimal }}));
            $('.revenue-data').show(500);

            $('.invoice-due-data').hide();
            $('.invoice-due-data').html(parseFloat(data[5] ?? 0).toFixed({{ $general_setting->decimal }}));
            $('.invoice-due-data').show(500);

            $('.invoice-count-data').hide();
            $('.invoice-count-data').html(parseInt(data[1] ?? 0));
            $('.invoice-count-data').show(500);

            $('.total_purchase-data').hide();
            $('.total_purchase-data').html(parseFloat(data[6] ?? 0).toFixed({{ $general_setting->decimal }}));
            $('.total_purchase-data').show(500);

            $('.purchase_due-data').hide();
            $('.purchase_due-data').html(parseFloat(data[7] ?? 0).toFixed({{ $general_setting->decimal }}));
            $('.purchase_due-data').show(500);

            $('.delivery-order-count-data').hide();
            $('.delivery-order-count-data').html(parseInt(data[3] ?? 0));
            $('.delivery-order-count-data').show(500);

            $('.profit-data').hide();
            $('.profit-data').html(parseFloat(data[2] ?? 0).toFixed({{ $general_setting->decimal }}));
            $('.profit-data').show(500);
        }
    </script>
@endpush
