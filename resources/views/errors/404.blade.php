<!DOCTYPE html>
<html dir="ltr" lang="en-US">

<head>
    <!-- Metas -->
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Document Title -->
    <title>404 | Page Not Found</title>


    <!-- Links -->
    <link rel="icon" type="image/ico" href="{{ asset('frontend/images') }}/{{$ecommerce_setting->favicon ?? ''}}" />
    <!-- Plugins CSS -->
    <link href="{{ asset('frontend/css/plugins.css') }}" rel="stylesheet" />

    <!-- style CSS -->
    <link rel="preload" as="style" onload="this.onload=null;this.rel='stylesheet'" href="{{ asset('frontend/css/style.css') }}">
    <noscript>
        <link rel="preload" as="style" onload="this.onload=null;this.rel='stylesheet'" href="{{ asset('frontend/css/style.css') }}">
    </noscript>
    <!-- google fonts-->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@48,400,0,0" rel="stylesheet" />

</head>
<body>
    <section class="error-section mt-5 mb-5">
        <div class="container">
            <div class="row">
                <div class="col-md-12 text-center">
                    <div class="error-icon">
                        <span style="font-size: 60px;" class="material-symbols-outlined">sentiment_dissatisfied</span>
                    </div>
                </div>
                <div class="col-md-6 offset-md-3  error-text text-center">
                    <i class="las la-binoculars"style="color:var(--theme-color);font-size: 90px;margin-bottom:30px"></i>
                    <h2 class="h1">{{__('db.Oh snap! We are lost')}}</h2>
                    <p class="lead">{{__('db.It seems we can not find what you are looking for Perhaps searching can help or go back to')}}
                    <br><br>
                    @php
                        $backUrl = url('/');
                        $backLabel = __('db.Home');
                        if (auth()->check()) {
                            $moduleBack = [
                                'purchases' => [url('purchases'), __('db.Purchase List')],
                                'sales' => [url('sales'), __('db.Sale List')],
                                'quotations' => [url('quotations'), __('db.Quotation List')],
                                'delivery-orders' => [url('delivery-orders'), 'Delivery Order List'],
                                'invoices' => [url('invoices'), 'Invoice List'],
                            ];
                            $segment = request()->segment(1);
                            if (isset($moduleBack[$segment])) {
                                [$backUrl, $backLabel] = $moduleBack[$segment];
                            } else {
                                $backUrl = url('dashboard');
                                $backLabel = __('db.dashboard');
                            }
                        }
                    @endphp
                    @if(auth()->check() && url()->previous() && url()->previous() !== url()->current())
                        <a class="button lg me-2" style="background-color: #f5f6f7; border:1px solid #ddd; box-shadow:0 5px 10px rgba(44,44,44,0.1)" href="{{ url()->previous() }}">{{ __('db.Back') }}</a>
                    @endif
                    <a class="button lg" style="background-color: #f5f6f7; border:1px solid #ddd; box-shadow:0 5px 10px rgba(44,44,44,0.1)" href="{{ $backUrl }}">{{ $backLabel }}</a> </p>
                </div>
            </div>
        </div>
    </section>
</body>
</html>
