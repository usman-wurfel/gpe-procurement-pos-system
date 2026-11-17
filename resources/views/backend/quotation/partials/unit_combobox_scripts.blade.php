var quotationQuickUnitUrl = @json(route('quotations.units.quick-store'));
var quotationSessionUnits = [];

function quotationNormalizeUnitName(value) {
    if (value === null || value === undefined) {
        return '';
    }

    if (typeof value === 'object') {
        if (value.unit_name) {
            return $.trim(String(value.unit_name));
        }
        return '';
    }

    return $.trim(String(value));
}

function quotationSanitizeUnitArray(values) {
    var clean = [];
    var seen = {};

    $.each(values || [], function(_, value) {
        value = quotationNormalizeUnitName(value);
        if (!value || value === 'n/a' || value === '[object Object]' || seen[value]) {
            return;
        }
        seen[value] = true;
        clean.push(value);
    });

    return clean;
}

function quotationSanitizeSystemUnits() {
    if (typeof allSystemUnits === 'undefined') {
        allSystemUnits = [];
        return;
    }

    allSystemUnits = quotationSanitizeUnitArray(allSystemUnits);
    quotationSessionUnits = quotationSanitizeUnitArray(quotationSessionUnits);
}

function quotationEscapeHtml(value) {
    return String(value || '').replace(/&/g, '&amp;').replace(/"/g, '&quot;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
}

function quotationDefaultUnit() {
    return (typeof allSystemUnits !== 'undefined' && allSystemUnits.length) ? allSystemUnits[0] : 'EA';
}

function quotationUnitExistsExact(unitName) {
    unitName = quotationNormalizeUnitName(unitName);
    if (!unitName) {
        return false;
    }

    var exists = false;
    $.each(quotationSanitizeUnitArray(allSystemUnits), function(_, value) {
        if (value === unitName) {
            exists = true;
            return false;
        }
    });

    return exists;
}

function quotationRegisterSystemUnit(unitName) {
    unitName = quotationNormalizeUnitName(unitName);
    if (!unitName || typeof allSystemUnits === 'undefined') {
        return;
    }

    if (!quotationUnitExistsExact(unitName)) {
        allSystemUnits.push(unitName);
        quotationSessionUnits.push(unitName);
    }
}

function quotationMergedUnits(extraValues, selectedValue) {
    var merged = [];
    var seen = {};

    var push = function(value) {
        value = quotationNormalizeUnitName(value);
        if (!value || value === 'n/a' || seen[value]) {
            return;
        }
        seen[value] = true;
        merged.push(value);
    };

    if (extraValues && extraValues.length) {
        $.each(extraValues, function(_, value) {
            push(value);
        });
    }

    push(selectedValue);

    $.each(quotationSanitizeUnitArray(allSystemUnits), function(_, value) {
        push(value);
    });

    if (!merged.length) {
        merged.push('EA');
    }

    return merged;
}

function quotationUnitCell($select) {
    var $cell = $select.closest('.quotation-unit-cell');
    return $cell.length ? $cell : $select.parent();
}

function quotationDestroyUnitSelect($select) {
    if (!$select || !$select.length) {
        return;
    }

    $select.removeData('quotation-unit-ready');
    $select.off('shown.bs.select.quotationUnit hidden.bs.select.quotationUnit changed.bs.select.quotationUnitGuard');

    try {
        if ($select.closest('.bootstrap-select').length) {
            $select.selectpicker('destroy');
        }
    } catch (e) {}

    quotationCleanupUnitCell($select);
}

function quotationCleanupUnitCell($select) {
    if (!$select || !$select.length) {
        return;
    }

    $select.removeClass('bs-select-hidden').css({ display: '', visibility: '' });

    var $cell = quotationUnitCell($select);
    if (!$cell.length) {
        return;
    }

    $cell.find('.bootstrap-select').each(function() {
        var $wrapper = $(this);
        var $innerSelect = $wrapper.find('select.sale-unit-select');
        if ($innerSelect.length && $innerSelect[0] === $select[0]) {
            $select.detach();
        }
        $wrapper.remove();
    });

    if (!$cell[0].contains($select[0])) {
        $cell.append($select);
    }
}

function quotationBuildUnitOptions($select, selectedValue, extraValues) {
    var selected = quotationNormalizeUnitName(selectedValue);
    var merged = quotationMergedUnits(extraValues, selected);
    if (!selected) {
        selected = merged[0];
    }
    var hasSelected = false;

    $select.empty();
    $.each(merged, function(_, label) {
        var isSelected = label === selected;
        if (isSelected) {
            hasSelected = true;
        }
        $select.append(new Option(label, label, false, isSelected));
    });

    if (!hasSelected && selected) {
        $select.append(new Option(selected, selected, true, true));
    }
}

function quotationEnsureUnitOptionExact($select, value) {
    value = quotationNormalizeUnitName(value);
    if (!value) {
        return false;
    }

    var exists = false;
    $select.find('option').each(function() {
        if ($.trim($(this).val()) === value) {
            exists = true;
            return false;
        }
    });

    if (!exists) {
        $select.append(new Option(value, value, false, false));
    }

    return true;
}

function quotationSelectHasExactOption($select, term) {
    term = quotationNormalizeUnitName(term);
    if (!term || !$select || !$select.length) {
        return false;
    }

    var found = false;
    $select.find('option').each(function() {
        if ($.trim($(this).val()) === term) {
            found = true;
            return false;
        }
    });

    return found;
}

function quotationUpdateRowUnitName($select) {
    var unitValue = quotationGetSelectUnitValue($select);
    var $row = $select.closest('tr');
    if ($row.length) {
        var changedRowIndex = $row.index();
        if (typeof unit_name !== 'undefined' && unit_name[changedRowIndex] !== undefined) {
            unit_name[changedRowIndex] = unitValue + ',';
        }
    }
}

function quotationEnforceSearchTerm($select) {
    if (!$select || !$select.length || $select.data('quotation-applying')) {
        return;
    }

    var $picker = $select.closest('.bootstrap-select');
    if (!$picker.length) {
        return;
    }

    var searchTerm = quotationNormalizeUnitName($picker.find('.bs-searchbox input').val());
    if (!searchTerm) {
        return;
    }

    var selected = quotationGetSelectUnitValue($select);
    if (selected === searchTerm) {
        $picker.find('.bs-searchbox input').val('');
        return;
    }

    $select.data('quotation-applying', true);

    if (quotationSelectHasExactOption($select, searchTerm) || quotationUnitExistsExact(searchTerm)) {
        quotationApplyUnitSelection($select, searchTerm);
    } else {
        $picker.find('.bs-searchbox input').val('');
    }

    $select.data('quotation-applying', false);
}

function quotationForceCloseUnitSelect($select) {
    if (!$select || !$select.length) {
        return;
    }

    var $picker = $select.closest('.bootstrap-select');
    if (!$picker.length) {
        return;
    }

    $picker.find('.bs-searchbox input').val('');
    if ($picker.hasClass('open') || $picker.hasClass('show')) {
        $select.selectpicker('toggle');
    }
}

function quotationApplyUnitSelection($select, term) {
    term = quotationNormalizeUnitName(term);
    if (!term || !$select || !$select.length) {
        return;
    }

    $select.data('quotation-applying', true);
    quotationForceCloseUnitSelect($select);
    quotationRegisterSystemUnit(term);

    var extras = quotationGetRowUnitExtras($select.closest('tr'));
    quotationInitUnitSelect($select, term, extras);
    quotationUpdateRowUnitName($select);
    $select.data('quotation-applying', false);
}

function quotationGetRowUnitExtras($row) {
    if (!$row || !$row.length) {
        return [];
    }

    var catalog = $row.find('.sale-unit-catalog').val() || '';
    return catalog.replace(/,$/, '').split(',').map(function(value) {
        return $.trim(value);
    }).filter(function(value) {
        return value && value !== 'n/a';
    });
}

function quotationSyncUnitSelectOptions($select) {
    if (!$select || !$select.length) {
        return;
    }

    var $row = $select.closest('tr');
    var extras = quotationGetRowUnitExtras($row);
    var current = quotationGetSelectUnitValue($select) || quotationDefaultUnit();

    quotationInitUnitSelect($select, current, extras);
}

function quotationAddUnitToAllSelects(unitName, skipSelect) {
    unitName = quotationNormalizeUnitName(unitName);
    if (!unitName) {
        return;
    }

    quotationRegisterSystemUnit(unitName);

    $('table.order-list tbody .sale-unit-select, select[name="edit_unit"]').each(function() {
        var $select = $(this);
        if (skipSelect && $select[0] === skipSelect[0]) {
            return;
        }
        if (!$select.data('quotation-unit-ready')) {
            return;
        }

        var current = quotationGetSelectUnitValue($select);
        quotationEnsureUnitOptionExact($select, unitName);

        var $picker = $select.closest('.bootstrap-select');
        if ($picker.length && ($picker.hasClass('open') || $picker.hasClass('show'))) {
            return;
        }

        $select.selectpicker('refresh');
        if (current) {
            $select.selectpicker('val', current);
        }
    });
}

function quotationCreateUnitOnServer(unitName, callback, activeSelect) {
    unitName = quotationNormalizeUnitName(unitName);
    if (!unitName) {
        callback(false);
        return;
    }

    if (quotationUnitExistsExact(unitName)) {
        quotationRegisterSystemUnit(unitName);
        quotationAddUnitToAllSelects(unitName, activeSelect);
        callback(true, unitName);
        return;
    }

    $.ajax({
        url: quotationQuickUnitUrl,
        method: 'POST',
        data: { unit_name: unitName },
        success: function(res) {
            if (res.success && res.unit) {
                var createdName = quotationNormalizeUnitName(res.unit.unit_name || res.unit);
                if (!createdName) {
                    callback(false);
                    return;
                }
                quotationRegisterSystemUnit(createdName);
                quotationAddUnitToAllSelects(createdName, activeSelect);
                callback(true, createdName);
                return;
            }
            callback(false);
        },
        error: function(xhr) {
            var msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Could not create unit.';
            alert(msg);
            callback(false);
        }
    });
}

function quotationCreateUnitFromSearch($select, term) {
    term = quotationNormalizeUnitName(term);
    if (!term || !$select || !$select.length) {
        return;
    }

    quotationCreateUnitOnServer(term, function(ok, unitName) {
        if (ok) {
            quotationApplyUnitSelection($select, unitName || term);
        }
    }, $select);
}

function quotationBindUnitSearchEnter($select) {
    $select.off('shown.bs.select.quotationUnit hidden.bs.select.quotationUnit changed.bs.select.quotationUnitGuard');

    $select.on('shown.bs.select.quotationUnit', function() {
        var $picker = $select.closest('.bootstrap-select');
        var $menu = $picker.find('.dropdown-menu');
        var $search = $menu.find('.bs-searchbox input');

        $picker.data('quotation-user-clicked', false);
        $picker.data('quotation-search-touched', false);

        $search.off('input.quotationUnitTouch').on('input.quotationUnitTouch', function() {
            $picker.data('quotation-search-touched', true);
        });

        $menu.off('mousedown.quotationUnitClick').on('mousedown.quotationUnitClick', 'li:not(.disabled):not(.bs-searchbox):not(.no-results) a', function() {
            $picker.data('quotation-user-clicked', true);
        });

        $search.off('keydown.quotationUnit').on('keydown.quotationUnit', function(e) {
            if (e.which !== 13) {
                return;
            }

            e.preventDefault();
            e.stopPropagation();
            e.stopImmediatePropagation();

            $picker.data('quotation-user-clicked', false);
            quotationCreateUnitFromSearch($select, $(this).val());
            return false;
        });

        $picker.off('keydown.quotationUnitMenu').on('keydown.quotationUnitMenu', '.dropdown-menu', function(e) {
            if (e.which !== 13) {
                return;
            }

            e.preventDefault();
            e.stopPropagation();
            e.stopImmediatePropagation();

            $picker.data('quotation-user-clicked', false);
            quotationCreateUnitFromSearch($select, $search.val());
            return false;
        });

        $picker.off('click.quotationUnitCreate').on('click.quotationUnitCreate', '.no-results', function(e) {
            e.preventDefault();
            e.stopPropagation();
            $picker.data('quotation-user-clicked', false);
            quotationCreateUnitFromSearch($select, $search.val());
        });
    });

    $select.on('changed.bs.select.quotationUnitGuard', function() {
        if ($select.data('quotation-applying')) {
            return;
        }

        var $picker = $select.closest('.bootstrap-select');
        if ($picker.data('quotation-user-clicked')) {
            $picker.data('quotation-user-clicked', false);
        }

        quotationUpdateRowUnitName($select);
    });

    $select.on('hidden.bs.select.quotationUnit', function() {
        if ($select.data('quotation-applying')) {
            return;
        }

        var $picker = $select.closest('.bootstrap-select');
        if ($picker.data('quotation-user-clicked')) {
            $picker.data('quotation-user-clicked', false);
            return;
        }

        if ($picker.data('quotation-search-touched')) {
            quotationEnforceSearchTerm($select);
            $picker.data('quotation-search-touched', false);
        }

        $picker.find('.bs-searchbox input').val('');
    });
}

function quotationPrepareUnitSelect($select) {
    quotationDestroyUnitSelect($select);
}

function quotationInitUnitSelect($select, selectedValue, extraValues) {
    if (!$select || !$select.length || typeof $.fn.selectpicker !== 'function') {
        return;
    }

    quotationPrepareUnitSelect($select);
    quotationBuildUnitOptions($select, selectedValue, extraValues);

    $select.removeClass('selectpicker');
    $select.removeAttr('size');
    $select.attr({
        'data-live-search': 'true',
        'data-size': '8',
        'title': 'Select unit'
    });

    $select.selectpicker({
        liveSearch: true,
        liveSearchNormalize: false,
        size: 8,
        width: '100%',
        style: 'btn-default',
        noneResultsText: 'Create "{0}" as new unit (press Enter)'
    });

    var value = $.trim(selectedValue || '') || quotationDefaultUnit();
    quotationEnsureUnitOptionExact($select, value);
    $select.find('option').each(function() {
        this.selected = ($.trim($(this).val()) === value);
    });
    $select.selectpicker('refresh');
    $select.selectpicker('val', value);
    quotationBindUnitSearchEnter($select);
    $select.data('quotation-unit-ready', true);
}

function quotationGetSelectUnitValue($select) {
    if (!$select || !$select.length) {
        return '';
    }

    var value = '';

    if ($select.closest('.bootstrap-select').length) {
        value = $select.selectpicker('val');
        if ($.isArray(value)) {
            value = value[0] || '';
        }
    } else {
        value = $select.val() || '';
    }

    return quotationNormalizeUnitName(value);
}

function quotationSetUnitValue($select, value) {
    value = quotationNormalizeUnitName(value);
    if (!value || !$select || !$select.length) {
        return;
    }

    quotationApplyUnitSelection($select, value);
}

function quotationBuildUnitFieldHtml(selectedValue, extraValues) {
    var selected = quotationNormalizeUnitName(selectedValue);
    var merged = quotationMergedUnits(extraValues, selected);
    if (!selected) {
        selected = merged[0];
    }
    var optionsHtml = '';
    var hasSelected = false;

    $.each(merged, function(_, label) {
        var selectedAttr = label === selected ? ' selected' : '';
        if (label === selected) {
            hasSelected = true;
        }
        optionsHtml += '<option value="' + quotationEscapeHtml(label) + '"' + selectedAttr + '>' + quotationEscapeHtml(label) + '</option>';
    });

    if (!hasSelected && selected) {
        optionsHtml += '<option value="' + quotationEscapeHtml(selected) + '" selected>' + quotationEscapeHtml(selected) + '</option>';
    }

    return '<select class="form-control sale-unit-select" name="sale_unit[]" data-live-search="true" data-size="8" title="Select unit">' + optionsHtml + '</select>';
}

function quotationInitRowUnitSelect($row, selectedValue, extraValues) {
    var $select = $row.find('.sale-unit-select');
    quotationInitUnitSelect($select, selectedValue, extraValues);
}

function quotationGetRowUnit($row) {
    return quotationGetSelectUnitValue($row.find('.sale-unit-select'));
}

function quotationPopulateEditUnitFields(unitList, currentUnit) {
    quotationInitUnitSelect($('select[name="edit_unit"]'), currentUnit, unitList);
}

function quotationInitExistingRowUnits() {
    $('table.order-list tbody tr').each(function() {
        var $row = $(this);
        var $select = $row.find('.sale-unit-select');
        if (!$select.length) {
            return;
        }

        var extras = quotationGetRowUnitExtras($row);
        var selected = quotationGetSelectUnitValue($select) || $.trim($select.val()) || quotationDefaultUnit();
        quotationInitRowUnitSelect($row, selected, extras);
    });

    var $editUnit = $('select[name="edit_unit"]');
    if ($editUnit.length) {
        quotationInitUnitSelect($editUnit, quotationGetSelectUnitValue($editUnit) || quotationDefaultUnit(), []);
    }
}

function quotationBindDelegatedUnitHandlers() {
    $(document).off('keydown.quotationUnitDelegated', 'table.order-list .quotation-unit-cell .bootstrap-select .bs-searchbox input');
    $(document).on('keydown.quotationUnitDelegated', 'table.order-list .quotation-unit-cell .bootstrap-select .bs-searchbox input', function(e) {
        if (e.which !== 13) {
            return;
        }

        e.preventDefault();
        e.stopPropagation();

        var $select = $(this).closest('.bootstrap-select').find('select.sale-unit-select');
        if (!$select.length || !$select.data('quotation-unit-ready')) {
            return;
        }

        $(this).closest('.bootstrap-select').data('quotation-user-clicked', false);
        quotationCreateUnitFromSearch($select, $(this).val());
    });

    $(document).off('click.quotationUnitDelegated', 'table.order-list .quotation-unit-cell .bootstrap-select .no-results');
    $(document).on('click.quotationUnitDelegated', 'table.order-list .quotation-unit-cell .bootstrap-select .no-results', function(e) {
        e.preventDefault();
        e.stopPropagation();

        var $picker = $(this).closest('.bootstrap-select');
        var $select = $picker.find('select.sale-unit-select');
        if (!$select.length || !$select.data('quotation-unit-ready')) {
            return;
        }

        $picker.data('quotation-user-clicked', false);
        quotationCreateUnitFromSearch($select, $picker.find('.bs-searchbox input').val());
    });
}

function quotationBindUnitHandlers() {
    quotationBindDelegatedUnitHandlers();

    $('#quotation-form').off('submit.quotationUnits').on('submit.quotationUnits', function() {
        $('table.order-list tbody tr').each(function() {
            var $row = $(this);
            var unitValue = quotationGetRowUnit($row);
            var changedRowIndex = $row.index();
            if (typeof unit_name !== 'undefined' && unit_name[changedRowIndex] !== undefined && unitValue) {
                unit_name[changedRowIndex] = unitValue + ',';
            }
        });
    });

    $('table.order-list tbody').off('click.quotationUnitDestroy').on('click.quotationUnitDestroy', '.ibtnDel', function() {
        quotationDestroyUnitSelect($(this).closest('tr').find('.sale-unit-select'));
    });
}

quotationBindUnitHandlers();

$(document).ready(function() {
    quotationSanitizeSystemUnits();

    setTimeout(function() {
        quotationSanitizeSystemUnits();
        quotationInitExistingRowUnits();
    }, 0);
});
