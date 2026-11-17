@extends('backend.layout.main') @section('content')

    <x-success-message key="message" />
    <x-error-message key="not_permitted" />

    <section>
        <div class="container-fluid">
            <div class="card">
                <div class="card-header mt-2">
                    <h3 class="text-center">Delivery Order List</h3>
                </div>
                {!! Form::open(['route' => 'delivery-orders.index', 'method' => 'get']) !!}
                <div class="row mb-3">
                    <div class="col-md-3 offset-md-1 mt-3">
                        <div class="form-group row">
                            <label class="d-tc mt-2"><strong>{{__('db.Choose Your Date')}}</strong> &nbsp;</label>
                            <div class="d-tc">
                                <div class="input-group">
                                    <input type="text" class="daterangepicker-field form-control"
                                        value="{{$starting_date}} To {{$ending_date}}" required />
                                    <input type="hidden" name="starting_date" value="{{$starting_date}}" />
                                    <input type="hidden" name="ending_date" value="{{$ending_date}}" />
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mt-3 @if(\Auth::user()->role_id > 2){{'d-none'}}@endif">
                        <div class="form-group row">
                            <label class="d-tc mt-2"><strong>{{__('db.Choose Warehouse')}}</strong> &nbsp;</label>
                            <div class="d-tc">
                                <select id="warehouse_id" name="warehouse_id" class="selectpicker form-control"
                                    data-live-search="true" data-live-search-style="begins">
                                    <option value="0">{{__('db.All Warehouse')}}</option>
                                    @foreach($lims_warehouse_list as $warehouse)
                                        <option value="{{$warehouse->id}}" {{$warehouse->id == $warehouse_id ? 'selected' : ''}}>
                                            {{$warehouse->name}}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mt-3">
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
                        <div class="form-group">
                            <button class="btn btn-primary" id="filter-btn" type="submit">{{__('db.submit')}}</button>
                        </div>
                    </div>
                </div>
                {!! Form::close() !!}
            </div>
            @if(in_array("delivery-orders-add", $all_permission))
                <a href="{{route('delivery-orders.create')}}" class="btn btn-info"><i class="dripicons-plus"></i> Add Delivery
                    Order</a>&nbsp;
            @endif
        </div>
        <div class="table-responsive">
            <table id="delivery-order-table" class="table" style="width: 100%">
                <thead>
                    <tr>
                        <th class="not-exported"></th>
                        <th>{{__('db.date')}}</th>
                        <th>Delivery Order No</th>
                        <th>{{__('db.Biller')}}</th>
                        <th>{{__('db.customer')}}</th>
                        <th>{{__('db.grand total')}}</th>
                        <th>Status</th>
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
                </tfoot>
            </table>
        </div>
    </section>

@endsection

@push('scripts')
    <script type="text/javascript">
        $("ul#delivery-order").siblings('a').attr('aria-expanded', 'true');
        $("ul#delivery-order").addClass("show");
        $("ul#delivery-order #delivery-order-menu").addClass("active");

        var delivery_order_id = [];
        var user_verified = <?php echo json_encode(env('USER_VERIFIED')); ?>;
        var all_permission = <?php echo json_encode($all_permission); ?>;

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

        $('#delivery-order-table').DataTable({
            "processing": true,
            "serverSide": true,
            "ajax": {
                url: "{{ route('delivery-orders.data') }}",
                type: "post",
                data: {
                    starting_date: starting_date,
                    ending_date: ending_date,
                    warehouse_id: warehouse_id,
                    status: status
                }
            },
            "columns": [
                { "data": "key" },
                { "data": "date" },
                { "data": "delivery_order_no" },
                { "data": "biller" },
                { "data": "customer" },
                { "data": "grand_total" },
                { "data": "status" },
                { "data": "options" },
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
                    'targets': [0, 7]
                },
                {
                    'render': function (data, type, row, meta) {
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
            'select': { style: 'multi', selector: 'td:first-child' },
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
            callback: function (startDate, endDate, period) {
                var starting_date = startDate.format('YYYY-MM-DD');
                var ending_date = endDate.format('YYYY-MM-DD');
                var title = starting_date + ' To ' + ending_date;
                $(this).val(title);
                $('input[name="starting_date"]').val(starting_date);
                $('input[name="ending_date"]').val(ending_date);
            }
        });
    </script>
@endpush