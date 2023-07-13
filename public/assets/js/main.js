$(function() {
    
    var $chartsRow = $('#charts-row');

    var $group = $chartsRow.find('> div:first')
        .detach();

    var $modal = $('#chart-modal');

    const colors = new Gradient()
        //.setColorGradient('#318CE7', '#fd5c63')
        .setColorGradient('#000000', '#eeeeee')
        .setMidpoint(13)
        .getColors();

    function redirectToTable(filters) {

        var query = new URLSearchParams(filters).toString();
    
        window.open('table?' + query, '_blank').focus();
    }
        
    function createChart($canvas, dataset) {
            
        var onChartClick = function(event, elements) {

            if (!elements || elements.length == 0) return;
        
            var filters = getFilters();
    
            filters.year_max = null;
            filters.year_min = null;
    
            for (let row of elements) {
    
                var data = row.element.$context.raw.data;
        
                filters['models[]'] = [data.brand + ' ' + data.model];
    
                filters.price_max = data.price / 1000;
                filters.price_min = Math.max((data.price / 1000) - 10, 0);
    
                filters.odometer_max = data.odometer / 1000;
                filters.odometer_min = Math.max((data.odometer / 1000) - 10, 0);
    
                if (!filters.year_max || data.year > filters.year_max) {
                    filters.year_max = data.year;
                }
    
                if (!filters.year_min || data.year < filters.year_min) {
                    filters.year_min = data.year;
                }
            }

            redirectToTable(filters);
        }

        const data = {
            datasets: [dataset]
        };

        const config = {
            type: 'bubble',
            data: data,
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    /* title: {
                        display: true,
                        text: title
                    }, */
                    legend: {
                        display: false
                    },
                    tooltip: {
                        enabled: false,
                        position: 'nearest',
                        external: externalTooltipHandler
                    }
                },
                scales: {
                    x: {
                        reverse: true,
                        title: {
                            display: true,
                            text: 'Quilometragem (Km x1000)',
                        }
                    },
                    y: {
                        title: {
                            display: true,
                            text: 'Preço (R$ x1000)',
                        },
                        //min: 0,
                        //max: 350,
                        ticks: {
                            beginAtZero: true,
                            steps: 50,
                            stepValue: 5,
                            max: 1000
                        }
                    },
                },
                onClick: onChartClick,
            }
        };

        var ctx = $canvas[0].getContext('2d');

        var chart = new Chart(ctx, config);

        return chart;
    }

    function createGroup(dataset) {

        var $groupClone = $group.clone()
            .removeClass('d-none');

        $groupClone.find('[data-attribute="brand-model"]')
            .text(dataset.label);

        var $canvas = $groupClone.find('canvas');

        $chartsRow.append($groupClone);

        createChart($canvas, dataset);

        $groupClone.on('click', '[data-action="expand"]', function() {

            $modal.off('shown.bs.modal');

            $modal.on('shown.bs.modal', function(e) {
                
                var $canvas = $('<canvas></canvas>');

                $modal.find('.modal-title').text(dataset.label);

                $modal.find('.modal-body').append($canvas);

                createChart($canvas, dataset);

            });

            var modal = $modal.modal('show');

            $modal.one('click', '[data-action="close"]', function() {
                modal.modal('hide');
            });

            $modal.one('hidden.bs.modal', function() {
                $modal.find('.modal-body').empty();
            });

        });

        $groupClone.on('click', '[data-action="table"]', function() {

            var filters = getFilters();

            filters['models[]'] = [dataset.label];

            redirectToTable(filters);

        });
    }

    function getYearColor(year) {

        let index = 2023 - year;

        if (index < 0) {
            index = 0;
        }

        if (index > (colors.length - 1)) {
            index = colors.length - 1;
        }

        var color = colors[index];

        var percent = (colors.length - index) / colors.length;

        var opacity = Math.round(percent * 255);

        var opacityHex = opacity.toString(16).padStart(2, '0');

        return color;// + opacityHex;
    }

    function getFilters() {

        var $form = $('form#filters');

        var filters = {};

        $form.find('input').each(function() {
                
            var $input = $(this);

            var name = $input.attr('name');
            var value = $input.val();

            if (value) {
                filters[name] = value;
            }
        });

        $form.find('select').each(function() {

            var $select = $(this);

            var name = $select.attr('name');

            var value = $select.val();

            if (Array.isArray(value) ? value.length > 0 : value) {
                filters[name] = value;
            }

        });

        return filters;
    }

    function loadCars() {

        var filters = getFilters();

        $.ajax({
            method: 'get',
            url: 'charts-data',
            data: filters,
            success: function(datasets) {

                datasets.forEach(function(dataset) {

                    dataset.borderWidth = 0;
                    dataset.hoverRadius = 1;

                    dataset.pointBackgroundColor = function(context) {
                        return getYearColor(context.raw.data.year);
                    }

                    createGroup(dataset);

                });
            },
            error: function(error) {
                console.error(error);
            }
        })
    }

    loadCars();

    $('#update-btn').on('click', loadCars);
    
});