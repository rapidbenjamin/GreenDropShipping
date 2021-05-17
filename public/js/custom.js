var ajax_link = '/ajax';

$(document).ready(function () {

	//Page Name
	$('#pageName').html($('.indexContent').data('page_name'));

	//Left Menu
	//$('.row-menu ul li a').removeClass('active');
	$('.row-menu ul li').removeClass('active');
	//$('.row-menu ul li a[data-name="' + $('.indexContent').data('page_name') + '"]').addClass('active');
	$('.row-menu ul li[data-name="' + $('.indexContent').data('page_name') + '"]').addClass('active');

	/* DASHBOARD */
	$('.checklist-item input').click(function () {

		if ($(this).prop('checked')) {
			var value = 1;
		} else {
			var value = 0;
		}

		var parameters = {
			'action': 'add_check',
			'id_user': $('#inputId').val(),
			'step': $(this).data('id'),
			'value': value,
		};

		$.getJSON(ajax_link, parameters, function (data) {

		});

	});

	/* SEARCH PAGE */
	$('.btn_import_list').click(function () {
		var parameters = {
			'action': 'add_import_list',
			'id_product': $(this).data('id')
		}
		
		//This option has been disabled
		/*
		if (parseInt($(this).data('stock')) == 0) {
			$('.id_' + $(this).data('id')).find('.lable-out-stock').css('display', 'block');
			return;
		}*/
		$.getJSON(ajax_link, parameters, function (data) {
			//$('.id_' + JSON.parse(data)).hide();
			$('.id_' + JSON.parse(data) + ' button.add').hide();
			$('.id_' + JSON.parse(data) + ' button.edit').show();
		});

	});



	/* PRODUCT DETAIL */
	$('.imgThumb').click(function () {
		$('.detailImage img').attr('src', $(this).data('img'));
	});


	$('.btn_import_list_detail').click(function () {
		var parameters = {
			'action': 'add_import_list',
			'id_product': $(this).data('id')
		}
		let id = $(this).data('id');
		$.getJSON(ajax_link, parameters, function (data) {
			//window.location.href = $('.pBack').data('url');
			$('.add-to-import-list-' + id).hide();
			$('.edit-on-import-list-' + id).show();
		});

	});


	/* IMPORT LIST */
	$('.import-tab').click(function () {
		var id_product = $(this).parent().parent().data('id');
		var tabName = $(this).data('name');
		$('#product' + id_product).find('.import-tab').removeClass('active');
		$('#product' + id_product).find('.import-tab label').css('color', '#000000');
		$('#product' + id_product).find('.import-content').hide();
		$(this).addClass('active');
		$(this).find('label').css('color', '#89B73D');
		$('#product' + id_product).find('.import-' + tabName).show();
	});



	$('.btn-import-list-delete').click(function () {
		if (confirm('Deleting the product will remove it from your Shopify store. Do you really want to delete it?')) {

			var parameters = {
				'action': 'delete_import_list',
				'id_import_list': $(this).data('id')
			}

			$.getJSON(ajax_link, parameters, function (data) {
				location.reload();

			}).fail(function (data) {
				console.log("error1", data.status);
				if (data.status == 403)
					$('#upgrade-plans-modal').modal('show')
				//$("#upgrade-plans-modal").appendTo("body");
			});

		}

	});

	$('.box-profit').change(function () {
		var id_product = $(this).data('id');
		var cost = $('#cost' + id_product).val();
		var profit = $(this).val();
		if (profit != 0)
			//var value = parseFloat(cost * ( 1 + (profit/100))).toFixed(2);
			var value = parseFloat((100 * cost) / (100 - profit)).toFixed(2);
		else value = cost;

		$('#price' + id_product).val(value);
		$('#price' + id_product).data('price', value);

	});

	$('.box-price').change(function () {
		var id_product = $(this).data('id');
		var cost = $('#cost' + id_product).val();
		var precio = $(this).val();
		var value = 0;
		if (precio != 0)
			value = parseFloat(((precio - cost) / precio) * 100).toFixed(2);

		$('#profit' + id_product).val(value);
		$('#profit' + id_product).data('profit', value);

	});



	/* MY PRODUCTS */
	$('.mp-table-view button').click(function () {
		var id_product = $(this).data('id');
		$('.mp-product-detail').hide();
		$('.row' + id_product).find('.mp-product-detail').show();
	});

	$('button.orders-customers').click(function () {
		window.location.href = "/admin/orders?merchant=" + encodeURIComponent($(this).data('merchant'));
	});
	$('button.detail-merchants').click(function () {
		window.location.href = "/admin/merchants/show/" + $(this).data('merchantid');
	});

	$('input.change-status').click(function () {
		let status = $(this).is(':checked') ? 1 : 0;
		let result = confirm('Are you sure you want to ' + (status == 1 ? 'enable' : 'disable') + ' this merchant?');
		if (!result)
			result;
		window.location.href = "/admin/merchants/changeStatus/" + $(this).data('merchantid') + "/" + status;
	});

	/* PLANS */
	/*
	$('#btnSubmitToken').click(function () {
		$.post('{{url("/plans/save-token")}}', {
			"_token": "{{ csrf_token() }}",
			"token": $('#txtToken').val()
		}, function (data, status) {
			//$('.token-error').hide();
			//$('.token-success').show();
			window.location.href = "{{url('/plans')}}"
		}).fail(function (data) {
			$('.token-error').show();
			//$('.token-success').hide();
		});
	});

	$('.update').click(function () {
		$.post('{{url("/plans/update")}}', {
			"_token": "{{ csrf_token() }}",
			'plan': $(this).data('plan')
		}, function (data, status) {
			$('.token-error').hide();
			window.location.href = "{{url('/plans')}}?update=true";
		}).fail(function (data) {
			$('.token-error').show();
		});
	});
	$("div.alert button.close").click(function () {
		window.location.href = "{{url('/plans')}}"
	});*/

	$('.buttonDisabled').mouseover(function () {
		$('.answerBD' + $(this).data('id')).show();
	});

	$('.buttonDisabled').mouseout(function () {
		$('.answerBD' + $(this).data('id')).hide();
	});


	/* ADMIN ORDER DETAIL */
	$("#btnNotes").click(function () {

		var texto = $("textarea.ta" + $(this).data("id")).val();
		var parameters = {
			'action': 'update_notes',
			'id_order': $(this).data('id'),
			'notes': " " + texto + ". "
		}

		$.getJSON(ajax_link, parameters, function (data) {
			alert("The notes have been updated successfully");
			location.reload();
		});
	});

	/* ADMIN USERS */
	$("#btn-save-user").click(function () {

		//validation
		if($('#txt-user-name').val().length > 2 && $('#txt-email').val().length > 2 && $('#txt-password').val().length > 2  && $('#txt-password').val() == $('#txt-repeat-password').val()){
			var parameters = {
				'action': 'save-user',
				'user' : $('#txt-user-name').val(),
				'email' : $('#txt-email').val(),
				'password' : $('#txt-password').val()
			}

			$.getJSON(ajax_link, parameters, function (data) {
				alert("The user was created successfully");
				location.reload();
			});		
	
		}else{
			alert("You should fill all the fields.");
		}



	});


});