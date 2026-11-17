@php
    $perms = isset($role_has_permissions_list) ? $role_has_permissions_list : collect();
    $show_delivery_order = (int) auth()->user()->role_id <= 2
        || $perms->where('name', 'delivery-orders-index')->first()
        || $perms->where('name', 'delivery-orders-add')->first();
    $show_invoice = (int) auth()->user()->role_id <= 2
        || $perms->where('name', 'invoices-index')->first()
        || $perms->where('name', 'invoices-add')->first();
@endphp
@if($show_delivery_order)
    <li class="gpe-sidebar-delivery-order">
        <a href="#delivery-order" aria-expanded="false" data-toggle="collapse"> <i class="fa fa-truck"></i><span>Delivery Order</span></a>
        <ul id="delivery-order" class="collapse list-unstyled ">
            @if((int) auth()->user()->role_id <= 2 || $perms->where('name', 'delivery-orders-index')->first())
                <li id="delivery-order-menu"><a href="{{ route('delivery-orders.index') }}">Delivery Order List</a></li>
            @endif
            @if((int) auth()->user()->role_id <= 2 || $perms->where('name', 'delivery-orders-add')->first())
                <li id="delivery-order-create-menu"><a href="{{ route('delivery-orders.create') }}">Add Delivery Order</a></li>
            @endif
        </ul>
    </li>
@endif
@if($show_invoice)
    <li class="gpe-sidebar-invoice">
        <a href="#invoice" aria-expanded="false" data-toggle="collapse"> <i class="fa fa-file-text-o"></i><span>Invoice</span></a>
        <ul id="invoice" class="collapse list-unstyled ">
            @if((int) auth()->user()->role_id <= 2 || $perms->where('name', 'invoices-index')->first())
                <li id="invoice-menu"><a href="{{ route('invoices.index') }}">Invoice List</a></li>
            @endif
            @if((int) auth()->user()->role_id <= 2 || $perms->where('name', 'invoices-add')->first())
                <li id="invoice-create-menu"><a href="{{ route('invoices.create') }}">Add Invoice</a></li>
            @endif
        </ul>
    </li>
@endif
