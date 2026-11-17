// Generic showAlert function for Bootstrap alerts
function showAlert(message, type) {
    var alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
    var alertHtml = '<div class="alert ' + alertClass + ' alert-dismissible fade show expense-quick-alert" role="alert">' +
        message +
        '<button type="button" class="close" data-dismiss="alert" aria-label="Close">' +
        '<span aria-hidden="true">&times;</span>' +
        '</button>' +
        '</div>';
    
    // Remove existing alerts
    $('.expense-quick-alert').remove();
    
    // Prepend alert to the correct modal
    if($('#quickAddExpenseCategoryModal').hasClass('show')) {
        $('#quickAddExpenseCategoryModal .modal-body').prepend(alertHtml);
    } else if($('#quickAddWarehouseModal').hasClass('show')) {
        $('#quickAddWarehouseModal .modal-body').prepend(alertHtml);
    } else if($('#expense-modal').hasClass('show') || $('#expense-modal').hasClass('temp-hidden')) {
        $('#expense-modal .modal-body').prepend(alertHtml);
    } else if($('#editModal').hasClass('show') || $('#editModal').hasClass('temp-hidden')) {
        $('#editModal .modal-body').prepend(alertHtml);
    } else {
        $('body').prepend(alertHtml);
    }
    
    // Auto dismiss after 5 seconds
    setTimeout(function() {
        $('.expense-quick-alert').fadeOut(function() {
            $(this).remove();
        });
    }, 5000);
}

// Modal stacking: Hide parent modal and other quick add modals when one opens
$('#quickAddExpenseCategoryModal').on('show.bs.modal', function() {
    $('#quickAddWarehouseModal').modal('hide');
    // Temporarily hide parent modals
    if($('#expense-modal').hasClass('show')) {
        $('#expense-modal').addClass('temp-hidden');
    }
    if($('#editModal').hasClass('show')) {
        $('#editModal').addClass('temp-hidden');
    }
});

$('#quickAddWarehouseModal').on('show.bs.modal', function() {
    $('#quickAddExpenseCategoryModal').modal('hide');
    // Temporarily hide parent modals
    if($('#expense-modal').hasClass('show')) {
        $('#expense-modal').addClass('temp-hidden');
    }
    if($('#editModal').hasClass('show')) {
        $('#editModal').addClass('temp-hidden');
    }
});

// Restore parent modals when quick add modals close
$('#quickAddExpenseCategoryModal, #quickAddWarehouseModal').on('hidden.bs.modal', function() {
    // Show parent modals back
    if($('#expense-modal').hasClass('temp-hidden')) {
        $('#expense-modal').removeClass('temp-hidden');
    }
    if($('#editModal').hasClass('temp-hidden')) {
        $('#editModal').removeClass('temp-hidden');
    }
    
    // Safety: Always restore buttons when modal closes
    $('#expenseCategorySubmitBtn').prop('disabled', false).html('Submit');
    $('#warehouseSubmitBtn').prop('disabled', false).html('Submit');
});

// Global safety: Check and restore buttons every second when modals are open
setInterval(function() {
    if($('#quickAddExpenseCategoryModal').hasClass('show')) {
        var $btn = $('#expenseCategorySubmitBtn');
        if($btn.length && $btn.prop('disabled')) {
            $btn.prop('disabled', false).html('Submit');
        }
    }
    if($('#quickAddWarehouseModal').hasClass('show')) {
        var $btn = $('#warehouseSubmitBtn');
        if($btn.length && $btn.prop('disabled')) {
            $btn.prop('disabled', false).html('Submit');
        }
    }
}, 1000);

