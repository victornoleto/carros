$(function() {
    
    var $chartsRow = $('#charts-row');

    const colors = new Gradient()
        //.setColorGradient('#318CE7', '#fd5c63')
        .setColorGradient('#000000', '#eeeeee')
        .setMidpoint(13)
        .getColors();

    function onChartClick(event, elements) {

        elements.forEach(function(row) {

            var data = row.element.$context.raw.data;

            console.debug(data);

            const params = new URLSearchParams(data);

            var url = 'redirect?' + params.toString();

            window.open(url,'_blank');

        });
    }

    function createChart(title, dataset) {

        var $div = $('<div class="col-12 col-md-6 col-lg-4 mt-4"></div>');

        var $canvas = $('<canvas></canvas>');

        $div.append($canvas);

        $chartsRow.append($div);
        
        const data = {
            datasets: [dataset]
        };

        const config = {
            type: 'bubble',
            data: data,
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    title: {
                        display: true,
                        text: title
                    },
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
                    xAxis: {
                        reverse: true,
                        title: {
                            display: true,
                            text: 'Quilometragem (Km x1000)',
                        },
                        ticks: {
                            /* callback: function(value, index, values) {
                                return value / 1000;
                            } */
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
                            /* callback: function(value, index, values) {
                                return value / 1000;
                            } */
                        }
                    },
                },
                onClick: onChartClick
            }
        };

        var ctx = $canvas[0].getContext('2d');

        new Chart(ctx, config);
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

        var $form = $('#filters form');

        var filters = {};

        $form.find('input').each(function() {
                
            var $input = $(this);

            var name = $input.attr('name');
            var value = $input.val();

            if (value) {
                filters[name] = value;
            }
        });

        return filters;
    }

    function createCarsDatasets(list) {

        var datasets = {};
        var index = 0;

        list.forEach(function(car) {

            var keyParts = [
                car.brand,
                car.model,
                //car.version
            ];

            var key = keyParts.join(' ');

            if (!datasets[key]) {

                datasets[key] = {
                    label: key,
                    data: [],
                    backgroundColor: colors[index] + '0d',
                    borderWidth: 0,
                    hoverRadius: 1,
                    pointBackgroundColor: function(context) {

                        var year = context.raw.data.year;

                        var color = getYearColor(year);

                        return color;
                    }
                };

                index++;
            }

            datasets[key].data.push({
                x: car.odometer / 1000,
                y: car.price / 1000,
                r: 10,
                data: car
            });

        });

        return datasets;
    }

    function loadCars() {

        var filters = getFilters();

        $.ajax({
            method: 'get',
            url: 'cars',
            data: filters,
            success: function(list) {

                var datasets = createCarsDatasets(list);

                $chartsRow.empty();

                for (var title in datasets) {
                    createChart(title, datasets[title]);
                }
            },
            error: function(error) {
                console.error(error);
            }
        })
    }

    loadCars();

    $('#update-btn').on('click', loadCars);
    
});