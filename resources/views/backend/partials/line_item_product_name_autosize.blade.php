@once
@push('scripts')
<style>
textarea.line-item-product-name {
    overflow: hidden;
    resize: none;
    min-height: calc(1.5em + 0.75rem + 2px);
    line-height: 1.5;
    white-space: pre-wrap;
    word-break: break-word;
}
</style>
<script>
(function ($) {
    function resizeLineItemProductName(el) {
        if (!el || el.tagName !== 'TEXTAREA') return;
        el.style.height = 'auto';
        el.style.height = el.scrollHeight + 'px';
    }
    window.resizeLineItemProductName = resizeLineItemProductName;
    $(function () {
        $(document).on('input', 'textarea.line-item-product-name', function () {
            resizeLineItemProductName(this);
        });
        $(window).on('resize', function () {
            $('textarea.line-item-product-name').each(function () {
                resizeLineItemProductName(this);
            });
        });
    });
})(window.jQuery);
</script>
@endpush
@endonce
