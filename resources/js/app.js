import './bootstrap';
import 'bootstrap';
import jquery from 'jquery';

window.$ = window.jQuery = jquery;

Promise.all([
    import('select2/dist/js/select2.full.js'),
    import('select2/dist/js/i18n/pt-BR.js'),
]).then(() => {
    initSelect2();
    initFilters();
});

function initSelect2() {
    $('select.select2').each(function () {
        loadSelect2($(this));
    });
}

function loadSelect2($select, customOptions) {
    const defaultOptions = {
        language: 'pt-BR',
        placeholder: $select.attr('placeholder') || 'Pesquise pelas opções disponíveis...',
        allowClear: !$select.attr('multiple') && $select.data('allow-clear') === 1,
    };

    if (!customOptions || !customOptions.ajax) {
        defaultOptions.minimumResultsForSearch = 5;
    }

    const options = Object.assign(defaultOptions, customOptions || {});

    $select
        .select2(options)
        .on('select2:unselecting', function () {
            $(this).data('unselecting', true);
        })
        .on('select2:opening', function (event) {
            if ($(this).data('unselecting')) {
                $(this).removeData('unselecting');
                event.preventDefault();
            }
        });

    if (!$select.attr('data-parsley-errors-container')) {
        const name = $select.attr('name');
        const errorContainerId = `${name}_errors`.replace('[]', '');
        const $div = $(`<div id="${errorContainerId}"></div>`);

        $select.attr('data-parsley-errors-container', `#${errorContainerId}`);
        $select.closest('.form-group').append($div);
    }

    const onSelectOrUnselect = () => {
        const data = $select.select2('data');
        $select.attr('data-selected-count', data ? data.length : 0);
    };

    $select.on('select2:select', onSelectOrUnselect);
    $select.on('select2:unselect', onSelectOrUnselect);
    onSelectOrUnselect();
}

function initFilters() {
    const $filters = $('#filters');

    initRangeFilters($filters);

    $filters.on('change', '.filter-chip input', function () {
        $(this).closest('.filter-chip').toggleClass('is-active', this.checked);
    });

    $filters.on('click', '#clear-filters-button', function () {
        $filters.find('input[type="checkbox"]').prop('checked', false).trigger('change');
        $filters.find('input[type="range"]').each(function () {
            const $input = $(this);
            $input.val($input.is('[data-range-min]') ? $input.attr('min') : $input.attr('max'));
        });
        initRangeFilters($filters);
        $filters.find('select').each(function () {
            $(this).val('').trigger('change');
        });
    });

    $.fn.getData = function () {
        const filters = {};

        $(this).find('input').each(function () {
            const $input = $(this);
            const name = $input.attr('name');
            const value = $input.val();

            if (value) {
                filters[name] = value;
            }
        });

        $(this).find('select').each(function () {
            const $select = $(this);
            filters[$select.attr('name')] = $select.val();
        });

        return filters;
    };

    $('#page-table').on('click', '#filters-button', function () {
        $(this).closest('form').trigger('submit');
    });
}

function initRangeFilters($root) {
    $root.find('[data-range-filter]').each(function () {
        const $range = $(this);
        const $min = $range.find('[data-range-min]');
        const $max = $range.find('[data-range-max]');
        const $minLabel = $range.find('[data-range-min-label]');
        const $maxLabel = $range.find('[data-range-max-label]');
        const $track = $range.find('.filter-range__track');
        const prefix = $range.data('prefix') || '';
        const suffix = $range.data('suffix') || '';

        const render = () => {
            let minValue = Number($min.val());
            let maxValue = Number($max.val());

            if (minValue > maxValue) {
                if (document.activeElement === $min[0]) {
                    maxValue = minValue;
                    $max.val(maxValue);
                } else {
                    minValue = maxValue;
                    $min.val(minValue);
                }
            }

            const min = Number($min.attr('min'));
            const max = Number($min.attr('max'));
            const left = ((minValue - min) / (max - min)) * 100;
            const right = 100 - (((maxValue - min) / (max - min)) * 100);

            $track.css({ '--range-left': `${left}%`, '--range-right': `${right}%` });
            $minLabel.text(`${prefix}${minValue}${suffix}`);
            $maxLabel.text(`${prefix}${maxValue}${suffix}`);
        };

        $min.off('input.rangeFilter change.rangeFilter').on('input.rangeFilter change.rangeFilter', render);
        $max.off('input.rangeFilter change.rangeFilter').on('input.rangeFilter change.rangeFilter', render);
        render();
    });
}

window.loadSelect2 = loadSelect2;
