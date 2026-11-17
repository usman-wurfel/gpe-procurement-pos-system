// Function to show Bootstrap alert
function showAlert(message, type) {
    type = type || 'success';
    var alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
    var alertHtml = '<div class="alert ' + alertClass + ' alert-dismissible fade show" role="alert">' +
        '<strong>' + message + '</strong>' +
        '<button type="button" class="close" data-dismiss="alert" aria-label="Close">' +
        '<span aria-hidden="true">&times;</span>' +
        '</button>' +
        '</div>';
    
    // Remove existing alerts
    $('.quick-add-alert').remove();
    
    // Find the currently open modal and prepend alert to it
    var $targetModal = null;
    if($('#quickAddBillerModal').hasClass('show')) {
        $targetModal = $('#quickAddBillerModal .modal-body');
    } else if($('#quickAddSupplierModal').hasClass('show')) {
        $targetModal = $('#quickAddSupplierModal .modal-body');
    } else if($('#quickAddCustomerModal').hasClass('show')) {
        $targetModal = $('#quickAddCustomerModal .modal-body');
    } else if($('#quickAddWarehouseModal').hasClass('show')) {
        $targetModal = $('#quickAddWarehouseModal .modal-body');
    } else {
        // If no modal is open, prepend to all modals as fallback
        $targetModal = $('#quickAddBillerModal .modal-body, #quickAddSupplierModal .modal-body, #quickAddCustomerModal .modal-body, #quickAddWarehouseModal .modal-body');
    }
    
    // Add alert before modal body
    var $alert = $(alertHtml).addClass('quick-add-alert');
    $targetModal.prepend($alert);
    
    // Auto dismiss after 5 seconds
    setTimeout(function() {
        $alert.fadeOut(function() {
            $(this).remove();
        });
    }, 5000);
}

// Clear alerts when modals are closed
$('#quickAddBillerModal, #quickAddSupplierModal, #quickAddCustomerModal, #quickAddWarehouseModal').on('hidden.bs.modal', function() {
    $('.quick-add-alert').remove();
    $(this).find('form')[0].reset();
});

// Quick Add Biller
$('#quickAddBillerForm').on('submit', function(e) {
    e.preventDefault();
    var $form = $(this);
    var $submitBtn = $form.find('button[type="submit"]');
    var originalText = $submitBtn.html();
    
    // Disable button and show loading
    $submitBtn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Processing...');
    
    $.ajax({
        url: '{{route("biller.quickAdd")}}',
        method: 'POST',
        data: $form.serialize(),
        success: function(response) {
            if(response.success) {
                var newOption = new Option(response.data.name + ' (' + response.data.company_name + ')', response.data.id, true, true);
                // Support both #biller-id and #biller_id
                var $billerSelect = $('#biller-id, #biller_id');
                if($billerSelect.length > 0) {
                    $billerSelect.append(newOption);
                    $billerSelect.val(response.data.id);
                    $billerSelect.selectpicker('refresh');
                }
                $('#quickAddBillerModal').modal('hide');
                $('#quickAddBillerForm')[0].reset();
                showAlert('Biller added successfully!', 'success');
                // Re-enable button after success
                $submitBtn.prop('disabled', false).html(originalText);
            }
        },
        error: function(xhr) {
            var errorMsg = 'Error adding biller';
            if(xhr.responseJSON) {
                if(xhr.responseJSON.message) {
                    errorMsg = xhr.responseJSON.message;
                } else if(xhr.responseJSON.errors) {
                    var errors = xhr.responseJSON.errors;
                    errorMsg = '';
                    $.each(errors, function(key, value) {
                        errorMsg += value[0] + '<br>';
                    });
                }
            }
            showAlert(errorMsg, 'error');
            // Re-enable button on error
            $submitBtn.prop('disabled', false).html(originalText);
        },
        complete: function() {
            // Ensure button is re-enabled even if something goes wrong
            if($submitBtn.prop('disabled')) {
                $submitBtn.prop('disabled', false).html(originalText);
            }
        }
    });
});

// Quick Add Supplier
$('#quickAddSupplierForm').on('submit', function(e) {
    e.preventDefault();
    var $form = $(this);
    var $submitBtn = $form.find('button[type="submit"]');
    var originalText = $submitBtn.html();
    
    // Disable button and show loading
    $submitBtn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Processing...');
    
    $.ajax({
        url: '{{route("supplier.quickAdd")}}',
        method: 'POST',
        data: $form.serialize(),
        success: function(response) {
            if(response.success) {
                var newOption = new Option(response.data.name + ' (' + response.data.company_name + ')', response.data.id, true, true);
                $('#supplier-id').append(newOption).trigger('change');
                $('#supplier-id').selectpicker('refresh');
                $('#quickAddSupplierModal').modal('hide');
                $('#quickAddSupplierForm')[0].reset();
                showAlert('Supplier added successfully!', 'success');
                // Re-enable button after success
                $submitBtn.prop('disabled', false).html(originalText);
            }
        },
        error: function(xhr) {
            var errorMsg = 'Error adding supplier';
            if(xhr.responseJSON) {
                if(xhr.responseJSON.message) {
                    errorMsg = xhr.responseJSON.message;
                } else if(xhr.responseJSON.errors) {
                    var errors = xhr.responseJSON.errors;
                    errorMsg = '';
                    $.each(errors, function(key, value) {
                        errorMsg += value[0] + '<br>';
                    });
                }
            }
            showAlert(errorMsg, 'error');
            // Re-enable button on error
            $submitBtn.prop('disabled', false).html(originalText);
        },
        complete: function() {
            // Ensure button is re-enabled even if something goes wrong
            if($submitBtn.prop('disabled')) {
                $submitBtn.prop('disabled', false).html(originalText);
            }
        }
    });
});

