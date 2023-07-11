'use strict';

$(function() {

    $('select.select2').each(function() {
        loadSelect2($(this));
    });

});

/**
 * Carregar select2
 * @param {*} $select 
 * @param {*} customOptions 
 */
function loadSelect2($select, customOptions) {

    var placeholder = 'Pesquise pelas opções disponíveis...';
    
    if ($select.attr('placeholder')) {
        placeholder = $select.attr('placeholder');
    }

    var defaultOptions = {
        language: 'pt-BR',
        placeholder: placeholder,
        allowClear: !$select.attr('multiple') && $select.data('allow-clear') == 1,
    };

    if (!customOptions || !customOptions.ajax) {
        defaultOptions.minimumResultsForSearch = 5;
    }

    if (customOptions) {
        var options = Object.assign(defaultOptions, customOptions);

    } else {
        var options = defaultOptions;
    }

    $select
        .select2(options)
        .on('select2:unselecting', function() {
            $(this).data('unselecting', true);
        }).on('select2:opening', function(e) {
            if ($(this).data('unselecting')) {
                $(this).removeData('unselecting');
                e.preventDefault();
            }
        });

    if (!$select.attr('data-parsley-errors-container')) {

        var name = $select.attr('name');
        var errorContainerId = `${name}_errors`.replace('[]', '');

        var $div = $(`<div id="${ errorContainerId }"></div>`);

        $select.attr('data-parsley-errors-container', '#' + errorContainerId);

        // Adicionar container de erros no form group
        $select.closest('.form-group').append($div);
    }

    function onSelectOrUnselect() {

        var data = $select.select2('data');

        $select.attr('data-selected-count', data ? data.length : 0);
    }

    $select.on('select2:select', onSelectOrUnselect);
    $select.on('select2:unselect', onSelectOrUnselect);
    onSelectOrUnselect();

    $select.one('select2:open', function(e) {
        $('input.select2-search__field').prop('placeholder', 'Pesquise dentre as opções disponíveis...');
    });
}

function destroySelect2($select) {

    var $parent = $select.parent();

    $parent.find('.select2').remove();

    $select.removeClass('select2-select select2-hidden-accessible');
    $select.removeAttr('data-select2-id');
    $select.find('option').removeAttr('data-select2-id');
}

function reloadSelect2($select, keepValue) {

    destroySelect2($select);

    loadSelect2($select);

    if (!keepValue) {
        $select.val('');
    }

    $select.trigger('change.select2');
}

/**
 * Carregar select2 (modo pesquisa por ajax)
 * @param {*} $select 
 * @param {*} url 
 * @param {*} processResults 
 * @param {*} customOptions 
 * @param {*} customOptions 
 */
function loadSelect2Ajax($select, url, processResults, customOptions, customParentOptions) {

    var options = {
        url: url,
        delay: 250,
        cache: true,
        data: function(params) {
            
            var data = {
                search: params.term,
                page: params.page || 1
            };

            if (customOptions && customOptions.customSearchQuery) {
                Object.assign(data, customOptions.customSearchQuery());
            }

            return data;
        }
    }

    options.processResults = processResults || function(results) {
        var data = {
            results: results.data,
            pagination: {
                more: results.total > 0 && results.to != results.total
            }
        };
        return data;
    }

    if (customOptions) {
        options = Object.assign(options, customOptions);
    }

    var parentOptions = { ajax: options };

    if (customParentOptions) {
        Object.assign(parentOptions, customParentOptions);
    }

    loadSelect2($select, parentOptions);
}