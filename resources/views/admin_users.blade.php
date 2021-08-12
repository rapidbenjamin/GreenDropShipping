@extends('layouts.app')
@section('content')

<div class="indexContent" data-page_name="ADMIN USERS">
	<div class="maincontent">
		<div class="wrapinsidecontent">
			<div class="new-user">
				<h4>Create a new user.</h4>
				<h5 style="display: none;" id="success-user">The user was created successfully.</h5>
				<h5 style="display: none;" id="fail-user">The email already exists.</h5>
				<div class="nuform">
					<div class="user-info">
						<label>User Name<span>*</span> </label><input type="text" minlength="3" id="txt-user-name" required>
						<p style="display: none;" id="name-error"></p>
					</div>
					<div class="user-info">
						<label>Email<span>*</span> </label><input type="email" id="txt-email" pattern="([A-Za-z0-9][._]?)+[A-Za-z0-9]@[A-Za-z0-9]+(\.?[A-Za-z0-9]){2}\.(com?|net|org)+(\.[A-Za-z0-9]{2,4})?" autocomplete="email" required>
						<p style="display: none;" id="email-error"></p>
					</div>
					<div class="user-info">
						<label>Password<span>*</span> </label><input type="password" minlength="12" id="txt-password" autocomplete="new-password" title="more than 12 characters, lowercase, uppercase, number, symbol" required><i class="fa fa-eye" data-id="#txt-password"></i>
						<p id="password-error"></p>
					</div>
					<div class="user-info">
						<label>Confirm Password<span>*</span> </label><input type="password" minlength="12" id="txt-confirm-password" autocomplete="new-password" required><i class="fa fa-eye" data-id="#txt-confirm-password"></i>
						<p style="display: none;" id="confirm-error"></p>
					</div>
					<div class="update-user">
						<button class="bgVC colorBL" id="btn-save-user">Save</button>
					</div>
				</div>
			</div>
			<div class="pagesize">
				<span>Show</span>
				<select name="PageSize" id="page_size">
					<option value="10">10</option>
					<option value="20">20</option>
					<option value="50">50</option>
					<option value="100">100</option>
				</select>
			</div>
			<div class="users">
				<h5 class="font-weight-bold">Search </h5>
                <table class="searchtable tableorders mb-5" cellspacing="0">
                    <thead>
                        <tr>
                            <th>
                                <input type="text" id="user_name" class="merchant-search" list="names" placeholder="USER NAME">
                                <datalist id="names">
                                    <div id="name_data"></div>
                                </datalist>
                            </th>
                            <th>
                                <input type="text" id="user_email" class="merchant-search" list="emails" placeholder="EMAIL">
                                <datalist id="emails">
                                    <div id="email_data"></div>
                                </datalist>
                            </th>
                            <th>
                                <select name="Active" id="user_active" class="merchant-search">
                                    <option value="" style="color: gray">Status</option>
                                    <option value="1">Active</option>
                                    <option value="0">Inactive</option>
                                </select>
                            </th>
                        </tr>
                    </thead>
                </table>
				<table class="greentable tableorders" cellspacing="0">
					<thead>
						<tr>
							<th>
								USER NAME
							</th>
							<th>
								EMAIL
							</th>
							<th>
								ACTIVE
							</th>
							<th>
								ACTIONS
							</th>
						</tr>
					</thead>
					<tbody id="user_data">

						@php $k = 0 @endphp
						@foreach($users as $user)
						@if($k == 0)
						@php
						$back = 'transparent';
						$k = 1;
						@endphp
						@else
						@php
						$back = '';
						$k = 0;
						@endphp

						@endif
						<tr class="userdatarow">
							<td data-label="USER NAME">
								{{$user->name}}
							</td>
							<td data-label="EMAIL">
								{{$user->email}}
							</td>
							<td data-label="ACTIVE">
								<input type="checkbox" name="switch-button" id="switch-label{{$user->id}}" data-userid="{{$user->id}}" data-toggle="modal" data-target="#delete-product-modal" class="switch-button__checkbox change-status" @if($user->active == 1)checked @endif>
							</td>
							<td>
								<a href="/admin/merchants/show/{{$user->id}}"><button class="view greenbutton">View</button></a>
							</td>
						</tr>
						@endforeach
					</tbody>
				</table>
			</div>
		</div>
	</div>
</div>

<div id="pagination"></div>
<input type="text" id="total_count" value="{{$total_count}}" hidden>
<input type="text" id="user_id" hidden>

<script type="text/javascript">
    $(document).ready(function() {
        $('#total_count').text("{{$total_count}}");
		$('#confirm').click(function() {
            var user_id = $('#user_id').val();
            var status = $(`#switch-label${user_id}`).is(':checked') ? false : true;
            var parameters = {
                action: 'change-user-status',
                user_id: user_id,
                active: status ? 1 : 0,
            }
            $.getJSON(ajax_link, parameters, function(res) {
                $(`#switch-label${user_id}`).prop('checked', status);
            })
        });
	});
	$('#user_data').on('click', 'input.change-status', function() {
        let status = $(this).is(':checked')
        if ($(this).is(':checked')) {
			$(this).prop('checked', false);
		} else {
			$(this).prop('checked', true);
		}
        $('#user_id').val($(this).data('userid'));
        $('#modal-body').html(`<h5>Are your sure to ${$(this).is(':checked') ? 'disable' : 'enable'} this user?</h5>`);
    });
</script>

@endsection