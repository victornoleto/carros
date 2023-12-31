const getOrCreateTooltip = (chart) => {
	
	let tooltipEl = chart.canvas.parentNode.querySelector('div');
	
	if (!tooltipEl) {
		tooltipEl = document.createElement('div');
		tooltipEl.style.background = 'rgba(0, 0, 0, 0.7)';
		tooltipEl.style.borderRadius = '3px';
		tooltipEl.style.color = 'white';
		tooltipEl.style.opacity = 1;
		tooltipEl.style.pointerEvents = 'none';
		tooltipEl.style.position = 'absolute';
		tooltipEl.style.transform = 'translate(-50%, 0)';
		tooltipEl.style.transition = 'all .1s ease';
		
		const table = document.createElement('table');
		table.style.margin = '0px';
		
		tooltipEl.appendChild(table);

		chart.canvas.parentNode.appendChild(tooltipEl);
	}

	tooltipEl.style['z-index'] = 100;
	
	return tooltipEl;
};

const externalTooltipHandler = (context) => {

	// Tooltip Element
	const {chart, tooltip} = context;
	const tooltipEl = getOrCreateTooltip(chart);
	
	// Hide if no tooltip
	if (tooltip.opacity === 0) {
		tooltipEl.style.opacity = 0;
		return;
	}

	// Set Text
	if (tooltip.body) {

		const titleLines = tooltip.title || [];

		const bodyLines = tooltip.body.map(b => b.lines);
		
		const tableHead = document.createElement('thead');
		
		titleLines.forEach(title => {

			const tr = document.createElement('tr');
			tr.style.borderWidth = 0;
			
			const th = document.createElement('th');
			th.style.borderWidth = 0;
			const text = document.createTextNode(title);
			
			th.appendChild(text);
			tr.appendChild(th);
			tableHead.appendChild(tr);
		});
		
		const tableBody = document.createElement('tbody');

		bodyLines.forEach((body, i) => {

			var car = tooltip.dataPoints[i].raw.data;

			const colors = tooltip.labelColors[i];
			
			const span = document.createElement('span');
			span.style.background = colors.backgroundColor.substr(0, colors.backgroundColor.length - 2);
			span.style.borderColor = colors.borderColor;
			span.style.borderWidth = '2px';
			span.style.marginRight = '10px';
			span.style.height = '10px';
			span.style.width = '10px';
			span.style.display = 'inline-block';
			
			const tr = document.createElement('tr');
			tr.style.backgroundColor = 'inherit';
			tr.style.borderWidth = 0;
			
			const td = document.createElement('td');
			td.style.borderWidth = 0;
			
			var $tr = $(tr);
			var $td = $(td);

			var $item = $('<div class="d-flex align-items-center"></div>');

			$td.append($item);

			$item.append(span);

			var $text = $('<div class="d-flex flex-column"></div>');

			$text.append(`<small class="opacity-50 text-nowrap">${car.version ?? '-'}</small>`);
			$text.append(`<p class="m-0">${car.year}, <b>${parseFloat(car.price).toLocaleString('pt-br', {style: 'currency', currency: 'BRL'})}</b></p>`);
			$text.append(`<small>${car.odometer} Km</small>`)

			$item.append($text);

			$tr.append($td);

			tableBody.appendChild($tr[0]);
		});
		
		const tableRoot = tooltipEl.querySelector('table');
		
		// Remove old children
		while (tableRoot.firstChild) {
			tableRoot.firstChild.remove();
		}
		
		// Add new children
		tableRoot.appendChild(tableHead);
		tableRoot.appendChild(tableBody);
	}
	
	const {offsetLeft: positionX, offsetTop: positionY} = chart.canvas;
	
	// Display, position, and set styles for font
	tooltipEl.style.opacity = 1;
	tooltipEl.style.left = positionX + tooltip.caretX + 'px';
	tooltipEl.style.top = positionY + tooltip.caretY + 'px';
	tooltipEl.style.font = tooltip.options.bodyFont.string;
	tooltipEl.style.padding = tooltip.options.padding + 'px ' + tooltip.options.padding + 'px';
};

function getColorByYear(year, palleteIndex) {

	let index = 2023 - year;

	return getPointColor(index, palleteIndex);
}

function getPointColor(index, palleteIndex) {

	palleteIndex = palleteIndex || 0;

	var palletes = [
		['#000000', '#eeeeee'], // black
		['#000000', '#eeeeee'], // blue
		['#000000', '#eeeeee'], // red
		['#000000', '#eeeeee'], // green
		['#000000', '#eeeeee'], // orange
	]

	const colors = new Gradient()
		.setColorGradient(palletes[palleteIndex][0], palletes[palleteIndex][1])
		.setMidpoint(13)
		.getColors();

	if (index < 0) {
		index = 0;
	}

	if (index > (colors.length - 1)) {
		index = colors.length - 1;
	}

	var color = colors[index];

	return color;
}