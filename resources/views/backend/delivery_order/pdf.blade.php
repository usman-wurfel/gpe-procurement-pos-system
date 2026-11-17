<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Delivery Order - {{ $delivery_order->delivery_order_no }}</title>
    <style>
        @page { size: A4; margin: 10mm; }
        body { font-family: Arial, sans-serif; font-size: 12px; width: 100%; max-width: 100%; margin: 0; padding: 0; box-sizing: border-box; }
        * { box-sizing: border-box; }
        table { width: 100%; }
    </style>
</head>
<body>
    @php
        $logo_file = $general_setting->invoice_logo ?: $general_setting->site_logo;
        $logo_url = $logo_file ? public_path('logo/' . $logo_file) : '';
    @endphp
    @include('backend.delivery_order.partials.print_content', ['logo_url' => $logo_url])
</body>
</html>
