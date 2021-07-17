@extends('layouts.app')
@section('content')

<div class="indexContent" data-page_name="ADMIN DASHBOARD">
	<div class="maincontent">
		<div class="wrapinsidecontent">	

			<div class="panel-settings">
				<div class="admin-title">Admin Dashboard</div>

				<div class="admin-b1">
					<button id="day">Day</button>
					<button id="month">Month</button>
					<button id="year">Year</button>
				</div>
				
					<label for="start">From:</label>
					<input type="date" id="start" name="trip-start" class="date-input">
					<label for="to">To:</label>
					<input type="date" id="to" name="trip-to" class="date-input">

				<button id="apply" class="basicbtn">Apply</button>
			</div>

			<div class="adtwocol">
				<div class="panel-left">
					<ul class="tab-custom">
						<li data-li="Orders" class="tab-active">Orders<br><span id="txt-orders"></span></li>
						<li data-li="Returns">Returns<br><span id="txt-returns"></span></li>
						<li data-li="Sales">Sales<br><span id="txt-sales"></span></li>
						<li data-li="NewInst">Installations<br><span id="txt-newinstalls"></span></li>
						<li data-li="AffPlan">App Upgrades<br><span id="txt-affiates">0</span></li>
						<li data-li="Plans">Plans<br><span id="txt-plans">0</span></li>
					</ul>
					<div class="cwrap">
						<ul style="list-style-type:none" class="tab-body">
							<li class="Orders"><canvas id="canvas-order"></canvas></li>
							<li class="Returns"><canvas id="canvas-returns"></li>
							<li class="Sales"><canvas id="canvas-sale"></canvas></li>
							<li class="NewInst"><canvas id="canvas-newinstalls"></canvas></li>
							<li class="AffPlan"><canvas id="canvas-affiates"></canvas></li>
							<li class="Plans"><canvas id="canvas-plans"></canvas></li>
						</ul>
					</div>
				</div>
				<div class="panel-left">
					<ul class="tab-custom-right">
						<li data-li="lastorders" class="tab-active">Last 10 Orders</li>
						<li data-li="bestsellers">Best Sellers</li>
						<li data-li="topmerchants">Top Merchants</li>
					</ul>
					<div class="cwrap">
						<ul style="list-style-type:none" class="tab-body-right">
							<li class="lastorders">
								<table class="greentable">
									<thead>
										<tr>
											<th>
												Shop
											</th>
											<th>
												Products
											</th>
											<th>
												Total (GDS)
											</th>
											<th>
												Date
											</th>
											<th>
												Status
											</th>
										</tr>
									</thead>
									<tbody id="orders">
									</tbody>
								</table>
							</li>
							<li class="bestsellers">
								<table class="greentable">
									<thead>
										<tr>
											<th>
												Product
											</th>
											<th>
												SKU
											</th>
											<th>
												Units Sold
											</th>
											<th>
												Total (GDS)
											</th>
										</tr>
									</thead>
									<tbody id="sellers">
									</tbody>
								</table>
							</li>
							<li class="topmerchants">
								<table class="greentable">
									<thead>
										<tr>
											<th>
												Shop
											</th>
											<th>
												Orders
											</th>
											<th>
												Total
											</th>
										</tr>
									</thead>
									<tbody id="merchants">
									</tbody>
								</table>
							</li>
						</ul>
					</div>
				</div>
			</div>

		</div>
	</div>
</div>
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.27.0/moment.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@2.9.3/dist/Chart.min.js"></script>

