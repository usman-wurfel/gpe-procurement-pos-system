@once
@push('scripts')
<script>
(function () {
    window.parseLimsProductAutocompleteValue = function (str) {
        if (!str || typeof str !== 'string') return null;
        var qParts = str.split('?');
        var payload = qParts[0] || '';
        var parts = payload.split('|');
        if (parts.length < 4) return null;
        var is_embeded = parts.pop();
        var imei_number = parts.pop();
        var code = parts.shift();
        var name = parts.join('|');
        return {
            code: code,
            name: name,
            imei_number: imei_number,
            is_embeded: is_embeded,
            customer_id: qParts[1],
            qty: qParts[2]
        };
    };
    window.parseLimsTransferAutocompleteValue = function (str) {
        if (!str || typeof str !== 'string') return null;
        var qParts = str.split('?');
        var payload = qParts[0] || '';
        var parts = payload.split('|');
        while (parts.length && parts[parts.length - 1] === '') parts.pop();
        if (parts.length < 5) return null;
        var rowQty = parts.pop();
        var is_embeded = parts.pop();
        var imei_number = parts.pop();
        var code = parts.shift();
        var name = parts.join('|');
        return {
            code: code,
            name: name,
            imei_number: imei_number,
            is_embeded: is_embeded,
            rowQty: rowQty,
            customer_id: qParts[1],
            qty: qParts[2]
        };
    };
})();
</script>
@endpush
@endonce
