<style>
    .product-name-layout-row { align-items: stretch !important; }
    .product-name-layout-row > .col-md-4 > .form-group:first-of-type { margin-bottom: 0.45rem; }
    .product-name-layout-row > .col-md-4 > .form-group:last-of-type { margin-bottom: 0; }
    .product-name-layout-row > .col-md-4,
    .product-name-layout-row > .col-md-8 {
        display: flex;
        flex-direction: column;
    }
    .product-name-layout-row > .col-md-8 > .form-group-name {
        flex: 1 1 auto;
        display: flex;
        flex-direction: column;
        min-height: 0;
        margin-bottom: 0;
        height: 100%;
    }
    .product-name-layout-row #name {
        min-height: 7.4rem;
        max-height: 118px;
        height: auto;
        flex: 0 1 auto;
        resize: none;
        overflow-y: auto;
        width: 100%;
        line-height: 1.35;
        padding-top: 0.4rem;
        padding-bottom: 0.4rem;
    }
</style>