<script type="text/javascript">
	var salesChartConfig = {
		type: 'line',
		data: {
			labels: [],
			datasets: [{
				label: '',
				backgroundColor: 'rgb(255, 99, 132)',
				borderColor: 'rgb(255, 99, 132)',
				data: [

				],
				fill: false,
			}]
		},
		options: {
			responsive: true,
			title: {
				display: true,
				text: 'SALES'
			},
			tooltips: {
				callbacks: {
					label: function(tooltipItem, chart) {
						var datasetLabel = chart.datasets[tooltipItem.datasetIndex].label || '';
						return datasetLabel + 'Sales: $ ' + tooltipItem.yLabel;
					}
				}
			},
			hover: {
				mode: 'nearest',
				intersect: true,

			},
			scales: {
				xAxes: [{

					type: 'time',
					time: {
						//parser: 'YYYY-MM-DD',
						unit: 'day',
						unitStepSize: 12,
						tooltipFormat: 'MMM DD',
						displayFormats: {
							'day': 'MMM DD'
						}
					},
					display: true,
					scaleLabel: {
						display: true,
						labelString: 'Dates'
					}
				}],
				yAxes: [{
					display: true,
					scaleLabel: {
						display: true,
						labelString: 'Total'
					},
					ticks: {
						callback: function(value, index, values) {
							return '$' + value;
						}
					},
				}]
			}
		}
	};


	var ordersChartConfig = {
		type: 'line',
		data: {
			labels: [],
			datasets: [{
				label: '',
				backgroundColor: 'rgb(255, 159, 64)',
				borderColor: 'rgb(255, 159, 64)',
				data: [

				],
				fill: false,
			}]
		},
		options: {
			responsive: true,
			title: {
				display: true,
				text: 'ORDERS'
			},
			tooltips: {
				callbacks: {
					label: function(tooltipItem, chart) {
						var datasetLabel = chart.datasets[tooltipItem.datasetIndex].label || '';
						return datasetLabel + 'Orders: ' + tooltipItem.yLabel;
					}
				}
			},
			hover: {
				mode: 'nearest',
				intersect: true,

			},
			scales: {
				xAxes: [{

					type: 'time',
					time: {
						//parser: 'YYYY-MM-DD',
						unit: 'day',
						unitStepSize: 12,
						tooltipFormat: 'MMM DD',
						displayFormats: {
							'day': 'MMM DD'
						}
					},
					display: true,
					scaleLabel: {
						display: true,
						labelString: 'Dates'
					}
				}],
				yAxes: [{
					display: true,
					scaleLabel: {
						display: true,
						labelString: 'Total'
					},
					ticks: {
						stepSize: 1,
						callback: function(value, index, values) {
							return value;
						}
					},
				}]
			}
		}
	};


	var returnsChartConfig = {
		type: 'line',
		data: {
			labels: [],
			datasets: [{
				label: '',
				backgroundColor: 'rgb(255, 205, 86)',
				borderColor: 'rgb(255, 205, 86)',
				data: [

				],
				fill: false,
			}]
		},
		options: {
			responsive: true,
			title: {
				display: true,
				text: 'RETURNS'
			},
			tooltips: {
				callbacks: {
					label: function(tooltipItem, chart) {
						var datasetLabel = chart.datasets[tooltipItem.datasetIndex].label || '';
						return datasetLabel + 'Returns: ' + tooltipItem.yLabel;
					}
				}
			},
			hover: {
				mode: 'nearest',
				intersect: true,

			},
			scales: {
				xAxes: [{

					type: 'time',
					time: {
						//parser: 'YYYY-MM-DD',
						unit: 'day',
						unitStepSize: 12,
						tooltipFormat: 'MMM DD',
						displayFormats: {
							'day': 'MMM DD'
						}
					},
					display: true,
					scaleLabel: {
						display: true,
						labelString: 'Dates'
					}
				}],
				yAxes: [{
					display: true,
					scaleLabel: {
						display: true,
						labelString: 'Total'
					},
					ticks: {
						stepSize: 1,
						callback: function(value, index, values) {
							return value;
						}
					},
				}]
			}
		}
	};

	var newinstallsChartConfig = {
		type: 'line',
		data: {
			labels: [],
			datasets: [{
				label: '',
				backgroundColor: 'rgb(75, 192, 192)',
				borderColor: 'rgb(75, 192, 192)',
				data: [

				],
				fill: false,
			}]
		},
		options: {
			responsive: true,
			title: {
				display: true,
				text: 'NEW INSTALLATIONS'
			},
			tooltips: {
				callbacks: {
					label: function(tooltipItem, chart) {
						var datasetLabel = chart.datasets[tooltipItem.datasetIndex].label || '';
						return datasetLabel + 'Installations: ' + tooltipItem.yLabel;
					}
				}
			},
			hover: {
				mode: 'nearest',
				intersect: true,

			},
			scales: {
				xAxes: [{

					type: 'time',
					time: {
						//parser: 'YYYY-MM-DD',
						unit: 'day',
						unitStepSize: 12,
						tooltipFormat: 'MMM DD',
						displayFormats: {
							'day': 'MMM DD'
						}
					},
					display: true,
					scaleLabel: {
						display: true,
						labelString: 'Dates'
					}
				}],
				yAxes: [{
					display: true,
					scaleLabel: {
						display: true,
						labelString: 'Total'
					},
					ticks: {
						stepSize: 1,
						callback: function(value, index, values) {
							return value;
						}
					},
				}]
			}
		}
	};

	var plansChartConfig = {
		type: 'line',
		data: {
			labels: [],
			datasets: [{
				label: '',
				backgroundColor: 'rgb(255, 159, 64)',
				borderColor: 'rgb(255, 159, 64)',
				data: [

				],
				fill: false,
			},
			{
				label: '',
				backgroundColor: 'rgb(75, 192, 192)',
				borderColor: 'rgb(75, 192, 192)',
				data: [

				],
				fill: false,
			},
			{
				label: '',
				backgroundColor: 'rgb(255, 99, 132)',
				borderColor: 'rgb(255, 99, 132)',
				data: [

				],
				fill: false,
			}]
		},
		options: {
			responsive: true,
			title: {
				display: true,
				text: 'Plans'
			},
			tooltips: {
				callbacks: {
					label: function(tooltipItem, chart) {
						var datasetLabel = chart.datasets[tooltipItem.datasetIndex].label || '';
						return datasetLabel + ' plan users: ' + tooltipItem.yLabel;
					}
				}
			},
			hover: {
				mode: 'nearest',
				intersect: true,

			},
			scales: {
				xAxes: [{

					type: 'time',
					time: {
						//parser: 'YYYY-MM-DD',
						unit: 'day',
						unitStepSize: 12,
						tooltipFormat: 'MMM DD',
						displayFormats: {
							'day': 'MMM DD'
						}
					},
					display: true,
					scaleLabel: {
						display: true,
						labelString: 'Dates'
					}
				}],
				yAxes: [{
					display: true,
					scaleLabel: {
						display: true,
						labelString: 'Total'
					},
					ticks: {
						stepSize: 1,
						callback: function(value, index, values) {
							return value;
						}
					},
				}]
			}
		}
	};




	window.onload = function() {

		$('.tab-body li:first').show();
		$('.tab-body-right li:first').show();

		$('#start').val(window.datenow().subtract(1, 'months').format('YYYY-MM-DD'));
		$('#to').attr('max', window.datenow().format('YYYY-MM-DD'));
		$('#to').val(window.datenow().format('YYYY-MM-DD'));
		//sales
		var ctx = document.getElementById('canvas-sale').getContext('2d');
		window.salesChart = new Chart(ctx, salesChartConfig);
		//orders
		var ctx = document.getElementById('canvas-order').getContext('2d');
		window.orderChart = new Chart(ctx, ordersChartConfig);

		//plans
		var ctx = document.getElementById('canvas-plans').getContext('2d');
		window.plansChart = new Chart(ctx, plansChartConfig);

		//returns
		var ctx = document.getElementById('canvas-returns').getContext('2d');
		window.returnsChart = new Chart(ctx, returnsChartConfig);

		//new installs
		var ctx = document.getElementById('canvas-newinstalls').getContext('2d');
		window.newinstallsChart = new Chart(ctx, newinstallsChartConfig);
		applyFilter(12);
	};


	$("#start").change(function() {
		let start = moment($(this).val());
		let to = start.add(1, 'months');
		//$('#to').val(to.format('YYYY-MM-DD'));
	});

	$("#to").change(function() {
		let to = moment($(this).val());
		let start = to.subtract(1, 'months');
		//$('#start').val(start.format('YYYY-MM-DD'));
	});


	$("#day").click(function() {
		$('#start').val(window.datenow().format('YYYY-MM-DD'));
		$('#to').val(window.datenow().format('YYYY-MM-DD'));
		applyFilter(6);
	});

	$("#month").click(function() {
		$('#start').val(moment().startOf('month').format('YYYY-MM-DD'));
		$('#to').val(window.datenow().format('YYYY-MM-DD'));
		applyFilter(15);
	});

	$("#year").click(function() {
		$('#start').val(moment().startOf('year').format('YYYY-MM-DD'));
		$('#to').val(moment().endOf('year').format('YYYY-MM-DD'));
		applyFilter(28);
	});

	$("#apply").click(function() {
		if (moment($('#start').val()).isAfter( moment($('#to').val()).format('YYYY-MM-DD')) ) {
			$('#modal-body').html(`<h5>Invalid date range</h5>`);
			$('#modal-footer').hide();
			$('#apply').attr('data-toggle', 'modal');
			$('#apply').attr('data-target', '#delete-product-modal');
			return;
		} else $('#apply').attr('data-toggle', '');
		applyFilter(12);
	});

	function applyFilter(days) {
		$.post('{{url("admin/stats-data")}}', {
			"_token": "{{ csrf_token() }}",
			'from': $("#start").val(),
			'to': $("#to").val()
		}, function(data, status) {
			let sales = data.sales;
			let orders = data.orders;
			let returns = data.returns;
			let newinstalls = data.newinstalls;
			let lastOrders = data.lastorders;
			let topMerchants = data.topmerchants;
			let bestsellers = data.bestsellers;
			let plans_basic = data.plans_basic;
			let plans_free = data.plans_free;
			let plans_advance = data.plans_advance;

			let start = $('#start').val();
			let to = $('#to').val();

			let dates = [];
			start_moment = moment(start);
			dates.push(start_moment.toDate());
			do {
				let dateaux = start_moment.add(days, 'days')
				dates.push(dateaux.toDate());
				start_moment = dateaux;
			} while (start_moment.isSameOrBefore(moment($('#to').val()).format('YYYY-MM-DD')));

			//sales
			salesChartConfig.data.labels = [];
			salesChartConfig.data.datasets[0].data = [];

			//orders
			ordersChartConfig.data.labels = [];
			ordersChartConfig.data.datasets[0].data = [];

			//returns
			returnsChartConfig.data.labels = [];
			returnsChartConfig.data.datasets[0].data = [];

			//new installs
			newinstallsChartConfig.data.labels = [];
			newinstallsChartConfig.data.datasets[0].data = [];

			//plans
			plansChartConfig.data.labels = [];
			plansChartConfig.data.datasets[0].data = [];

			dates.forEach(function(ele) {
				salesChartConfig.data.labels.push(ele);
				ordersChartConfig.data.labels.push(ele);
				returnsChartConfig.data.labels.push(ele);
				newinstallsChartConfig.data.labels.push(ele);
				plansChartConfig.data.labels.push(ele);
			});


			//plans data
			plansChartConfig.data.datasets[0].label = 'Free';
			plansChartConfig.data.datasets[1].label = 'Basic';
			plansChartConfig.data.datasets[2].label = 'Advance';
			plans_free.forEach(function(ele) {
				plansChartConfig.data.datasets[0].data.push({
					x: ele.date_at,
					y: ele.Numero
				});
			});

			plans_basic.forEach(function(ele) {
				plansChartConfig.data.datasets[1].data.push({
					x: ele.date_at,
					y: ele.Numero
				});
			});

			plans_advance.forEach(function(ele) {
				plansChartConfig.data.datasets[2].data.push({
					x: ele.date_at,
					y: ele.Numero
				});
			});

			//sales data
			
			sales.forEach(function(ele) {
				salesChartConfig.data.datasets[0].data.push({
					x: ele.date_at,
					y: ele.Total
				});
			});

			//orders data
			orders.forEach(function(ele) {
				ordersChartConfig.data.datasets[0].data.push({
					x: ele.date_at,
					y: ele.Total
				});
			});

			//orders data
			returns.forEach(function(ele) {
				returnsChartConfig.data.datasets[0].data.push({
					x: ele.date_at,
					y: ele.Total
				});
			});

			//new installs data
			newinstalls.forEach(function(ele) {
				newinstallsChartConfig.data.datasets[0].data.push({
					x: ele.date_at,
					y: ele.Total
				});
			});

			$('#txt-orders').text(data.num_sales);
			$('#txt-returns').text(data.num_returns);
			$('#txt-sales').text(data.total_sales[0].TotalSales ? `$${data.total_sales[0].TotalSales}` : 0);
			$('#txt-newinstalls').text(data.new_installations);
			$('#txt-affiates').text();

			$('#orders').empty();
			let lis = '';
			var k = 1;
			for (let i = 0; i < lastOrders.length; i++) {
				if (k == 1) var col = "#fff";
				else col = "transparent";
				lis += `<tr class="productdatarow" style="background:${col}"><td style="word-break: break-all;" data-label="Shop">${lastOrders[i].name}</td><td data-label="Products">${lastOrders[i].Products}</td><td data-label="Total (GDS)">$${lastOrders[i].total}</td><td data-label="Date">${lastOrders[i].created_at}</td><td data-label="Status"><div style="display: flex; justify-content: flex-end;"><span style="background:${lastOrders[i].statuscolor}; color:#000;padding: 4px; min-width:70px; text-align:center;"> ${lastOrders[i].status} </span></div></td></tr>`;
				if (k == 1) k = 0;
				else k = 1;
			}
			$('#orders').append(lis);

			$('#merchants').empty();
			let lism = '';
			var k = 1;
			for (let i = 0; i < topMerchants.length; i++) {
				if (k == 1) var col = "#fff";
				else col = "transparent";
				lism += `<tr class="productdatarow" style="background:${col}"><td style="word-break: break-all;" data-label="Shop">${topMerchants[i].name}</td><td data-label="Orders">${topMerchants[i].num_orders}</td><td data-label="Total">$${topMerchants[i].total}</td></tr>`;
				if (k == 1) k = 0;
				else k = 1;
			}
			$('#merchants').append(lism);

			$('#sellers').empty();
			let liss = '';
			var k = 1;
			for (let i = 0; i < bestsellers.length; i++) {
				if (k == 1) var col = "#fff";
				else col = "transparent";
				liss += `<tr class="productdatarow" style="background:${col}"><td style="word-break: break-all;" data-label="Product">${bestsellers[i].name}</td><td data-label="SKU">${bestsellers[i].sku}</td><td data-label="Units Sold">${bestsellers[i].Counts}</td><td data-label="Total (GDS)">$${bestsellers[i].total}</td></tr>`;
				if (k == 1) k = 0;
				else k = 1;
			}
			$('#sellers').append(liss);

			window.salesChart.update();
			window.orderChart.update();
			window.returnsChart.update();
			window.newinstallsChart.update();
			window.plansChart.update();
		});
	}

	window.datenow = function() {
		return moment();
	};
	window.chartColors = {
		red: 'rgb(255, 99, 132)',
		orange: 'rgb(255, 159, 64)',
		yellow: 'rgb(255, 205, 86)',
		green: 'rgb(75, 192, 192)',
		blue: 'rgb(54, 162, 235)',
		purple: 'rgb(153, 102, 255)',
		grey: 'rgb(201, 203, 207)'
	};

	$(".tab-custom li").click(function() {
		/*reset */
		$('.tab-body li').hide();
		$('.tab-custom li').removeClass('tab-active');
		$('.tab-custom li').addClass('tab-disabled');

		$(this).addClass('tab-active');
		$('.' + $(this).attr('data-li')).show();
	});

	$(".tab-custom-right li").click(function() {
		/*reset */
		$('.tab-body-right > li').hide();
		$('.tab-custom-right > li').removeClass('tab-active');
		$('.tab-custom-right  > li').addClass('tab-disabled');

		$(this).addClass('tab-active');
		$('.' + $(this).attr('data-li')).show();
	});
</script>
@endsection