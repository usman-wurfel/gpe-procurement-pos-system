<!-- Quick Add Biller Modal -->
<div id="quickAddBillerModal" tabindex="-1" role="dialog" aria-labelledby="quickAddBillerModalLabel" aria-hidden="true" class="modal fade text-left" data-backdrop="static" data-keyboard="false">
    <div role="document" class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="quickAddBillerModalLabel">{{__('db.Add Biller')}}</h5>
                <button type="button" data-dismiss="modal" aria-label="Close" class="close"><span aria-hidden="true"><i class="dripicons-cross"></i></span></button>
            </div>
            <div class="modal-body">
                <form id="quickAddBillerForm">
                    <div class="form-group">
                        <label>{{__('db.name')}} *</label>
                        <input type="text" name="name" required class="form-control">
                    </div>
                    <div class="form-group">
                        <label>{{__('db.Company Name')}} *</label>
                        <input type="text" name="company_name" required class="form-control">
                    </div>
                    <div class="form-group">
                        <label>{{__('db.Email')}} *</label>
                        <input type="email" name="email" placeholder="example@example.com" required class="form-control">
                    </div>
                    <div class="form-group">
                        <label>{{__('db.Phone Number')}} *</label>
                        <input type="text" name="phone_number" required class="form-control">
                    </div>
                    <div class="form-group">
                        <label>{{__('db.Address')}} *</label>
                        <input type="text" name="address" required class="form-control">
                    </div>
                    <div class="form-group">
                        <label>{{__('db.City')}} *</label>
                        <input type="text" name="city" required class="form-control">
                    </div>
                    <button type="submit" class="btn btn-primary">{{__('db.submit')}}</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Quick Add Supplier Modal -->
<div id="quickAddSupplierModal" tabindex="-1" role="dialog" aria-labelledby="quickAddSupplierModalLabel" aria-hidden="true" class="modal fade text-left" data-backdrop="static" data-keyboard="false">
    <div role="document" class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="quickAddSupplierModalLabel">{{__('db.Add Supplier')}}</h5>
                <button type="button" data-dismiss="modal" aria-label="Close" class="close"><span aria-hidden="true"><i class="dripicons-cross"></i></span></button>
            </div>
            <div class="modal-body">
                <form id="quickAddSupplierForm">
                    <div class="form-group">
                        <label>{{__('db.name')}} *</label>
                        <input type="text" name="name" required class="form-control">
                    </div>
                    <div class="form-group">
                        <label>{{__('db.Company Name')}} *</label>
                        <input type="text" name="company_name" required class="form-control">
                    </div>
                    <div class="form-group">
                        <label>{{__('db.Email')}} *</label>
                        <input type="email" name="email" placeholder="example@example.com" required class="form-control">
                    </div>
                    <div class="form-group">
                        <label>{{__('db.Phone Number')}} *</label>
                        <input type="text" name="phone_number" required class="form-control">
                    </div>
                    <div class="form-group">
                        <label>{{__('db.Address')}} *</label>
                        <input type="text" name="address" required class="form-control">
                    </div>
                    <div class="form-group">
                        <label>{{__('db.City')}} *</label>
                        <input type="text" name="city" required class="form-control">
                    </div>
                    <button type="submit" class="btn btn-primary">{{__('db.submit')}}</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Quick Add Customer Modal -->
