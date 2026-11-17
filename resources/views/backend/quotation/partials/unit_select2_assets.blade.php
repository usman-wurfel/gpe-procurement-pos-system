@push('css')
<style>
    .quotation-order-table-wrap {
        overflow: visible !important;
    }
    .quotation-unit-cell {
        min-width: 150px;
        vertical-align: middle !important;
        position: relative;
        overflow: visible;
    }
    .quotation-unit-cell .bootstrap-select {
        display: block;
        width: 100%;
    }
    .quotation-unit-cell .bootstrap-select.open {
        z-index: 2001;
    }
    .quotation-unit-cell > select.sale-unit-select {
        display: none !important;
    }
    .quotation-unit-cell .bootstrap-select select.sale-unit-select {
        display: none !important;
    }
    .quotation-unit-cell .bootstrap-select,
    .quotation-unit-cell .bootstrap-select > .dropdown-toggle,
    #edit_unit .bootstrap-select,
    #edit_unit .bootstrap-select > .dropdown-toggle {
        width: 100% !important;
    }
    .quotation-unit-cell .bootstrap-select .dropdown-toggle,
    #edit_unit .bootstrap-select .dropdown-toggle {
        background: #fff;
        border: 1px solid #ced4da;
        color: #495057;
        cursor: pointer;
        pointer-events: auto;
    }
    .quotation-unit-cell .dropdown-menu,
    #edit_unit .dropdown-menu {
        z-index: 2000;
    }
    .quotation-unit-cell .dropdown-menu .no-results,
    #edit_unit .dropdown-menu .no-results {
        cursor: pointer;
        color: #28a745;
        font-weight: 500;
    }
</style>
@endpush