// Quick Add Customer
$('#quickAddCustomerForm').on('submit', function(e) {
    e.preventDefault();
    var $form = $(this);
    var $submitBtn = $form.find('button[type="submit"]');
    var originalText = $submitBtn.html();
    
    // Disable button and show loading
    $submitBtn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Processing...');
    
    $.ajax({
        url: '{{route("customer.quickAdd")}}',
        method: 'POST',
        data: $form.serialize(),
        success: function(response) {
            if(response.success) {
                var newOption = new Option(response.data.name + ' (' + response.data.company_name + ')', response.data.id, true, true);
                var $customerSelect = $('#customer_id');
                if($customerSelect.length > 0) {
                    $customerSelect.append(newOption);
                    $customerSelect.val(response.data.id);
                    $customerSelect.selectpicker('refresh');
                    // Ensure dependent customer logic (group rate, pricing, etc.) runs immediately.
                    $customerSelect.trigger('change');
                    $customerSelect.trigger('changed.bs.select');
                }
                $('#quickAddCustomerModal').modal('hide');
                $('#quickAddCustomerForm')[0].reset();
                showAlert('Customer added successfully!', 'success');
                // Re-enable button after success
                $submitBtn.prop('disabled', false).html(originalText);
            }
        },
        error: function(xhr) {
            var errorMsg = 'Error adding customer';
            if(xhr.responseJSON) {
                if(xhr.responseJSON.message) {
                    errorMsg = xhr.responseJSON.message;
                } else if(xhr.responseJSON.errors) {
                    var errors = xhr.responseJSON.errors;
                    errorMsg = '';
                    $.each(errors, function(key, value) {
                        errorMsg += value[0] + '<br>';
                    });
                }
            }
            showAlert(errorMsg, 'error');
            // Re-enable button on error
            $submitBtn.prop('disabled', false).html(originalText);
        },
        complete: function() {
            // Ensure button is re-enabled even if something goes wrong
            if($submitBtn.prop('disabled')) {
                $submitBtn.prop('disabled', false).html(originalText);
            }
        }
    });
});

// Quick Add Warehouse is handled globally by expense partials (main.blade.php)

// Hide header, navbar, sidebar and footer in product iframe - keep form visible
$('#quickAddProductModal').on('shown.bs.modal', function() {
    setTimeout(function() {
        try {
            var iframe = document.getElementById('productIframe');
            if(iframe && iframe.contentDocument) {
                var iframeDoc = iframe.contentDocument || iframe.contentWindow.document;
                
                // Hide sidebar
                var sideNavbar = iframeDoc.querySelector('.side-navbar');
                if(sideNavbar) {
                    sideNavbar.style.display = 'none';
                }
                
                // Hide header/navbar
                var header = iframeDoc.querySelector('header.container-fluid');
                if(header) {
                    header.style.display = 'none';
                }
                
                // Hide footer
                var footer = iframeDoc.querySelector('.main-footer');
                if(footer) {
                    footer.style.display = 'none';
                }
                
                // Adjust page layout to show form properly
                var page = iframeDoc.querySelector('.page');
                if(page) {
                    page.style.marginLeft = '0';
                    page.style.width = '100%';
                }
                
                // Show content (form)
                var content = iframeDoc.querySelector('#content');
                if(content) {
                    content.style.display = 'block';
                }
            }
        } catch(e) {
            console.log('Iframe access issue:', e);
        }
    }, 500);
});

// Product iframe message listener
window.addEventListener('message', function(event) {
    if(event.data && event.data.type === 'productAdded') {
        // Hide modal
        $('#quickAddProductModal').modal('hide');
        
        var warehouse_id = $('#warehouse_id').val();
        var customer_id = $('#customer_id').val();
        var productCode = event.data.product_code || null;
        
        if(warehouse_id && customer_id && productCode) {
            // Show loading message
            showAlert('Product added! Loading...', 'success');
            
            // First refresh product list, then auto-search
            $.get('getproduct/' + warehouse_id, function(data) {
                lims_product_array = [];
                product_code = data[0];
                product_name = data[1];
                product_qty = data[2];
                product_type = data[3];
                product_id = data[4];
                product_list = data[5];
                qty_list = data[6];
                product_warehouse_price = data[7];
                expired_date = data[10];
                is_embeded = data[11];
                imei_number = data[12];
                $.each(product_code, function(index) {
                    lims_product_array.push(product_code[index]+'|'+product_name[index]+'|'+imei_number[index]+'|'+is_embeded[index]);
                });
                
                // Find the product in the array and call productSearch directly
                var found = false;
                $.each(lims_product_array, function(index, item) {
                    if(item.indexOf(productCode) === 0) {
                        // Call productSearch function directly (same as autocomplete select)
                        if(typeof productSearch === 'function') {
                            productSearch(item);
                            showAlert('Product added to order table!', 'success');
                        }
                        found = true;
                        return false; // break loop
                    }
                });
                
                if(!found) {
                    showAlert('Product created! Search for "' + productCode + '" to add it.', 'success');
                }
            });
        } else if(productCode) {
            showAlert('Product created! Code: ' + productCode + '. Select Customer & Warehouse first, then search.', 'success');
        } else {
            showAlert('Product created successfully!', 'success');
        }
    }
});