<div id="quickAddCustomerModal" tabindex="-1" role="dialog" aria-labelledby="quickAddCustomerModalLabel" aria-hidden="true" class="modal fade text-left" data-backdrop="static" data-keyboard="false">
    <div role="document" class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="quickAddCustomerModalLabel">{{__('db.Add Customer')}}</h5>
                <button type="button" data-dismiss="modal" aria-label="Close" class="close"><span aria-hidden="true"><i class="dripicons-cross"></i></span></button>
            </div>
            <div class="modal-body">
                <form id="quickAddCustomerForm">
                    @if(isset($lims_customer_group_all) && count($lims_customer_group_all) > 0)
                        <input type="hidden" name="customer_group_id" value="{{$lims_customer_group_all[0]->id}}">
                    @else
                        <input type="hidden" name="customer_group_id" value="1">
                    @endif
                    
                    <div class="form-group">
                        <label>{{__('db.name')}} *</label>
                        <input type="text" name="customer_name" id="customer_name_input" required class="form-control">
                    </div>
                    <div class="form-group">
                        <label>{{__('db.Company Name')}} *</label>
                        <input type="text" name="company_name" id="company_name_input" required class="form-control">
                    </div>
                    
                    <div class="form-group">
                        <label>{{__('db.Email')}} *</label>
                        <input type="email" name="email" id="email_input" required class="form-control" placeholder="example@example.com">
                    </div>
                    <div class="form-group">
                        <label>{{__('db.Phone Number')}} *</label>
                        <input type="text" name="phone_number" id="phone_number_input" required class="form-control">
                    </div>
                    <div class="form-group">
                        <label>{{__('db.Address')}}</label>
                        <input type="text" name="address" class="form-control">
                    </div>
                    <div class="form-group">
                        <label>{{__('db.City')}}</label>
                        <input type="text" name="city" class="form-control">
                    </div>

                    <button type="submit" class="btn btn-primary">{{__('db.submit')}}</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const companyNameInput = document.getElementById('company_name_input');
        const emailInput = document.getElementById('email_input');
        const phoneInput = document.getElementById('phone_number_input');
        
        if (companyNameInput) {
            companyNameInput.addEventListener('input', function() {
                const companyName = this.value.trim().toLowerCase().replace(/\s+/g, '');
                if (companyName && !emailInput.value) {
                    const randomDigits = Math.floor(1000 + Math.random() * 9000);
                    emailInput.value = companyName + randomDigits + '@gmail.com';
                } else if (!companyName) {
                    emailInput.value = '';
                }
            });
        }

        if (phoneInput) {
            phoneInput.addEventListener('input', function() {
                this.value = this.value.replace(/[^0-9+]/g, '');
            });
        }
    });
</script>

<!-- Quick Add Warehouse Modal -->
<div id="quickAddWarehouseModal" tabindex="-1" role="dialog" aria-labelledby="quickAddWarehouseModalLabel" aria-hidden="true" class="modal fade text-left" data-backdrop="static" data-keyboard="false">
    <div role="document" class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="quickAddWarehouseModalLabel">{{__('db.Add Warehouse')}}</h5>
                <button type="button" data-dismiss="modal" aria-label="Close" class="close"><span aria-hidden="true"><i class="dripicons-cross"></i></span></button>
            </div>
            <div class="modal-body">
                <form id="quickAddWarehouseForm">
                    <div class="form-group">
                        <label>{{__('db.name')}} *</label>
                        <input type="text" name="name" required class="form-control">
                    </div>
                    <div class="form-group">
                        <label>{{__('db.Phone Number')}} *</label>
                        <input type="text" name="phone" required class="form-control">
                    </div>
                    <div class="form-group">
                        <label>{{__('db.Address')}} *</label>
                        <textarea name="address" required class="form-control" rows="3"></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary">{{__('db.submit')}}</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Quick Add Product Modal -->
<div id="quickAddProductModal" tabindex="-1" role="dialog" aria-labelledby="quickAddProductModalLabel" aria-hidden="true" class="modal fade text-left" data-backdrop="static" data-keyboard="false">
    <div role="document" class="modal-dialog modal-xl" style="min-width: 1200px;">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="quickAddProductModalLabel">{{__('db.Add Product')}}</h5>
                <button type="button" data-dismiss="modal" aria-label="Close" class="close"><span aria-hidden="true"><i class="dripicons-cross"></i></span></button>
            </div>
            <div class="modal-body">
                <p class="text-info">{{__('db.Please')}} {{__('db.fill')}} {{__('db.all required fields. After adding, product will be available in the product list.')}}</p>
                <iframe id="productIframe" src="{{route('products.create')}}" style="width:100%; height:700px; border:none;"></iframe>
            </div>
        </div>
    </div>
</div>