// Quick Add Expense Category
$(document).on('submit', '#quickAddExpenseCategoryForm', function(e) {
    e.preventDefault();
    e.stopPropagation();
    
    var $form = $(this);
    var $submitBtn = $('#expenseCategorySubmitBtn');
    
    // Disable button and show loading
    $submitBtn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Processing...');
    
    // Function to restore button - SIMPLE and DIRECT
    var restoreButton = function() {
        $('#expenseCategorySubmitBtn').prop('disabled', false).html('Submit');
    };
    
    $.ajax({
        url: '{{route("expense_category.quickAdd")}}',
        method: 'POST',
        data: $form.serialize(),
        timeout: 30000,
        dataType: 'json',
        success: function(response) {
            if(response && response.success) {
                var newOption = new Option(response.data.name + ' (' + response.data.code + ')', response.data.id, true, true);
                $('#expense_category_id, #edit_expense_category_id').append(newOption).trigger('change');
                $('#expense_category_id, #edit_expense_category_id').selectpicker('refresh');
                $('#quickAddExpenseCategoryModal').modal('hide');
                $('#quickAddExpenseCategoryForm')[0].reset();
                showAlert('Expense Category added successfully!', 'success');
            }
            // Always restore button on success
            restoreButton();
        },
        error: function(xhr, status, error) {
            // IMMEDIATELY restore button
            restoreButton();
            
            var errorMsg = 'Error adding expense category';
            if(xhr && xhr.responseJSON) {
                if(xhr.responseJSON.message) {
                    errorMsg = xhr.responseJSON.message;
                } else if(xhr.responseJSON.errors) {
                    var errors = xhr.responseJSON.errors;
                    errorMsg = '';
                    $.each(errors, function(key, value) {
                        if(errorMsg) errorMsg += '<br>';
                        errorMsg += value[0];
                    });
                }
            }
            showAlert(errorMsg, 'error');
        },
        complete: function() {
            // Final safety - ALWAYS restore button
            restoreButton();
            setTimeout(restoreButton, 50);
            setTimeout(restoreButton, 200);
        }
    });
    
    return false;
});

// Quick Add Warehouse
$(document).on('submit', '#quickAddWarehouseForm', function(e) {
    e.preventDefault();
    e.stopPropagation();
    
    var $form = $(this);
    var $submitBtn = $('#warehouseSubmitBtn');
    
    // Disable button and show loading
    $submitBtn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Processing...');
    
    // Function to restore button - SIMPLE and DIRECT
    var restoreButton = function() {
        $('#warehouseSubmitBtn').prop('disabled', false).html('Submit');
    };
    
    $.ajax({
        url: '{{route("warehouse.quickAdd")}}',
        method: 'POST',
        data: $form.serialize(),
        timeout: 30000,
        dataType: 'json',
            success: function(response) {
                if(response && response.success) {
                    var newOption = new Option(response.data.name, response.data.id, true, true);
                    // Handle all warehouse selects (expense, delivery_order, invoice)
                    $('#expense_warehouse_id, #edit_warehouse_id, #warehouse_id').append(newOption).val(response.data.id).trigger('change');
                    $('#expense_warehouse_id, #edit_warehouse_id, #warehouse_id').selectpicker('refresh');
                    $('#quickAddWarehouseModal').modal('hide');
                    $('#quickAddWarehouseForm')[0].reset();
                    showAlert('Warehouse added successfully!', 'success');
                }
                restoreButton();
            },
        error: function(xhr, status, error) {
            // IMMEDIATELY restore button
            restoreButton();
            
            var errorMsg = 'Error adding warehouse';
            if(xhr && xhr.responseJSON) {
                if(xhr.responseJSON.message) {
                    errorMsg = xhr.responseJSON.message;
                } else if(xhr.responseJSON.errors) {
                    var errors = xhr.responseJSON.errors;
                    errorMsg = '';
                    $.each(errors, function(key, value) {
                        if(errorMsg) errorMsg += '<br>';
                        errorMsg += value[0];
                    });
                }
            }
            showAlert(errorMsg, 'error');
        },
        complete: function() {
            // Final safety - ALWAYS restore button
            restoreButton();
            setTimeout(restoreButton, 50);
            setTimeout(restoreButton, 200);
        }
    });
    
    return false;
});
