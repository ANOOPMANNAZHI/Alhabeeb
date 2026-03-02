jQuery(document).ready(function() {
	'use strict';


	
	Morris.Donut({
		element: "donut_chart",
		data: [{
			label: 'Total',
			value: 100
		}, {
			label: 'Comprehensive',
			value: 75
		}, {
			label: 'Normal',
			value: 25
		}],
		colors: ['rgb(233, 30, 99)', 'rgb(0, 188, 212)', 'rgb(255, 152, 0)'],
		formatter: function (y) {
			return y + ''
		}
	});
		Morris.Donut({
		element: "donut_chart1",
		data: [{
			label: 'Total',
			value: 100
		}, {
			label: 'Comprehensive',
			value: 75
		}, {
			label: 'Normal',
			value: 25
		}],
		colors: ['rgb(233, 30, 99)', 'rgb(0, 188, 212)', 'rgb(255, 152, 0)'],
		formatter: function (y) {
			return y + ''
		}
	});
});


