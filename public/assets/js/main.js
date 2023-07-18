$(function() {

    var $filters = $('#filters');

    $filters.on('click', '#clear-filters-button', function() {

        $filters.find('input').val('');

        $filters.find('select').each(function() {
            $(this).val('').trigger('change');
        });

    });

    $filters.init.prototype.getData = function() {

        var filters = {};

        $(this).find('input').each(function() {
                
            var $input = $(this);

            var name = $input.attr('name');
            var value = $input.val();

            if (value) {
                filters[name] = value;
            }

        });

        $(this).find('select').each(function() {

            var $select = $(this);

            var name = $select.attr('name');

            var value = $select.val();

            //name = name.replace('[]', '');

            if (Array.isArray(value) ? value.length > 0 : value) {
                filters[name] = encodeURIComponent(JSON.stringify(value));;
            }

        });

        console.log(filters);

        return filters;
    };

    $('#page-dashboard').each(function() {

        var $page = $(this);
        
        var $chartsRow = $page.find('#charts-row');
    
        var $group = $chartsRow.find('> div:first')
            .detach();
    
        var $modal = $('#chart-modal');
    
        function redirectToTable(filters) {
    
            var query = new URLSearchParams(filters).toString();

            console.log(filters, query);
        
            window.open('table?' + query, '_blank').focus();
        }
            
        function createChart($canvas, dataset) {
                
            var onChartClick = function(event, elements) {
    
                if (!elements || elements.length == 0) return;
            
                var filters = $filters.getData();
        
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
    
                var filters = $filters.getData();
    
                filters['models[]'] = [dataset.label];
    
                redirectToTable(filters);
    
            });
        }
    
        function loadCars() {
    
            $.ajax({
                method: 'get',
                url: 'charts-data',
                data: $filters.getData(),
                success: function(datasets) {
                    
                    $chartsRow.empty();
                    
                    datasets.forEach(function(dataset) {
    
                        dataset.borderWidth = 0;
                        dataset.hoverRadius = 1;
    
                        dataset.pointBackgroundColor = function(context) {
                            return getColorByYear(context.raw.data.year);
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

        $page.on('click', '#filters-button', function() {
            loadCars();
        });

    });

    $('#page-table').each(function() {

        var $page = $(this);

        console.log('opa');

        $page.on('click', '#filters-button', function() {
            console.log('opa');
            $(this).closest('form').trigger('submit');
        });

    });
    
});