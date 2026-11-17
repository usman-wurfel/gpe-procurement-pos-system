<!-- Quick Add Expense Category Modal -->
<div id="quickAddExpenseCategoryModal" tabindex="-1" role="dialog" aria-labelledby="quickAddExpenseCategoryModalLabel" aria-hidden="true" class="modal fade text-left" data-backdrop="false" data-keyboard="false" style="z-index: 1060;">
    <div role="document" class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="quickAddExpenseCategoryModalLabel">{{__('db.Add Expense Category')}}</h5>
                <button type="button" data-dismiss="modal" aria-label="Close" class="close"><span aria-hidden="true"><i class="dripicons-cross"></i></span></button>
            </div>
            <div class="modal-body">
                <form id="quickAddExpenseCategoryForm">
                    <div class="form-group">
                        <label>{{__('db.name')}} *</label>
                        <input type="text" name="name" required class="form-control">
                    </div>
                    <div class="form-group">
                        <label>{{__('db.Code')}} *</label>
                        <input type="text" name="code" required class="form-control">
                    </div>
                    <button type="submit" class="btn btn-primary" id="expenseCategorySubmitBtn">{{__('db.submit')}}</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Quick Add Warehouse Modal -->
<div id="quickAddWarehouseModal" tabindex="-1" role="dialog" aria-labelledby="quickAddWarehouseModalLabel" aria-hidden="true" class="modal fade text-left" data-backdrop="false" data-keyboard="false" style="z-index: 1060;">
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
                    <button type="submit" class="btn btn-primary" id="warehouseSubmitBtn">{{__('db.submit')}}</button>
                </form>
            </div>
        </div>
    </div>
</div>

